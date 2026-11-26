<?php

namespace Tests\Feature\Blade;

use App\Http\Middleware\CheckForDebug;
use App\Http\Middleware\CheckForSetup;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CheckboxRowTest extends TestCase
{
    /**
     * Bind a temporary route that renders the checkbox-row component with
     * the caller-supplied data. Actual HTTP requests are what wire the
     * session onto the Request, so this is the shortest way to exercise
     * old() / session()->hasOldInput() correctly.
     */
    private function render(array $data, ?array $oldInput = null): string
    {
        Route::get('/__test/checkbox-row', function () use ($data) {
            return view('blade.form.checkbox-row', $data);
        })->middleware('web');

        $call = $this->withoutMiddleware([
            CheckForSetup::class,
            CheckForDebug::class,
        ]);
        if ($oldInput !== null) {
            $call = $call->withSession(['_old_input' => $oldInput]);
        }

        return $call->get('/__test/checkbox-row')->assertOk()->getContent();
    }

    private function item(): object
    {
        return new class
        {
            public bool $enabled = true;

            public bool $disabled_flag = false;

            public array $prefs = ['email', 'sms'];

            public static function rules()
            {
                return ['enabled' => 'required|boolean'];
            }
        };
    }

    public function test_single_fresh_render_reads_model_value_true()
    {
        $html = $this->render([
            'name' => 'enabled',
            'label' => 'Enabled',
            'item' => $this->item(),
        ]);

        $this->assertMatchesRegularExpression('/name="enabled"[^>]*checked/', $html);
    }

    public function test_single_fresh_render_reads_model_value_false()
    {
        $html = $this->render([
            'name' => 'disabled_flag',
            'label' => 'Disabled',
            'item' => $this->item(),
        ]);

        $this->assertDoesNotMatchRegularExpression('/name="disabled_flag"[^>]*checked/', $html);
    }

    public function test_single_redisplay_unchecked_does_not_fall_back_to_model()
    {
        $html = $this->render(
            data: [
                'name' => 'enabled',
                'label' => 'Enabled',
                'item' => $this->item(),
            ],
            oldInput: ['some_other_field' => 'x'],
        );

        $this->assertDoesNotMatchRegularExpression('/name="enabled"[^>]*checked/', $html);
    }

    public function test_single_redisplay_checked_shows_checked()
    {
        $html = $this->render(
            data: [
                'name' => 'disabled_flag',
                'label' => 'Disabled',
                'item' => $this->item(),
            ],
            oldInput: ['disabled_flag' => '1'],
        );

        $this->assertMatchesRegularExpression('/name="disabled_flag"[^>]*checked/', $html);
    }

    public function test_multi_fresh_render_from_model_array_checks_matching_options()
    {
        $html = $this->render([
            'name' => 'prefs',
            'label' => 'Notifications',
            'options' => ['email' => 'Email', 'sms' => 'SMS', 'push' => 'Push'],
            'item' => $this->item(),
        ]);

        $this->assertMatchesRegularExpression('/value="email"[^>]*checked/', $html);
        $this->assertMatchesRegularExpression('/value="sms"[^>]*checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/value="push"[^>]*checked/', $html);
    }

    public function test_multi_fresh_render_with_callable_selected()
    {
        $html = $this->render([
            'name' => 'modellist_displays',
            'label' => 'Model Columns',
            'options' => ['image' => 'Image', 'category' => 'Category'],
            'selected' => fn ($v) => $v === 'image',
        ]);

        $this->assertMatchesRegularExpression('/value="image"[^>]*checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/value="category"[^>]*checked/', $html);
    }

    public function test_multi_redisplay_reflects_old_selection_not_model()
    {
        $html = $this->render(
            data: [
                'name' => 'prefs',
                'label' => 'Notifications',
                'options' => ['email' => 'Email', 'sms' => 'SMS', 'push' => 'Push'],
                'item' => $this->item(),
            ],
            oldInput: ['prefs' => ['sms']],
        );

        $this->assertDoesNotMatchRegularExpression('/value="email"[^>]*checked/', $html);
        $this->assertMatchesRegularExpression('/value="sms"[^>]*checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/value="push"[^>]*checked/', $html);
    }

    public function test_multi_redisplay_all_unchecked_does_not_fall_back_to_model()
    {
        $html = $this->render(
            data: [
                'name' => 'prefs',
                'label' => 'Notifications',
                'options' => ['email' => 'Email', 'sms' => 'SMS', 'push' => 'Push'],
                'item' => $this->item(),
            ],
            oldInput: ['some_other_field' => 'x'],
        );

        $this->assertDoesNotMatchRegularExpression('/value="email"[^>]*checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/value="sms"[^>]*checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/value="push"[^>]*checked/', $html);
    }

    public function test_no_item_does_not_crash_when_deriving_required()
    {
        // Transient forms with no persistent model must not try to dereference
        // a null $item via checkIfRequired (which does $item::rules()).
        $html = $this->render([
            'name' => 'consent',
            'label' => 'I consent',
        ]);

        $this->assertDoesNotMatchRegularExpression('/name="consent"[^>]*required/', $html);
    }

    public function test_single_derives_required_from_model_rules()
    {
        $html = $this->render([
            'name' => 'enabled',
            'label' => 'Enabled',
            'item' => $this->item(),
        ]);

        $this->assertMatchesRegularExpression('/name="enabled"[^>]*required/', $html);
    }

    public function test_single_uses_explicit_required_prop_over_helper()
    {
        $html = $this->render([
            'name' => 'disabled_flag',
            'label' => 'Disabled',
            'item' => $this->item(),
            'required' => true,
        ]);

        $this->assertMatchesRegularExpression('/name="disabled_flag"[^>]*required/', $html);
    }

    public function test_single_section_label_renders_left_column_and_drops_offset()
    {
        $html = $this->render([
            'name' => 'saml_enabled',
            'label' => 'Enable SAML',
            'section_label' => 'SAML Integration',
        ]);

        // Section header renders as a non-<label> div so the checkbox's
        // accessible name comes only from the inner form-control label.
        $this->assertMatchesRegularExpression('/<div class="col-md-3 control-label">\s*<strong>SAML Integration<\/strong>/', $html);

        // With a left column present, the input div drops the offset so
        // the columns line up as col-md-3 + col-md-8.
        $this->assertStringNotContainsString('col-md-8 col-md-offset-3', $html);
        $this->assertStringContainsString('col-md-8', $html);
    }

    public function test_single_without_section_label_keeps_click_target_layout()
    {
        $html = $this->render([
            'name' => 'consent',
            'label' => 'I consent',
        ]);

        $this->assertStringNotContainsString('col-md-3 control-label', $html);
        $this->assertStringContainsString('col-md-8 col-md-offset-3', $html);
    }

    public function test_multi_ignores_section_label()
    {
        $html = $this->render([
            'name' => 'prefs',
            'label' => 'Notifications',
            'section_label' => 'Section Header',
            'options' => ['email' => 'Email', 'sms' => 'SMS'],
        ]);

        // In multi mode, $label already fills the left column; $section_label
        // must not render a second header alongside it.
        $this->assertStringNotContainsString('Section Header', $html);
    }
}
