<?php

namespace Tests\Feature\Labels;

use App\Models\Setting;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Regression tests for two defects in the label-preview endpoint:
 *
 *   1. The `settings[key]=value` override loop used to mutate the live
 *      `Setting::getSettings()` singleton, poisoning the static cache for
 *      every subsequent request on the same PHP-FPM worker. A clone keeps
 *      preview overrides request-scoped. An allowlist on the label2_ prefix
 *      bounds the attack surface to the keys the Settings UI actually sends.
 *
 *   2. The label2 renderer called `range(1, $settings->label2_empty_row_count)`
 *      and materialized the full result before `->take()` truncated it to
 *      the template's physical capacity. An unbounded request value therefore
 *      scaled CPU and memory with user input (CWE-400 / CWE-1284).
 */
class LabelPreviewRequestOverrideTest extends TestCase
{
    /**
     * Every test here hits the label preview endpoint which routes
     * through App\View\Label::render(), which calls TCPDF's Output()
     * with destination='I' (inline). TCPDF's 'I' mode `print`s the
     * PDF bytes, and PHPUnit's HTTP test harness doesn't always
     * capture those cleanly, so without a buffer the PDF stream
     * bleeds into CI stdout and interleaves with the progress line.
     * Save the outer OB level so we only clean down to our own.
     */
    private int $outerObLevel = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->outerObLevel = ob_get_level();
        ob_start();
    }

    protected function tearDown(): void
    {
        while (ob_get_level() > $this->outerObLevel) {
            ob_end_clean();
        }
        parent::tearDown();
    }

    #[Test]
    public function non_label2_override_does_not_mutate_the_setting_singleton(): void
    {
        $this->settings->set(['per_page' => 20]);

        $this->actingAs(User::factory()->create())
            ->get(route('labels.show', ['labelName' => 'DefaultLabel']).'?settings[per_page]=1');

        $this->assertSame(
            20,
            (int) Setting::getSettings()->per_page,
            'A non-allowlisted override must not reach Setting::getSettings().'
        );
    }

    #[Test]
    public function label2_override_does_not_mutate_the_setting_singleton(): void
    {
        $this->settings->set(['label2_title' => 'ORIGINAL']);

        $this->actingAs(User::factory()->create())
            ->get(route('labels.show', ['labelName' => 'DefaultLabel']).'?settings[label2_title]=HACKED');

        $this->assertSame(
            'ORIGINAL',
            Setting::getSettings()->label2_title,
            'Even an allowlisted override must stay scoped to the clone, not the singleton.'
        );
    }

    #[Test]
    public function preview_does_not_amplify_memory_when_label2_empty_row_count_is_a_large_integer(): void
    {
        // The clamp inside Label::materializeFieldsForAsset bounds the request
        // value to the template's physical capacity before range() runs. The
        // bench compares a baseline request (empty_row_count=1) against the
        // attacker scenario (25000). If the clamp works, peak memory does not
        // grow between them because both runs materialize at most
        // DefaultLabel::getSupportFields() rows.
        $this->settings->set([
            'label2_enable' => 1,
            'label2_template' => 'DefaultLabel',
            'label2_fields' => 'Asset Tag=asset_tag',
        ]);

        // TCPDF echoes bytes to the output stream, so wrap each request in
        // an output buffer to isolate the two renders from each other.
        ob_start();
        $this->actingAs(User::factory()->create())
            ->get(route('labels.show', ['labelName' => 'DefaultLabel']).'?settings[label2_empty_row_count]=1')
            ->assertOk();
        ob_end_clean();
        $baselinePeak = memory_get_peak_usage();

        ob_start();
        $this->actingAs(User::factory()->create())
            ->get(route('labels.show', ['labelName' => 'DefaultLabel']).'?settings[label2_empty_row_count]=25000')
            ->assertOk();
        ob_end_clean();
        $amplifiedPeak = memory_get_peak_usage();

        $delta = $amplifiedPeak - $baselinePeak;
        $this->assertLessThan(
            1 * 1024 * 1024,
            $delta,
            'Clamp failed. Attacker scenario grew peak memory by '.$delta.' bytes over the baseline.'
        );
    }

    #[Test]
    public function preview_renders_a_pdf_when_label2_empty_row_count_is_negative(): void
    {
        // range(1, -1) throws a ValueError in PHP 8, so a negative override
        // used to blow the request. The clamp drops anything below 0 to 0.
        $this->settings->set([
            'label2_enable' => 1,
            'label2_template' => 'DefaultLabel',
            'label2_fields' => 'Asset Tag=asset_tag',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('labels.show', ['labelName' => 'DefaultLabel']).'?settings[label2_empty_row_count]=-5')
            ->assertOk();
    }

    #[Test]
    public function preview_renders_a_pdf_when_label2_empty_row_count_is_non_numeric(): void
    {
        // `(int) 'banana'` is 0 in PHP, so the clamp also handles junk input.
        $this->settings->set([
            'label2_enable' => 1,
            'label2_template' => 'DefaultLabel',
            'label2_fields' => 'Asset Tag=asset_tag',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('labels.show', ['labelName' => 'DefaultLabel']).'?settings[label2_empty_row_count]=banana')
            ->assertOk();
    }
}
