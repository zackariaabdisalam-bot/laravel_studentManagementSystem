<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table) {
                $table->id();
                $table->string('student_number')->unique();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('gender')->nullable();
                $table->date('date_of_birth')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('address')->nullable();
                $table->string('status')->default('Active');
                $table->timestamps();
            });

            return;
        }

        $columns = Schema::getColumnListing('students');

        if (! in_array('student_number', $columns, true) && in_array('admission_number', $columns, true)) {
            Schema::table('students', function (Blueprint $table) {
                $table->renameColumn('admission_number', 'student_number');
            });
            $columns = Schema::getColumnListing('students');
        }

        if (! in_array('student_number', $columns, true)) {
            throw new \RuntimeException('Cannot align the students table: student_number or admission_number is missing.');
        }

        $needsFirstName = ! in_array('first_name', $columns, true);
        $needsLastName = ! in_array('last_name', $columns, true);
        $hasLegacyName = in_array('name', $columns, true);

        if (($needsFirstName || $needsLastName) && ! $hasLegacyName) {
            throw new \RuntimeException('Cannot align the students table: first_name or last_name is missing and no legacy name column is available.');
        }

        Schema::table('students', function (Blueprint $table) use ($columns): void {
            if (! in_array('first_name', $columns, true)) {
                $table->string('first_name')->nullable();
            }
            if (! in_array('last_name', $columns, true)) {
                $table->string('last_name')->nullable();
            }
            if (! in_array('gender', $columns, true)) {
                $table->string('gender')->nullable();
            }
            if (! in_array('date_of_birth', $columns, true)) {
                $table->date('date_of_birth')->nullable();
            }
            if (! in_array('address', $columns, true)) {
                $table->string('address')->nullable();
            }
        });

        if ($hasLegacyName && ($needsFirstName || $needsLastName)) {
            DB::table('students')
                ->select(['id', 'name'])
                ->orderBy('id')
                ->chunkById(100, function ($students) use ($needsFirstName, $needsLastName): void {
                    foreach ($students as $student) {
                        $parts = preg_split('/\s+/u', trim($student->name), 2, PREG_SPLIT_NO_EMPTY) ?: [];
                        $values = [];

                        if ($needsFirstName) {
                            $values['first_name'] = $parts[0] ?? '';
                        }
                        if ($needsLastName) {
                            $values['last_name'] = $parts[1] ?? '';
                        }

                        DB::table('students')->where('id', $student->id)->update($values);
                    }
                });
        }

        if (in_array('enrollment_date', $columns, true)) {
            Schema::table('students', function (Blueprint $table) {
                $table->date('enrollment_date')->nullable()->change();
            });
        }

        if ($hasLegacyName) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('students', 'course_id') || Schema::hasColumn('students', 'enrollment_date')) {
            return;
        }

        Schema::dropIfExists('students');
    }
};
