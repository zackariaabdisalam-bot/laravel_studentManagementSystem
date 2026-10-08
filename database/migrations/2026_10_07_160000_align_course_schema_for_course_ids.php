<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('courses')) {
            return;
        }

        $columns = Schema::getColumnListing('courses');

        if (in_array('name', $columns, true) && ! in_array('course_name', $columns, true)) {
            Schema::table('courses', function (Blueprint $table) {
                $table->renameColumn('name', 'course_name');
            });
            $columns = Schema::getColumnListing('courses');
        }

        if (in_array('code', $columns, true) && ! in_array('course_code', $columns, true)) {
            Schema::table('courses', function (Blueprint $table) {
                $table->renameColumn('code', 'course_code');
            });
            $columns = Schema::getColumnListing('courses');
        }

        if (in_array('course_code', $columns, true)) {
            Schema::table('courses', function (Blueprint $table) {
                $table->string('course_code')->nullable()->change();
            });
        }

        if (! in_array('status', $columns, true)) {
            Schema::table('courses', function (Blueprint $table) {
                $table->string('status')->default('Active');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('courses')) {
            return;
        }

        $columns = Schema::getColumnListing('courses');

        if (in_array('course_code', $columns, true)) {
            DB::table('courses')
                ->whereNull('course_code')
                ->orderBy('id')
                ->get(['id'])
                ->each(function (object $course): void {
                    DB::table('courses')
                        ->where('id', $course->id)
                        ->update(['course_code' => 'COURSE-'.Str::uuid()]);
                });

            Schema::table('courses', function (Blueprint $table) {
                $table->string('course_code')->nullable(false)->change();
            });

            if (! in_array('code', $columns, true)) {
                Schema::table('courses', function (Blueprint $table) {
                    $table->renameColumn('course_code', 'code');
                });
            }
        }

        if (in_array('course_name', $columns, true) && ! in_array('name', $columns, true)) {
            Schema::table('courses', function (Blueprint $table) {
                $table->renameColumn('course_name', 'name');
            });
        }

        if (in_array('status', $columns, true)) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
