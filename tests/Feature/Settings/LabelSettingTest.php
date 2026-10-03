<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Tests\TestCase;

class LabelSettingTest extends TestCase
{
    public function test_permission_required_to_view_label_settings()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('settings.labels.index'))
            ->assertForbidden();
    }

    private function previewUrl(array $query = []): string
    {
        return route('labels.customizer-preview', ['labelName' => 'DefaultLabel'] + $query);
    }

    public function test_requires_superuser(): void
    {
        $this->actingAs(User::factory()->create())
            ->get($this->previewUrl())
            ->assertForbidden();
    }

    public function test_rejects_remote_font_name(): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get($this->previewUrl(['content' => ['title_font' => 'http://127.0.0.1:1/x']]))
            ->assertSessionHasErrors('content.title_font');
    }

    public function test_rejects_filesystem_path_font_name(): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get($this->previewUrl(['content' => ['tag_font' => '/tmp/pwn']]))
            ->assertSessionHasErrors('content.tag_font');
    }

    public function test_rejects_out_of_allowlist_font_on_every_font_field(): void
    {
        $user = User::factory()->superuser()->create();

        foreach (['tag_font', 'title_font', 'field_label_font', 'field_value_font'] as $field) {
            $this->actingAs($user)
                ->get($this->previewUrl(['content' => [$field => 'notarealfont']]))
                ->assertSessionHasErrors("content.$field");
        }
    }

    public function test_allows_a_font_the_customizer_exposes(): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get($this->previewUrl(['content' => ['title_font' => 'freesans']]))
            ->assertSessionDoesntHaveErrors('content.title_font');
    }
}
