<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LegacyStudentSchemaMigrationTest extends TestCase
{
    public function test_legacy_student_schema_is_aligned_without_losing_existing_students(): void
    {
        $originalConnection = DB::getDefaultConnection();
        config([
            'database.connections.legacy_student_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('legacy_student_test');
        DB::setDefaultConnection('legacy_student_test');

        try {
            Schema::create('students', function (Blueprint $table): void {
                $table->id();
                $table->string('admission_number')->unique();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone', 30)->nullable();
                $table->unsignedBigInteger('course_id')->nullable();
                $table->date('enrollment_date');
                $table->string('status')->default('active');
                $table->timestamps();
            });

            DB::table('students')->insert([
                'admission_number' => 'ST-OLD-1',
                'name' => 'Alex Legacy Student',
                'email' => 'legacy@example.test',
                'enrollment_date' => '2025-09-01',
                'status' => 'active',
            ]);

            $migration = require database_path('migrations/2026_10_06_115646_create_students_table.php');
            $migration->up();

            $this->assertDatabaseHas('students', [
                'student_number' => 'ST-OLD-1',
                'first_name' => 'Alex',
                'last_name' => 'Legacy Student',
                'enrollment_date' => '2025-09-01',
            ]);
            $this->assertFalse(Schema::hasColumn('students', 'name'));
            $this->assertFalse(Schema::hasColumn('students', 'admission_number'));

            Student::query()->create([
                'student_number' => 'ST-NEW-1',
                'first_name' => 'New',
                'last_name' => 'Student',
                'gender' => 'Other',
                'date_of_birth' => '2005-06-01',
                'email' => 'new@example.test',
                'status' => 'active',
            ]);

            $this->assertDatabaseHas('students', ['student_number' => 'ST-NEW-1']);
        } finally {
            DB::setDefaultConnection($originalConnection);
            DB::purge('legacy_student_test');
        }
    }
}
