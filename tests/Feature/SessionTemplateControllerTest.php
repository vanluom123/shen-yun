<?php

namespace Tests\Feature;

use App\Models\SessionTemplate;
use App\Models\TemplateSlot;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionTemplateControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set admin session for authentication
        session(['admin_authed' => true]);
    }

    public function test_create_displays_venues_without_templates(): void
    {
        $venueWithTemplate = Venue::factory()->create(['name' => 'Venue With Template']);
        $venueWithoutTemplate = Venue::factory()->create(['name' => 'Venue Without Template']);
        
        SessionTemplate::factory()->create(['venue_id' => $venueWithTemplate->id]);

        $response = $this->get('/admin/templates/create');

        $response->assertStatus(200);
        $response->assertSee('Venue Without Template');
        $response->assertDontSee('Venue With Template');
    }

    public function test_store_creates_template_with_slots(): void
    {
        $venue = Venue::factory()->create();

        $response = $this->post('/admin/templates', [
                'venue_id' => $venue->id,
                'slots' => [
                    [
                        'day_of_week' => 0,
                        'time' => '14:30',
                        'default_capacity' => 50,
                    ],
                    [
                        'day_of_week' => 3,
                        'time' => '19:00',
                        'default_capacity' => 75,
                    ],
                ],
            ]);

        $response->assertRedirect('/admin/sessions');
        $response->assertSessionHas('status', 'Đã tạo mẫu lịch chiếu.');

        $this->assertDatabaseHas('session_templates', [
            'venue_id' => $venue->id,
        ]);

        $template = SessionTemplate::where('venue_id', $venue->id)->first();
        $this->assertCount(2, $template->slots);

        $this->assertDatabaseHas('template_slots', [
            'session_template_id' => $template->id,
            'day_of_week' => 0,
            'time' => '14:30',
            'default_capacity' => 50,
        ]);

        $this->assertDatabaseHas('template_slots', [
            'session_template_id' => $template->id,
            'day_of_week' => 3,
            'time' => '19:00',
            'default_capacity' => 75,
        ]);
    }

    public function test_store_validates_venue_uniqueness(): void
    {
        $venue = Venue::factory()->create();
        SessionTemplate::factory()->create(['venue_id' => $venue->id]);

        $response = $this->post('/admin/templates', [
                'venue_id' => $venue->id,
                'slots' => [
                    [
                        'day_of_week' => 0,
                        'time' => '14:30',
                        'default_capacity' => 50,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('venue_id');
    }

    public function test_store_validates_slot_day_of_week(): void
    {
        $venue = Venue::factory()->create();

        $response = $this->post('/admin/templates', [
                'venue_id' => $venue->id,
                'slots' => [
                    [
                        'day_of_week' => 7, // Invalid: must be 0-6
                        'time' => '14:30',
                        'default_capacity' => 50,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('slots.0.day_of_week');
    }

    public function test_store_validates_slot_capacity(): void
    {
        $venue = Venue::factory()->create();

        $response = $this->post('/admin/templates', [
                'venue_id' => $venue->id,
                'slots' => [
                    [
                        'day_of_week' => 0,
                        'time' => '14:30',
                        'default_capacity' => 0, // Invalid: must be >= 1
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('slots.0.default_capacity');
    }

    public function test_store_validates_slot_time_format(): void
    {
        $venue = Venue::factory()->create();

        $response = $this->post('/admin/templates', [
                'venue_id' => $venue->id,
                'slots' => [
                    [
                        'day_of_week' => 0,
                        'time' => '25:99', // Invalid time format
                        'default_capacity' => 50,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('slots.0.time');
    }

    public function test_store_requires_at_least_one_slot(): void
    {
        $venue = Venue::factory()->create();

        $response = $this->post('/admin/templates', [
                'venue_id' => $venue->id,
                'slots' => [],
            ]);

        $response->assertSessionHasErrors('slots');
    }

    public function test_edit_displays_template_with_slots(): void
    {
        $venue = Venue::factory()->create(['name' => 'Test Venue']);
        $template = SessionTemplate::factory()->create(['venue_id' => $venue->id]);
        
        TemplateSlot::factory()->create([
            'session_template_id' => $template->id,
            'day_of_week' => 0,
            'time' => '14:30',
            'default_capacity' => 50,
        ]);

        $response = $this->get("/admin/templates/{$template->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Test Venue');
        $response->assertSee('14:30');
    }

    public function test_update_modifies_template_slots(): void
    {
        $venue = Venue::factory()->create();
        $template = SessionTemplate::factory()->create(['venue_id' => $venue->id]);
        
        // Create initial slots
        TemplateSlot::factory()->create([
            'session_template_id' => $template->id,
            'day_of_week' => 0,
            'time' => '14:30',
            'default_capacity' => 50,
        ]);
        
        TemplateSlot::factory()->create([
            'session_template_id' => $template->id,
            'day_of_week' => 3,
            'time' => '19:00',
            'default_capacity' => 75,
        ]);

        // Update with new slots (replace strategy)
        $response = $this->put("/admin/templates/{$template->id}", [
            'venue_id' => $venue->id,
            'slots' => [
                [
                    'day_of_week' => 1,
                    'time' => '10:00',
                    'default_capacity' => 30,
                ],
                [
                    'day_of_week' => 5,
                    'time' => '18:00',
                    'default_capacity' => 60,
                ],
                [
                    'day_of_week' => 6,
                    'time' => '20:00',
                    'default_capacity' => 80,
                ],
            ],
        ]);

        $response->assertRedirect('/admin/sessions');
        $response->assertSessionHas('status', 'Đã cập nhật mẫu lịch chiếu.');

        // Verify old slots are deleted
        $this->assertDatabaseMissing('template_slots', [
            'session_template_id' => $template->id,
            'day_of_week' => 0,
            'time' => '14:30',
        ]);

        $this->assertDatabaseMissing('template_slots', [
            'session_template_id' => $template->id,
            'day_of_week' => 3,
            'time' => '19:00',
        ]);

        // Verify new slots are created
        $template->refresh();
        $this->assertCount(3, $template->slots);

        $this->assertDatabaseHas('template_slots', [
            'session_template_id' => $template->id,
            'day_of_week' => 1,
            'time' => '10:00',
            'default_capacity' => 30,
        ]);

        $this->assertDatabaseHas('template_slots', [
            'session_template_id' => $template->id,
            'day_of_week' => 5,
            'time' => '18:00',
            'default_capacity' => 60,
        ]);

        $this->assertDatabaseHas('template_slots', [
            'session_template_id' => $template->id,
            'day_of_week' => 6,
            'time' => '20:00',
            'default_capacity' => 80,
        ]);
    }

    public function test_update_validates_all_slot_fields(): void
    {
        $venue = Venue::factory()->create();
        $template = SessionTemplate::factory()->create(['venue_id' => $venue->id]);

        // Test invalid day_of_week
        $response = $this->put("/admin/templates/{$template->id}", [
            'venue_id' => $venue->id,
            'slots' => [
                [
                    'day_of_week' => 8,
                    'time' => '14:30',
                    'default_capacity' => 50,
                ],
            ],
        ]);

        $response->assertSessionHasErrors('slots.0.day_of_week');

        // Test invalid capacity
        $response = $this->put("/admin/templates/{$template->id}", [
            'venue_id' => $venue->id,
            'slots' => [
                [
                    'day_of_week' => 0,
                    'time' => '14:30',
                    'default_capacity' => 0,
                ],
            ],
        ]);

        $response->assertSessionHasErrors('slots.0.default_capacity');

        // Test invalid time format
        $response = $this->put("/admin/templates/{$template->id}", [
            'venue_id' => $venue->id,
            'slots' => [
                [
                    'day_of_week' => 0,
                    'time' => 'invalid',
                    'default_capacity' => 50,
                ],
            ],
        ]);

        $response->assertSessionHasErrors('slots.0.time');
    }

    public function test_update_prevents_venue_change(): void
    {
        $venue1 = Venue::factory()->create();
        $venue2 = Venue::factory()->create();
        $template = SessionTemplate::factory()->create(['venue_id' => $venue1->id]);

        $response = $this->put("/admin/templates/{$template->id}", [
            'venue_id' => $venue2->id, // Trying to change venue
            'slots' => [
                [
                    'day_of_week' => 0,
                    'time' => '14:30',
                    'default_capacity' => 50,
                ],
            ],
        ]);

        $response->assertSessionHasErrors('venue_id');
        
        // Verify venue hasn't changed
        $template->refresh();
        $this->assertEquals($venue1->id, $template->venue_id);
    }

    public function test_update_requires_at_least_one_slot(): void
    {
        $venue = Venue::factory()->create();
        $template = SessionTemplate::factory()->create(['venue_id' => $venue->id]);

        $response = $this->put("/admin/templates/{$template->id}", [
            'venue_id' => $venue->id,
            'slots' => [],
        ]);

        $response->assertSessionHasErrors('slots');
    }

    public function test_destroy_deletes_template_and_slots(): void
    {
        $venue = Venue::factory()->create();
        $template = SessionTemplate::factory()->create(['venue_id' => $venue->id]);
        
        // Create slots
        TemplateSlot::factory()->create([
            'session_template_id' => $template->id,
            'day_of_week' => 0,
            'time' => '14:30',
            'default_capacity' => 50,
        ]);
        
        TemplateSlot::factory()->create([
            'session_template_id' => $template->id,
            'day_of_week' => 3,
            'time' => '19:00',
            'default_capacity' => 75,
        ]);

        $response = $this->delete("/admin/templates/{$template->id}");

        $response->assertRedirect('/admin/sessions');
        $response->assertSessionHas('status', 'Đã xóa mẫu lịch chiếu.');

        // Verify template is deleted
        $this->assertDatabaseMissing('session_templates', [
            'id' => $template->id,
        ]);

        // Verify slots are cascade deleted
        $this->assertDatabaseMissing('template_slots', [
            'session_template_id' => $template->id,
        ]);
    }

    public function test_destroy_preserves_existing_event_sessions(): void
    {
        $venue = Venue::factory()->create();
        $template = SessionTemplate::factory()->create(['venue_id' => $venue->id]);
        
        // Create a slot
        TemplateSlot::factory()->create([
            'session_template_id' => $template->id,
            'day_of_week' => 0,
            'time' => '14:30',
            'default_capacity' => 50,
        ]);

        // Create an EventSession that was generated from this template
        $session = \App\Models\EventSession::create([
            'venue_id' => $venue->id,
            'starts_at' => now()->next('Sunday')->setTime(14, 30),
            'capacity_total' => 50,
            'capacity_reserved' => 0,
            'registration_status' => 'open',
        ]);

        // Delete the template
        $response = $this->delete("/admin/templates/{$template->id}");

        $response->assertRedirect('/admin/sessions');

        // Verify template is deleted
        $this->assertDatabaseMissing('session_templates', [
            'id' => $template->id,
        ]);

        // Verify EventSession still exists (not affected by template deletion)
        $this->assertDatabaseHas('event_sessions', [
            'id' => $session->id,
            'venue_id' => $venue->id,
        ]);
    }
}
