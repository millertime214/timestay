<?php

namespace Tests\Unit;

use App\Support\CourtDailyAvailability;
use PHPUnit\Framework\TestCase;

class CourtDailyAvailabilityTest extends TestCase
{
    public function test_availability_requires_owner_verification_and_subscription(): void
    {
        $this->assertSame(
            'owner_registration_required',
            CourtDailyAvailability::resolveEligibilityStatus(false, false, false)
        );
        $this->assertSame(
            'verification_required',
            CourtDailyAvailability::resolveEligibilityStatus(true, false, true)
        );
        $this->assertSame(
            'subscription_required',
            CourtDailyAvailability::resolveEligibilityStatus(true, true, false)
        );
        $this->assertSame(
            'available',
            CourtDailyAvailability::resolveEligibilityStatus(true, true, true)
        );
    }

    public function test_it_marks_each_overlapping_hour_as_occupied(): void
    {
        $schedule = CourtDailyAvailability::buildSlots(
            '06:00:00',
            '09:00:00',
            '2026-10-12',
            [['start' => '2026-10-12 07:30:00', 'end' => '2026-10-12 08:30:00']],
            12,
            'Asia/Manila'
        );

        $this->assertCount(3, $schedule['slots']);
        $this->assertSame([false, true, true], array_column($schedule['slots'], 'occupied'));
        $this->assertSame('2026-10-12', $schedule['slots'][0]['date']);
        $this->assertSame('06:00:00', $schedule['slots'][0]['start_time']);
        $this->assertSame('07:00:00', $schedule['slots'][0]['end_time']);
        $this->assertSame(1.0, $schedule['slots'][0]['hours']);
    }

    public function test_an_interval_ending_at_an_hour_boundary_does_not_occupy_the_next_hour(): void
    {
        $schedule = CourtDailyAvailability::buildSlots(
            '06:00:00',
            '08:00:00',
            '2026-10-12',
            [['start' => '2026-10-12 06:00:00', 'end' => '2026-10-12 07:00:00']],
            12,
            'Asia/Manila'
        );

        $this->assertSame([true, false], array_column($schedule['slots'], 'occupied'));
    }

    public function test_equal_open_and_close_times_produce_a_full_day_schedule(): void
    {
        $schedule = CourtDailyAvailability::buildSlots(
            '00:00:00',
            '00:00:00',
            '2026-10-12',
            [],
            24,
            'Asia/Manila'
        );

        $this->assertCount(24, $schedule['slots']);
        $this->assertSame('00:00', $schedule['open']);
        $this->assertSame('00:00', $schedule['close']);
    }

    public function test_overnight_operating_hours_continue_into_the_next_day(): void
    {
        $schedule = CourtDailyAvailability::buildSlots(
            '22:00:00',
            '02:00:00',
            '2026-10-12',
            [['start' => '2026-10-13 00:00:00', 'end' => '2026-10-13 01:00:00']],
            24,
            'Asia/Manila'
        );

        $this->assertCount(4, $schedule['slots']);
        $this->assertSame([false, false, true, false], array_column($schedule['slots'], 'occupied'));
    }
}
