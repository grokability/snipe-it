<?php

namespace Tests\Feature\Blade;

use App\Http\Middleware\CheckForDebug;
use App\Http\Middleware\CheckForSetup;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CheckboxInlineTest extends TestCase
{
    private function render(array $data, ?array $oldInput = null): string
    {
        Route::get('/__test/checkbox-inline', function () use ($data) {
            return view('blade.form.checkbox-inline', $data);
        })->middleware('web');

        $call = $this->withoutMiddleware([
            CheckForSetup::class,
            CheckForDebug::class,
        ]);
        if ($oldInput !== null) {
            $call = $call->withSession(['_old_input' => $oldInput]);
        }

        return $call->get('/__test/checkbox-inline')->assertOk()->getContent();
    }

    private function item(): object
    {
        return new class
        {
            public bool $enabled = true;

            public bool $disabled_flag = false;

            public static function rules()
            {
                return ['enabled' => 'required|boolean'];
            }
        };
    }

    public function test_does_not_emit_form_group_wrapper()
    {
        // Inline variant must not carry a form-group class; the caller
        // controls its own row layout in bulk-edit views.
        $html = $this->render([
            'name' => 'null_name',
            'label' => 'Set to null',
        ]);

        $this->assertStringNotContainsString('form-group', $html);
        $this->assertStringContainsString('<label class="form-control">', $html);
    }

    public function test_no_item_does_not_crash_when_deriving_required()
    {
        // The classic bulk-edit case: transient sentinel with no model.
        $html = $this->render([
            'name' => 'null_name',
            'label' => 'Set to null',
        ]);

        $this->assertMatchesRegularExpression('/type="checkbox"[^>]*name="null_name"/', $html);
        $this->assertDoesNotMatchRegularExpression('/name="null_name"[^>]*required/', $html);
    }

    public function test_fresh_render_reads_model_value()
    {
        $html = $this->render([
            'name' => 'enabled',
            'label' => 'Enabled',
            'item' => $this->item(),
        ]);

        $this->assertMatchesRegularExpression('/name="enabled"[^>]*checked/', $html);
    }

    public function test_redisplay_unchecked_does_not_fall_back_to_model()
    {
        // Model says true; user unchecked before submit; validation failed
        // elsewhere. On redisplay the box must stay unchecked.
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

    public function test_redisplay_checked_shows_checked()
    {
        $html = $this->render(
            data: [
                'name' => 'null_name',
                'label' => 'Set to null',
            ],
            oldInput: ['null_name' => '1'],
        );

        $this->assertMatchesRegularExpression('/name="null_name"[^>]*checked/', $html);
    }

    public function test_derives_required_from_model_rules()
    {
        $html = $this->render([
            'name' => 'enabled',
            'label' => 'Enabled',
            'item' => $this->item(),
        ]);

        $this->assertMatchesRegularExpression('/name="enabled"[^>]*required/', $html);
    }

    public function test_disabled_propagates_to_input()
    {
        $html = $this->render([
            'name' => 'null_name',
            'label' => 'Set to null',
            'disabled' => true,
        ]);

        $this->assertMatchesRegularExpression('/name="null_name"[^>]*disabled/', $html);
    }
}
