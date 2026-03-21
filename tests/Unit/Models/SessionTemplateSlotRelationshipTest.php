<?php

namespace Tests\Unit\Models;

use App\Models\SessionTemplate;
use App\Models\TemplateSlot;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionTemplateSlotRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_template_has_many_slots(): void
    {
        $venue = Venue::factory()->create();
        $template = SessionTemplate::create(['venue_id' => $venue->id]);

        $slot1 = TemplateSlot::create([
            'session_template_id' => $template->id,
            'day_of_week' => 0,
            'time' => '14:30',
            'default_capacity' => 50,
        ]);

        $slot2 = TemplateSlot::create([
            'session_template_id' => $template->id,
            'day_of_week' => 3,
            'time' => '19:00',
            'default_capacity' => 40,
        ]);

        $this->assertCount(2, $template->slots);
        $this->assertTrue($template->slots->contains($slot1));
        $this->assertTrue($template->slots->contains($slot2));
    }

    public function test_deleting_template_cascades_to_slots(): void
    {
        $venue = Venue::factory()->create();
        $template = SessionTemplate::create(['venue_id' => $venue->id]);

        TemplateSlot::create([
            'session_template_id' => $template->id,
            'day_of_week' => 0,
            'time' => '14:30',
            'default_capacity' => 50,
        ]);

        $templateId = $template->id;
        $template->delete();

        $this->assertDatabaseMissing('session_templates', ['id' => $templateId]);
        $this->assertDatabaseMissing('template_slots', ['session_template_id' => $templateId]);
    }
}
