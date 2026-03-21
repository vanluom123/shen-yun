<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SessionTemplate;
use App\Models\TemplateSlot;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SessionTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = SessionTemplate::query()
            ->with(['venue', 'slots'])
            ->orderBy('id')
            ->paginate(20);

        return view('admin.templates.index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get venues that don't have a template yet
        $venues = Venue::query()
            ->whereDoesntHave('sessionTemplate')
            ->orderBy('name')
            ->get();

        return view('admin.templates.create', [
            'venues' => $venues,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate venue_id
        $request->validate([
            'venue_id' => 'required|exists:venues,id|unique:session_templates,venue_id',
            'slots' => 'required|array|min:1',
            'slots.*.day_of_week' => 'required|integer|between:0,6',
            'slots.*.time' => 'required|date_format:H:i',
            'slots.*.default_capacity' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            // Create the SessionTemplate
            $template = SessionTemplate::create([
                'venue_id' => $request->venue_id,
            ]);

            // Create associated TemplateSlots
            foreach ($request->slots as $slotData) {
                TemplateSlot::create([
                    'session_template_id' => $template->id,
                    'day_of_week' => $slotData['day_of_week'],
                    'time' => $slotData['time'],
                    'default_capacity' => $slotData['default_capacity'],
                ]);
            }
        });

        return redirect()->to('/admin/sessions')->with('status', 'Đã tạo mẫu lịch chiếu.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SessionTemplate $template)
    {
        // Load the template with its slots
        $template->load('slots', 'venue');

        // For edit, we only show the current venue (can't change venue)
        $venues = Venue::query()
            ->where('id', $template->venue_id)
            ->get();

        return view('admin.templates.edit', [
            'template' => $template,
            'venues' => $venues,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SessionTemplate $template)
    {
        // Validate the request
        $request->validate([
            'venue_id' => 'required|exists:venues,id',
            'slots' => 'required|array|min:1',
            'slots.*.day_of_week' => 'required|integer|between:0,6',
            'slots.*.time' => 'required|date_format:H:i',
            'slots.*.default_capacity' => 'required|integer|min:1',
        ]);

        // Ensure venue_id matches the template's venue (can't change venue)
        if ($request->venue_id != $template->venue_id) {
            throw ValidationException::withMessages([
                'venue_id' => 'Không thể thay đổi địa điểm của mẫu lịch chiếu.',
            ]);
        }

        DB::transaction(function () use ($request, $template) {
            // Delete existing slots (replace strategy)
            $template->slots()->delete();

            // Create new slots from request data
            foreach ($request->slots as $slotData) {
                TemplateSlot::create([
                    'session_template_id' => $template->id,
                    'day_of_week' => $slotData['day_of_week'],
                    'time' => $slotData['time'],
                    'default_capacity' => $slotData['default_capacity'],
                ]);
            }
        });

        return redirect()->to('/admin/sessions')->with('status', 'Đã cập nhật mẫu lịch chiếu.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SessionTemplate $template)
    {
        // Delete the template (cascade delete will remove associated slots via foreign key)
        $template->delete();

        return redirect()->to('/admin/sessions')->with('status', 'Đã xóa mẫu lịch chiếu.');
    }

}
