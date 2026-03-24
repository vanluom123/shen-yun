<?php

namespace Tests\Feature;

use Tests\TestCase;

class FloatingContactWidgetTest extends TestCase
{
    private function setConfig(string $hotline = '0901234567', string $zaloUrl = 'https://zalo.me/0901234567'): void
    {
        config([
            'rsvp.hotline_number' => $hotline,
            'rsvp.zalo_url'       => $zaloUrl,
        ]);
    }

    /** Example 1.1 — Widget container present on public /login page */
    public function test_widget_container_present_on_login_page(): void
    {
        $this->setConfig();

        $html = $this->get('/login')->getContent();

        $this->assertStringContainsString('fixed bottom-6 right-4 z-50', $html);
    }

    /** Example 1.2 — Phone button appears before Zalo button in the DOM */
    public function test_phone_button_appears_before_zalo_button(): void
    {
        $this->setConfig();

        $html = $this->get('/login')->getContent();

        $phonePos = strpos($html, 'href="tel:');
        $zaloPos  = strpos($html, 'href="https://zalo.me');

        $this->assertNotFalse($phonePos, 'Phone button href not found');
        $this->assertNotFalse($zaloPos, 'Zalo button href not found');
        $this->assertLessThan($zaloPos, $phonePos, 'Phone button should appear before Zalo button');
    }

    /** Example 1.3 — Widget NOT present on admin login page */
    public function test_widget_not_present_on_admin_login_page(): void
    {
        $this->setConfig();

        $html = $this->get('/admin/login')->getContent();

        $this->assertStringNotContainsString('fixed bottom-6 right-4 z-50', $html);
    }

    /** Example 2.1 — Phone button contains an <svg> element */
    public function test_phone_button_contains_svg(): void
    {
        $this->setConfig();

        $html = $this->get('/login')->getContent();

        // Find the phone anchor and check it contains an svg before the next anchor
        $phoneStart = strpos($html, 'href="tel:');
        $this->assertNotFalse($phoneStart);

        // Extract a reasonable chunk after the phone anchor opening
        $chunk = substr($html, $phoneStart, 500);

        $this->assertStringContainsString('<svg', $chunk);
    }

    /** Example 2.3 — Phone button aria-label contains "Gọi hotline" */
    public function test_phone_button_aria_label_contains_goi_hotline(): void
    {
        $this->setConfig();

        $html = $this->get('/login')->getContent();

        $this->assertStringContainsString('Gọi hotline', $html);
    }

    /** Example 3.1 — Zalo button contains an <svg> element */
    public function test_zalo_button_contains_svg(): void
    {
        $this->setConfig();

        $html = $this->get('/login')->getContent();

        $zaloStart = strpos($html, 'href="https://zalo.me');
        $this->assertNotFalse($zaloStart);

        $chunk = substr($html, $zaloStart, 500);

        $this->assertStringContainsString('<svg', $chunk);
    }

    /** Example 3.3 — Zalo button aria-label contains "Zalo" */
    public function test_zalo_button_aria_label_contains_zalo(): void
    {
        $this->setConfig();

        $html = $this->get('/login')->getContent();

        $this->assertStringContainsString('aria-label="Liên hệ qua Zalo"', $html);
    }

    /** Example 5.3 — With hotline_number empty, phone button not rendered */
    public function test_phone_button_not_rendered_when_hotline_empty(): void
    {
        $this->setConfig(hotline: '', zaloUrl: 'https://zalo.me/0901234567');

        $html = $this->get('/login')->getContent();

        $this->assertStringNotContainsString('href="tel:', $html);
    }

    /** Example 5.4 — With zalo_url empty, Zalo button not rendered */
    public function test_zalo_button_not_rendered_when_zalo_url_empty(): void
    {
        $this->setConfig(hotline: '0901234567', zaloUrl: '');

        $html = $this->get('/login')->getContent();

        $this->assertStringNotContainsString('href="https://zalo.me', $html);
    }
}
