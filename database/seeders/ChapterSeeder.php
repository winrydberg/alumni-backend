<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Chapter;

class ChapterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $chapters = [
            // Ghana Chapter (Country-wide)
            [
                'name' => 'Ghana Chapter',
                'code' => 'GH',
                'description' => 'The Ghana chapter serves all University of Ghana alumni residing in Ghana.',
                'type' => 'country',
                'country_code' => 'GH',
                'country_name' => 'Ghana',
                'state_province' => null,
                'city' => null,
                'contact_email' => 'ghana@ugalumni.org',
                'contact_phone' => '+233 123 456 789',
                'is_active' => true,
            ],

            // United States City Chapters
            [
                'name' => 'New York Chapter',
                'code' => 'US-NY',
                'description' => 'University of Ghana alumni in New York City and surrounding areas.',
                'type' => 'city',
                'country_code' => 'US',
                'country_name' => 'United States',
                'state_province' => 'New York',
                'city' => 'New York',
                'contact_email' => 'newyork@ugalumni.org',
                'contact_phone' => '+1 212 555 0100',
                'is_active' => true,
            ],
            [
                'name' => 'Washington DC Chapter',
                'code' => 'US-DC',
                'description' => 'University of Ghana alumni in Washington DC, Maryland, and Virginia.',
                'type' => 'city',
                'country_code' => 'US',
                'country_name' => 'United States',
                'state_province' => 'District of Columbia',
                'city' => 'Washington',
                'contact_email' => 'dc@ugalumni.org',
                'contact_phone' => '+1 202 555 0100',
                'is_active' => true,
            ],
            [
                'name' => 'California Chapter',
                'code' => 'US-CA',
                'description' => 'University of Ghana alumni in California.',
                'type' => 'city',
                'country_code' => 'US',
                'country_name' => 'United States',
                'state_province' => 'California',
                'city' => 'Los Angeles',
                'contact_email' => 'california@ugalumni.org',
                'contact_phone' => '+1 310 555 0100',
                'is_active' => true,
            ],
            [
                'name' => 'Texas Chapter',
                'code' => 'US-TX',
                'description' => 'University of Ghana alumni in Texas.',
                'type' => 'city',
                'country_code' => 'US',
                'country_name' => 'United States',
                'state_province' => 'Texas',
                'city' => 'Houston',
                'contact_email' => 'texas@ugalumni.org',
                'contact_phone' => '+1 713 555 0100',
                'is_active' => true,
            ],

            // United Kingdom City Chapters
            [
                'name' => 'London Chapter',
                'code' => 'GB-LDN',
                'description' => 'University of Ghana alumni in London and surrounding areas.',
                'type' => 'city',
                'country_code' => 'GB',
                'country_name' => 'United Kingdom',
                'state_province' => 'England',
                'city' => 'London',
                'contact_email' => 'london@ugalumni.org',
                'contact_phone' => '+44 20 7123 4567',
                'is_active' => true,
            ],
            [
                'name' => 'Manchester Chapter',
                'code' => 'GB-MAN',
                'description' => 'University of Ghana alumni in Manchester and the North West.',
                'type' => 'city',
                'country_code' => 'GB',
                'country_name' => 'United Kingdom',
                'state_province' => 'England',
                'city' => 'Manchester',
                'contact_email' => 'manchester@ugalumni.org',
                'contact_phone' => '+44 161 123 4567',
                'is_active' => true,
            ],

            // Nigeria Chapter (Country-wide)
            [
                'name' => 'Nigeria Chapter',
                'code' => 'NG',
                'description' => 'The Nigeria chapter serves all University of Ghana alumni residing in Nigeria.',
                'type' => 'country',
                'country_code' => 'NG',
                'country_name' => 'Nigeria',
                'state_province' => null,
                'city' => null,
                'contact_email' => 'nigeria@ugalumni.org',
                'contact_phone' => '+234 123 456 789',
                'is_active' => true,
            ],

            // South Africa Chapter (Country-wide)
            [
                'name' => 'South Africa Chapter',
                'code' => 'ZA',
                'description' => 'The South Africa chapter serves all University of Ghana alumni residing in South Africa.',
                'type' => 'country',
                'country_code' => 'ZA',
                'country_name' => 'South Africa',
                'state_province' => null,
                'city' => null,
                'contact_email' => 'southafrica@ugalumni.org',
                'contact_phone' => '+27 21 123 4567',
                'is_active' => true,
            ],

            // Canada City Chapters
            [
                'name' => 'Toronto Chapter',
                'code' => 'CA-TOR',
                'description' => 'University of Ghana alumni in Toronto and the Greater Toronto Area.',
                'type' => 'city',
                'country_code' => 'CA',
                'country_name' => 'Canada',
                'state_province' => 'Ontario',
                'city' => 'Toronto',
                'contact_email' => 'toronto@ugalumni.org',
                'contact_phone' => '+1 416 555 0100',
                'is_active' => true,
            ],
            [
                'name' => 'Vancouver Chapter',
                'code' => 'CA-VAN',
                'description' => 'University of Ghana alumni in Vancouver and British Columbia.',
                'type' => 'city',
                'country_code' => 'CA',
                'country_name' => 'Canada',
                'state_province' => 'British Columbia',
                'city' => 'Vancouver',
                'contact_email' => 'vancouver@ugalumni.org',
                'contact_phone' => '+1 604 555 0100',
                'is_active' => true,
            ],
        ];

        foreach ($chapters as $chapter) {
            Chapter::create($chapter);
        }
    }
}

