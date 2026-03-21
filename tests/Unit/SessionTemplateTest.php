<?php

namespace Tests\Unit;

use App\Models\SessionTemplate;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_template_belongs_to_venue(): void
    {
        $venue = Venue::factory()->create();
        $template = SessionTemplate::create(['venue_id' => $venue->id]);

        $this->assertInstanceOf(Venue::class, $template->venue);
        $this->assertEquals($venue->id, $template->venue->id);
    }

    public function test_session_template_has_fillable_venue_id(): void
    {
        $venue = Venue::factory()->create();
        $template = SessionTemplate::create(['venue_id' => $venue->id]);

        $this->assertEquals($venue->id, $template->venue_id);
    }

    public function test_venue_id_must_be_unique(): void
    {
        $venue = Venue::factory()->create();
        SessionTemplate::create(['venue_id' => $venue->id]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        SessionTemplate::create(['venue_id' => $venue->id]);
    }

    public function test_venue_has_one_session_template(): void
    {
        $venue = Venue::factory()->create();
        $template = SessionTemplate::create(['venue_id' => $venue->id]);

        $this->assertInstanceOf(SessionTemplate::class, $venue->sessionTemplate);
        $this->assertEquals($template->id, $venue->sessionTemplate->id);
    }
}
