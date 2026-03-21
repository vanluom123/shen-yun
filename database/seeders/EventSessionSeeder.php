<?php

namespace Database\Seeders;

use App\Models\EventSession;
use App\Models\Venue;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class EventSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tz = 'Asia/Ho_Chi_Minh';

        $venueDefault = Venue::query()->where('name', 'NX-LP 96 vinhomes grand park')->first();
        if (! $venueDefault) {
            return;
        }

        $now = Carbon::now($tz);
        $startsAt = $now->copy()->next(Carbon::SUNDAY)->setTime(14, 30, 0);

        // If we're already within 24h of this Sunday's showtime, seed next week's Sunday instead.
        if ($now->greaterThanOrEqualTo($startsAt->copy()->subHours(24))) {
            $startsAt = $startsAt->addWeek();
        }

        $sessions = [
            [$venueDefault->id, $startsAt, 36],
        ];

        foreach ($sessions as [$venueId, $startsAt, $capacity]) {
            EventSession::query()->updateOrCreate(
                ['venue_id' => $venueId, 'starts_at' => $startsAt->toDateTimeString()],
                [
                    'capacity_total' => $capacity,
                    'capacity_reserved' => 0,
                    'registration_status' => 'open',
                ],
            );
        }
    }
}
