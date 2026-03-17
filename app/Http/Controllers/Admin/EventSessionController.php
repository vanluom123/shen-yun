<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventSession;
use App\Models\Venue;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class EventSessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sessions = EventSession::query()
            ->with('venue')
            ->orderByDesc('starts_at')
            ->paginate(20);

        return view('admin.sessions.index', [
            'sessions' => $sessions,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $venues = Venue::query()->orderBy('name')->get();

        $defaultVenue = Venue::query()
            ->whereRaw('LOWER(name) = ?', ['nx-lp 96 vinhomes grand park'])
            ->first();

        $defaultStartsAt = Carbon::now()
            ->startOfWeek(Carbon::SUNDAY)
            ->setTime(14, 30, 0);

        return view('admin.sessions.create', [
            'venues' => $venues,
            'default_venue_id' => $defaultVenue?->id,
            'default_starts_at' => $defaultStartsAt->format('Y-m-d\TH:i'),
            'default_capacity' => 36,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $venues = Venue::query()->pluck('id')->all();
        $data = $request->validate([
            'venue_id' => ['required', 'integer', 'in:'.implode(',', $venues)],
            'starts_at' => ['required', 'date'],
            'capacity_total' => ['required', 'integer', 'min:1', 'max:100000'],
            'registration_status' => ['required', 'string', 'max:32'],
        ]);

        $data['capacity_reserved'] = 0;

        EventSession::query()->create($data);

        return redirect()->to('/admin/sessions')->with('status', 'Đã tạo trình chiếu.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EventSession $session)
    {
        $venues = Venue::query()->orderBy('name')->get();

        return view('admin.sessions.edit', [
            'session' => $session->load('venue'),
            'venues' => $venues,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EventSession $session)
    {
        $venues = Venue::query()->pluck('id')->all();
        $data = $request->validate([
            'venue_id' => ['required', 'integer', 'in:'.implode(',', $venues)],
            'starts_at' => ['required', 'date'],
            'capacity_total' => ['required', 'integer', 'min:1', 'max:100000'],
            'registration_status' => ['required', 'string', 'max:32'],
        ]);

        $session->update($data);

        return redirect()->to('/admin/sessions')->with('status', 'Đã cập nhật trình chiếu.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EventSession $session)
    {
        $session->delete();

        return redirect()->to('/admin/sessions')->with('status', 'Đã xoá trình chiếu.');
    }

    /**
     * Remove multiple sessions from storage.
     */
    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('session_ids', []);

        if (empty($ids)) {
            return redirect()->to('/admin/sessions')->with('status', 'Vui lòng chọn ít nhất một trình chiếu.');
        }

        $count = EventSession::query()->whereIn('id', $ids)->delete();

        return redirect()->to('/admin/sessions')->with('status', "Đã xoá {$count} trình chiếu.");
    }
}
