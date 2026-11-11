<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentCourseSeeder extends Seeder
{
    public function run(): void
    {
        // Add courses
        $courses = [
            [
                'course_code' => 'ST001',
                'course_name' => 'Software Technologies',
                'description' => 'Software development and programming',
                'duration' => '2 Years',
                'status' => 'Active',
            ],
            [
                'course_code' => 'FD001',
                'course_name' => 'Fullstack Development',
                'description' => 'Frontend and backend web development',
                'duration' => '2 Years',
                'status' => 'Active',
            ],
            [
                'course_code' => 'DB001',
                'course_name' => 'Database Management',
                'description' => 'Database design and SQL',
                'duration' => '1 Year',
                'status' => 'Active',
            ],
        ];

        foreach ($courses as $course) {
            DB::table('courses')->updateOrInsert(
                ['course_code' => $course['course_code']],
                array_merge($course, [
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }

        // Add students with individual student numbers, genders and phone numbers
        $students = [
            ['Grace', 'Wanjiku', 'STU0001', 'Female', '+254712345001'],
            ['Brian', 'Otieno', 'STU0002', 'Male', '+254712345002'],
            ['Amina', 'Hassan', 'STU0003', 'Female', '+254712345003'],
            ['Kevin', 'Mwangi', 'STU0004', 'Male', '+254712345004'],
            ['Faith', 'Njeri', 'STU0005', 'Female', '+254712345005'],
            ['Daniel', 'Kiptoo', 'STU0006', 'Male', '+254712345006'],
            ['Mercy', 'Achieng', 'STU0007', 'Female', '+254712345007'],
            ['Peter', 'Kamau', 'STU0008', 'Male', '+254712345008'],
            ['Lucy', 'Wambui', 'STU0009', 'Female', '+254712345009'],
            ['Samuel', 'Maina', 'STU0010', 'Male', '+254712345010'],
            ['Jane', 'Naliaka', 'STU0011', 'Female', '+254712345011'],
            ['John', 'Mutua', 'STU0012', 'Male', '+254712345012'],
            ['Esther', 'Nyambura', 'STU0013', 'Female', '+254712345013'],
            ['David', 'Ouma', 'STU0014', 'Male', '+254712345014'],
            ['Rose', 'Chebet', 'STU0015', 'Female', '+254712345015'],
            ['Michael', 'Karanja', 'STU0016', 'Male', '+254712345016'],
            ['Cynthia', 'Adhiambo', 'STU0017', 'Female', '+254712345017'],
            ['Joseph', 'Muli', 'STU0018', 'Male', '+254712345018'],
            ['Caroline', 'Wairimu', 'STU0019', 'Female', '+254712345019'],
            ['Patrick', 'Omondi', 'STU0020', 'Male', '+254712345020'],
            ['Ann', 'Wekesa', 'STU0021', 'Female', '+254712345021'],
        ];

        foreach ($students as $student) {
            DB::table('students')->updateOrInsert(
                ['student_number' => $student[2]],
                [
                    'first_name' => $student[0],
                    'last_name' => $student[1],
                    'gender' => $student[3],
                    'phone' => $student[4],
                    'email' => strtolower(
                        $student[0].'.'.$student[1]
                    ).'@example.com',
                    'date_of_birth' => null,
                    'address' => null,
                    'status' => 'Active',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
