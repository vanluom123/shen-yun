<?php

namespace App\Services;

use App\Models\EventSession;
use App\Models\SessionTemplate;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SessionGeneratorService
{
    /**
     * Ensure sessions exist for current + next ISO week for a venue.
     * Called automatically on public page load.
     */
    public function ensureSessionsForVenue(int $venueId): void
    {
        $now = Carbon::now();
        
        // Get current ISO week (Monday start)
        $currentWeekStart = $now->copy()->startOfWeek(Carbon::MONDAY);
        
        // Get next ISO week (Monday start)
        $nextWeekStart = $currentWeekStart->copy()->addWeek();
        
        // Check if current week needs sessions
        $currentWeekHasSessions = EventSession::query()
            ->where('venue_id', $venueId)
            ->whereBetween('starts_at', [
                $currentWeekStart,
                $currentWeekStart->copy()->endOfWeek(Carbon::SUNDAY)
            ])
            ->exists();
        
        if (!$currentWeekHasSessions) {
            $this->generateForVenueWeek($venueId, $currentWeekStart);
        }
        
        // Check if next week needs sessions
        $nextWeekHasSessions = EventSession::query()
            ->where('venue_id', $venueId)
            ->whereBetween('starts_at', [
                $nextWeekStart,
                $nextWeekStart->copy()->endOfWeek(Carbon::SUNDAY)
            ])
            ->exists();
        
        if (!$nextWeekHasSessions) {
            $this->generateForVenueWeek($venueId, $nextWeekStart);
        }
    }

    /**
     * Generate sessions for all venues with templates.
     * Called manually from admin panel.
     * 
     * @return int Number of sessions created
     */
    public function generateForAllVenues(): int
    {
        $totalCreated = 0;
        
        $venues = Venue::query()
            ->whereHas('sessionTemplate')
            ->get();
        
        $now = Carbon::now();
        $currentWeekStart = $now->copy()->startOfWeek(Carbon::MONDAY);
        $nextWeekStart = $currentWeekStart->copy()->addWeek();
        
        foreach ($venues as $venue) {
            // Generate for current week
            $currentWeekHasSessions = EventSession::query()
                ->where('venue_id', $venue->id)
                ->whereBetween('starts_at', [
                    $currentWeekStart,
                    $currentWeekStart->copy()->endOfWeek(Carbon::SUNDAY)
                ])
                ->exists();
            
            if (!$currentWeekHasSessions) {
                $totalCreated += $this->generateForVenueWeek($venue->id, $currentWeekStart);
            }
            
            // Generate for next week
            $nextWeekHasSessions = EventSession::query()
                ->where('venue_id', $venue->id)
                ->whereBetween('starts_at', [
                    $nextWeekStart,
                    $nextWeekStart->copy()->endOfWeek(Carbon::SUNDAY)
                ])
                ->exists();
            
            if (!$nextWeekHasSessions) {
                $totalCreated += $this->generateForVenueWeek($venue->id, $nextWeekStart);
            }
        }
        
        return $totalCreated;
    }

    /**
     * Generate sessions for a specific venue and week range.
     * 
     * @param int $venueId
     * @param Carbon $weekStart Monday of the target week
     * @return int Number of sessions created
     */
    private function generateForVenueWeek(int $venueId, Carbon $weekStart): int
    {
        // Load venue with template and slots
        $venue = Venue::with('sessionTemplate.slots')->find($venueId);
        
        if (!$venue) {
            return 0;
        }
        
        // If no template exists, return 0 (no fallback behavior)
        $template = $venue->sessionTemplate;
        if (!$template || $template->slots->isEmpty()) {
            return 0;
        }
        
        $createdCount = 0;
        $venueTimezone = $venue->timezone ?? 'Asia/Ho_Chi_Minh';
        
        // For each template slot, create a session
        foreach ($template->slots as $slot) {
            // Calculate starts_at datetime in venue timezone
            // day_of_week: 0 = Sunday, 1 = Monday, ..., 6 = Saturday
            // weekStart is Monday (day 1), so we need to adjust
            $daysFromMonday = $slot->day_of_week === 0 ? 6 : $slot->day_of_week - 1;
            
            $startsAt = $weekStart->copy()
                ->addDays($daysFromMonday)
                ->setTimeFromTimeString($slot->time)
                ->timezone($venueTimezone);
            
            // Check for existing EventSession at same venue_id and starts_at
            $exists = EventSession::query()
                ->where('venue_id', $venueId)
                ->where('starts_at', $startsAt)
                ->exists();
            
            if ($exists) {
                continue; // Skip if already exists
            }
            
            // Create EventSession
            EventSession::create([
                'venue_id' => $venueId,
                'starts_at' => $startsAt,
                'capacity_total' => $slot->default_capacity,
                'capacity_reserved' => 0,
                'registration_status' => 'open',
            ]);
            
            $createdCount++;
        }
        
        // Recalculate capacity_reserved for all sessions in the generated week
        if ($createdCount > 0) {
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
            $this->recalculateCapacity($venueId, $weekStart, $weekEnd);
        }
        
        return $createdCount;
    }

    /**
     * Recalculate capacity_reserved for all sessions in a date range.
     */
    private function recalculateCapacity(int $venueId, Carbon $start, Carbon $end): void
    {
        // Query all EventSessions for venue in date range
        $sessions = EventSession::query()
            ->where('venue_id', $venueId)
            ->whereBetween('starts_at', [$start, $end])
            ->get();
        
        // For each session, recalculate capacity_reserved
        foreach ($sessions as $session) {
            EventSession::recalculateReserved($session->id);
        }
    }
}
