<?php

use App\Models\Statuslabel;
use App\Models\User;

/**
 * Regression coverage for GHSA-xwf8-rj39-5q7q. Status label color was
 * stored verbatim from request input and rendered into an inline
 * `style="color: ..."` attribute in the sidebar, so a low-privilege
 * user with statuslabels.create / statuslabels.edit could plant a CSS
 * payload (full-viewport overlay, forced url(...) request) seen by
 * every viewer holding assets.view, including superusers.
 *
 * The fix validates through App\Rules\CssColor on both the web and API
 * write paths, and adds an Attribute-cast on the model that runs
 * CssColor::sanitize() on read for defense-in-depth against pre-fix
 * stored values.
 */
test('web store rejects a css injection color', function () {
    $actor = User::factory()->createStatusLabels()->create();
    $payload = 'red;position:fixed;top:0;left:0;width:100vw;height:100vh;background:url(//attacker.example/p.png)';

    $this->actingAs($actor)
        ->post(route('statuslabels.store'), [
            'name' => 'poisoned-web-'.uniqid(),
            'statuslabel_types' => 'deployable',
            'color' => $payload,
        ])
        ->assertSessionHasErrors('color');

    $this->assertDatabaseMissing('status_labels', ['color' => $payload]);
});

test('web update rejects a css injection color', function () {
    $actor = User::factory()->editStatusLabels()->create();
    $existing = Statuslabel::factory()->create(['color' => '#00ff00']);
    $payload = 'red;background:url(//attacker.example/p.png)';

    $this->actingAs($actor)
        ->put(route('statuslabels.update', $existing->id), [
            'name' => $existing->name,
            'statuslabel_types' => 'deployable',
            'color' => $payload,
        ])
        ->assertSessionHasErrors('color');

    $this->assertNotSame($payload, $existing->fresh()->getRawOriginal('color'));
});

test('api store rejects a css injection color', function () {
    $actor = User::factory()->createStatusLabels()->create();
    $payload = 'red;position:fixed;background:url(//attacker.example/p.png)';

    // Snipe-IT's Api handler catches ValidationException and
    // returns HTTP 200 with an error-shaped body (see
    // app/Exceptions/Handler.php:174-177), so the "rejected"
    // signal here is `status: error` + a color error message,
    // not an HTTP 422.
    $this->actingAsForApi($actor)
        ->postJson(route('api.statuslabels.store'), [
            'name' => 'poisoned-api-'.uniqid(),
            'type' => 'deployable',
            'color' => $payload,
        ])
        ->assertOk()
        ->assertJson(['status' => 'error'])
        ->assertJsonPath('messages.color', fn ($messages) => is_array($messages) && count($messages) > 0);

    $this->assertDatabaseMissing('status_labels', ['color' => $payload]);
});

test('hex rgb and hsl colors pass validation', function () {
    $actor = User::factory()->createStatusLabels()->create();

    foreach (['#3c8dbc', '#fff', 'rgb(1,2,3)', 'rgba(1,2,3,0.5)', 'hsl(120,50%,50%)'] as $ok) {
        $this->actingAs($actor)
            ->post(route('statuslabels.store'), [
                'name' => 'ok-color-'.uniqid(),
                'statuslabel_types' => 'deployable',
                'color' => $ok,
            ])
            ->assertSessionHasNoErrors();
    }
});

test('pre fix stored payload is sanitized on read', function () {
    // Bypass ValidatingTrait by inserting directly, simulating a
    // row that predates the write-path guard.
    $payload = 'red;position:fixed;background:url(//attacker.example/p.png)';
    $label = Statuslabel::factory()->create();
    \Illuminate\Support\Facades\DB::table('status_labels')
        ->where('id', $label->id)
        ->update(['color' => $payload]);

    expect($label->fresh()->color)->toBe('', 'CssColor::sanitize must strip a poisoned pre-fix value on read.');
});
