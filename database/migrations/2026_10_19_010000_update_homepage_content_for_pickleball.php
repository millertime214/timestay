<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $languageId = DB::table('languages')->where('code', 'en')->value('id');

        if (!$languageId) {
            return;
        }

        DB::table('section_contents')->where('language_id', $languageId)->update([
            'hero_section_title' => 'Find and Book Pickleball Courts Near You',
            'hero_section_subtitle' => 'Search local Pickleball Sites, compare Court schedules, reserve an available time, or join an Open Play session.',
            'featured_section_title' => 'Everything You Need to Get on Court',
            'featured_section_text' => 'Explore local Sites, review Court information and amenities, choose an available schedule, and complete your reservation through one convenient booking experience.',
            'featured_room_section_title' => 'Popular Pickleball Courts',
            'featured_room_section_button_text' => 'View All Courts',
            'benifit_section_title' => 'More Ways to Play Pickleball',
            'updated_at' => now(),
        ]);

        $features = [
            1 => [
                'title' => 'Clear Court Availability',
                'subtitle' => 'Review available dates, start times, durations, and rates before you book.',
                'text' => 'Live',
            ],
            2 => [
                'title' => 'Local Sites and Amenities',
                'subtitle' => 'Compare nearby Pickleball Sites, Court details, facilities, and playing options.',
                'text' => 'Local',
            ],
            3 => [
                'title' => 'Flexible Hourly Booking',
                'subtitle' => 'Reserve the Court time your group needs without committing to a membership.',
                'text' => 'Flexible',
            ],
        ];

        foreach ($features as $serial => $feature) {
            DB::table('features')
                ->where('language_id', $languageId)
                ->where('serial_number', $serial)
                ->update($feature + ['updated_at' => now()]);
        }

        $benefits = [
            ['title' => 'Court Rentals', 'text' => 'Reserve a Court for your group at an available date and time.'],
            ['title' => 'Open Play Sessions', 'text' => 'Register individually and meet other local Pickleball players.'],
            ['title' => 'Skill-Friendly Games', 'text' => 'Find session categories and player information that suit your level.'],
            ['title' => 'Organized Play', 'text' => 'Join Site-managed sessions with clear schedules and player limits.'],
        ];

        $benefitIds = DB::table('benifits')
            ->where('language_id', $languageId)
            ->orderBy('id')
            ->limit(count($benefits))
            ->pluck('id');

        foreach ($benefitIds as $index => $benefitId) {
            DB::table('benifits')->where('id', $benefitId)->update(
                $benefits[$index] + ['updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        // Homepage copy remains editable in the admin panel. Avoid overwriting
        // administrator changes made after this content migration is applied.
    }
};
