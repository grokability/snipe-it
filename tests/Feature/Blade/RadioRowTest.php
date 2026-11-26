<?php

namespace Tests\Feature\Blade;

use App\Http\Middleware\CheckForDebug;
use App\Http\Middleware\CheckForSetup;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RadioRowTest extends TestCase
{
    private function render(array $data, ?array $oldInput = null): string
    {
        Route::get('/__test/radio-row', function () use ($data) {
            return view('blade.form.radio-row', $data);
        })->middleware('web');

        $call = $this->withoutMiddleware([
            CheckForSetup::class,
            CheckForDebug::class,
        ]);
        if ($oldInput !== null) {
            $call = $call->withSession(['_old_input' => $oldInput]);
        }

        return $call->get('/__test/radio-row')->assertOk()->getContent();
    }

    private function item(): object
    {
        return new class
        {
            public string $admin_cc_always = '1';

            public ?string $preference = null;

            public static function rules()
            {
                return ['admin_cc_always' => 'required'];
            }
        };
    }

    public function test_fresh_render_checks_the_matching_model_value()
    {
        $html = $this->render([
            'name' => 'admin_cc_always',
            'label' => 'CC Admin',
            'options' => ['1' => 'Always', '0' => 'Never'],
            'item' => $this->item(),
        ]);

        $this->assertMatchesRegularExpression('/value="1"[^>]*checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/value="0"[^>]*checked/', $html);
    }

    public function test_fresh_render_with_null_model_value_checks_nothing()
    {
        $html = $this->render([
            'name' => 'preference',
            'label' => 'Preference',
            'options' => ['a' => 'A', 'b' => 'B', 'c' => 'C'],
            'item' => $this->item(),
        ]);

        $this->assertDoesNotMatchRegularExpression('/value="a"[^>]*checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/value="b"[^>]*checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/value="c"[^>]*checked/', $html);
    }

    public function test_explicit_selected_overrides_model_value()
    {
        $html = $this->render([
            'name' => 'admin_cc_always',
            'label' => 'CC Admin',
            'options' => ['1' => 'Always', '0' => 'Never'],
            'item' => $this->item(),
            'selected' => '0',
        ]);

        $this->assertMatchesRegularExpression('/value="0"[^>]*checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/value="1"[^>]*checked/', $html);
    }

    public function test_redisplay_reflects_old_value_not_model()
    {
        // Model says 1; user submitted 0 and validation failed elsewhere.
        // Old input has admin_cc_always=0. Redisplay must reflect that.
        $html = $this->render(
            data: [
                'name' => 'admin_cc_always',
                'label' => 'CC Admin',
                'options' => ['1' => 'Always', '0' => 'Never'],
                'item' => $this->item(),
            ],
            oldInput: ['admin_cc_always' => '0'],
        );

        $this->assertMatchesRegularExpression('/value="0"[^>]*checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/value="1"[^>]*checked/', $html);
    }

    public function test_redisplay_with_no_old_value_does_not_fall_back_to_model()
    {
        // Session has old input but this specific field is absent. Radios
        // should render nothing checked; they must NOT fall through to the
        // stale $item->admin_cc_always default (which would mislead the
        // user about what they submitted).
        $html = $this->render(
            data: [
                'name' => 'admin_cc_always',
                'label' => 'CC Admin',
                'options' => ['1' => 'Always', '0' => 'Never'],
                'item' => $this->item(),
            ],
            oldInput: ['some_other_field' => 'x'],
        );

        $this->assertDoesNotMatchRegularExpression('/value="1"[^>]*checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/value="0"[^>]*checked/', $html);
    }

    public function test_no_label_uses_offset_column_default()
    {
        $html = $this->render([
            'name' => 'admin_cc_always',
            'options' => ['1' => 'Always', '0' => 'Never'],
            'item' => $this->item(),
        ]);

        $this->assertStringContainsString('col-md-8 col-md-offset-3', $html);
    }

    public function test_with_label_uses_non_offset_column_default()
    {
        $html = $this->render([
            'name' => 'admin_cc_always',
            'label' => 'CC Admin',
            'options' => ['1' => 'Always', '0' => 'Never'],
            'item' => $this->item(),
        ]);

        // With a left-hand label the option column shouldn't have the
        // col-md-offset-3 push (that offset conflicts with the col-md-3
        // label the row already emits).
        preg_match_all('/class="col-md-8[^"]*"/', $html, $matches);
        $classes = $matches[0] ?? [];
        $this->assertNotEmpty($classes);
        foreach ($classes as $class) {
            // The @error and help_text blocks legitimately use offset even
            // when there's a label, so we only care about the primary
            // options container: at least one col-md-8 that isn't offset.
            if (! str_contains($class, 'col-md-offset-3')) {
                return;
            }
        }
        $this->fail('Expected at least one non-offset col-md-8 option container.');
    }

    public function test_required_is_applied_to_first_radio_only()
    {
        $html = $this->render([
            'name' => 'admin_cc_always',
            'label' => 'CC Admin',
            'options' => ['1' => 'Always', '0' => 'Never'],
            'item' => $this->item(),
        ]);

        // Browser treats any-required-in-a-radio-group as "the group is
        // required," so we emit required on the first option only.
        $this->assertMatchesRegularExpression('/value="1"[^>]*required/', $html);
        $this->assertDoesNotMatchRegularExpression('/value="0"[^>]*required/', $html);
    }

    public function test_no_item_does_not_crash_when_deriving_required()
    {
        // Transient forms like bulk-checkin have no persistent model backing.
        // The row must not try to dereference a null $item via checkIfRequired.
        $html = $this->render([
            'name' => 'update_default_location',
            'selected' => '1',
            'options' => ['1' => 'Update default', '0' => 'Leave alone'],
        ]);

        $this->assertMatchesRegularExpression('/value="1"[^>]*checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/value="1"[^>]*required/', $html);
    }

    public function test_disabled_is_applied_to_every_radio()
    {
        $html = $this->render([
            'name' => 'admin_cc_always',
            'label' => 'CC Admin',
            'options' => ['1' => 'Always', '0' => 'Never'],
            'item' => $this->item(),
            'disabled' => true,
        ]);

        $this->assertMatchesRegularExpression('/value="1"[^>]*disabled/', $html);
        $this->assertMatchesRegularExpression('/value="0"[^>]*disabled/', $html);
    }
}
