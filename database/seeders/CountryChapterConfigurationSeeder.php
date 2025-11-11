<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CountryChapterConfiguration;

class CountryChapterConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configurations = [
            // Ghana - Country-wide chapter
            [
                'country_code' => 'GH',
                'country_name' => 'Ghana',
                'chapter_type' => 'country',
                'allow_multiple_chapters' => false,
                'is_active' => true,
                'notes' => 'Ghana has one national chapter for all alumni in the country.',
            ],

            // United States - City-based chapters
            [
                'country_code' => 'US',
                'country_name' => 'United States',
                'chapter_type' => 'city',
                'allow_multiple_chapters' => false,
                'is_active' => true,
                'notes' => 'USA alumni are organized by city/state chapters.',
            ],

            // United Kingdom - City-based chapters
            [
                'country_code' => 'GB',
                'country_name' => 'United Kingdom',
                'chapter_type' => 'city',
                'allow_multiple_chapters' => false,
                'is_active' => true,
                'notes' => 'UK alumni are organized by city chapters.',
            ],

            // Nigeria - Country-wide chapter
            [
                'country_code' => 'NG',
                'country_name' => 'Nigeria',
                'chapter_type' => 'country',
                'allow_multiple_chapters' => false,
                'is_active' => true,
                'notes' => 'Nigeria has one national chapter.',
            ],

            // South Africa - Country-wide chapter
            [
                'country_code' => 'ZA',
                'country_name' => 'South Africa',
                'chapter_type' => 'country',
                'allow_multiple_chapters' => false,
                'is_active' => true,
                'notes' => 'South Africa has one national chapter.',
            ],

            // Canada - City-based chapters
            [
                'country_code' => 'CA',
                'country_name' => 'Canada',
                'chapter_type' => 'city',
                'allow_multiple_chapters' => false,
                'is_active' => true,
                'notes' => 'Canada alumni are organized by city chapters.',
            ],
        ];

        foreach ($configurations as $config) {
            CountryChapterConfiguration::updateOrCreate(
                ['country_code' => $config['country_code']],
                $config
            );
        }
    }
}

