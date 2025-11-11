<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Hall;

class HallSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $halls = [
            // Male Halls
            [
                'name' => 'Legon Hall',
                'hall_code' => 'LEG',
                'description' => 'The first hall of residence established at the University of Ghana. Known for its rich history and traditions.',
                'gender' => 'mixed',
                'is_active' => true,
            ],
            [
                'name' => 'Akuafo Hall',
                'hall_code' => 'AKU',
                'description' => 'One of the oldest halls, known for its vibrant student life and academic excellence.',
                'gender' => 'mixed',
                'is_active' => true,
            ],
            [
                'name' => 'Commonwealth Hall',
                'hall_code' => 'COM',
                'description' => 'The largest male hall, popularly known as "Vandals". Known for its strong sporting culture.',
                'gender' => 'male',
                'is_active' => true,
            ],

            // Female Halls
            [
                'name' => 'Volta Hall',
                'hall_code' => 'VOL',
                'description' => 'The first female hall of residence. Known for elegance and academic excellence.',
                'gender' => 'female',
                'is_active' => true,
            ],
            [
                'name' => 'Mensah Sarbah Hall',
                'hall_code' => 'MSH',
                'description' => 'A female hall known for its strong sense of community and academic achievement.',
                'gender' => 'mixed',
                'is_active' => true,
            ],

            [
                'name' => 'VALCO Trust Fund Hostel (VALTROSS)',
                'hall_code' => 'VAL',
                'description' => 'A modern mixed hostel providing comfortable accommodation for students.',
                'gender' => 'mixed',
                'is_active' => true,
            ],
            [
                'name' => 'Jean Nelson Aka Hall',
                'hall_code' => 'JNA',
                'description' => 'A mixed hall offering modern facilities for both male and female students.',
                'gender' => 'mixed',
                'is_active' => true,
            ],
            [
                'name' => 'Alexander Adum Kwapong Hall (Pentagon)',
                'hall_code' => 'PEN',
                'description' => 'A mixed hostel known for its unique pentagon architecture.',
                'gender' => 'mixed',
                'is_active' => true,
            ],
            [
                'name' => 'International Students Hostel (ISH)',
                'hall_code' => 'ISH',
                'description' => 'Dedicated accommodation for international students studying at UG.',
                'gender' => 'mixed',
                'is_active' => true,
            ],
            [
                'name' => 'ECOMOG Hostel',
                'hall_code' => 'ECO',
                'description' => 'A mixed hostel providing affordable accommodation for students.',
                'gender' => 'mixed',
                'is_active' => true,
            ],
            [
                'name' => 'Hilla Limann Hall',
                'hall_code' => 'HLH',
                'description' => 'A mixed hall named after the former President of Ghana.',
                'gender' => 'mixed',
                'is_active' => true,
            ],
            [
                'name' => 'Elizabeth Sey Hall',
                'hall_code' => 'ESH',
                'description' => 'A modern mixed hostel offering contemporary facilities.',
                'gender' => 'mixed',
                'is_active' => true,
            ],
            [
                'name' => 'James Topp Nelson Yankah Hall',
                'hall_code' => 'JTN',
                'description' => 'A mixed hall providing quality accommodation for students.',
                'gender' => 'mixed',
                'is_active' => true,
            ],
        ];

        foreach ($halls as $hall) {
            Hall::create($hall);
        }
    }
}
