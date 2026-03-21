<?php

namespace Tests\Unit\Models;

use App\Models\SessionTemplate;
use App\Models\TemplateSlot;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateSlotTest extends TestCase
{
    use RefreshDatabase;

    public function test_template_slot_can_be_created_with_valid_data(): void
    {
        $venue = Venue::factory()->create();
        $template = SessionTemplate::create(['venue_id' => $venue->id]);

        $slot = TemplateSlot::create([
            'session_template_id' => $template->id,
            'day_of_week' => 0,
            'time' => '14:30',
            'default_capacity' => 50,
        ]);

        $this->assertDatabaseHas('template_slots', [
            'session_template_id' => $template->id,
            'day_of_week' => 0,
            'time' => '14:30',
            'default_capacity' => 50,
        ]);
    }

    public function test_template_slot_belongs_to_session_template(): void
    {
        $venue = Venue::factory()->create();
        $template = SessionTemplate::create(['venue_id' => $venue->id]);
        $slot = TemplateSlot::create([
            'session_template_id' => $template->id,
            'day_of_week' => 1,
            'time' => '10:00',
            'default_capacity' => 30,
        ]);

        $this->assertInstanceOf(SessionTemplate::class, $slot->sessionTemplate);
        $this->assertEquals($template->id, $slot->sessionTemplate->id);
    }

    public function test_template_slot_casts_day_of_week_as_integer(): void
    {
        $venue = Venue::factory()->create();
        $template = SessionTemplate::create(['venue_id' => $venue->id]);
        $slot = TemplateSlot::create([
            'session_template_id' => $template->id,
            'day_of_week' => '3',
            'time' => '15:00',
            'default_capacity' => 40,
        ]);

        $this->assertIsInt($slot->day_of_week);
        $this->assertEquals(3, $slot->day_of_week);
    }

    public function test_template_slot_casts_default_capacity_as_integer(): void
    {
        $venue = Venue::factory()->create();
        $template = SessionTemplate::create(['venue_id' => $venue->id]);
        $slot = TemplateSlot::create([
            'session_template_id' => $template->id,
            'day_of_week' => 2,
            'time' => '16:00',
            'default_capacity' => '25',
        ]);

        $this->assertIsInt($slot->default_capacity);
        $this->assertEquals(25, $slot->default_capacity);
    }

    public function test_template_slot_validation_rules_exist(): void
    {
        $rules = TemplateSlot::rules();

        $this->assertArrayHasKey('session_template_id', $rules);
        $this->assertArrayHasKey('day_of_week', $rules);
        $this->assertArrayHasKey('time', $rules);
        $this->assertArrayHasKey('default_capacity', $rules);
    }
}
