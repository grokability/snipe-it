<?php

use App\Models\Statuslabel;
use App\Models\User;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('statuslabels.edit', Statuslabel::factory()->create()->id))
        ->assertOk();
});

test('edit page ships cancel and submit controls', function () {
    // Regression guard: migrating from layouts/edit-form to
    // layouts/default + <x-form>/<x-box> flipped the bottom cancel +
    // save button from an explicit <x-redirect_submit_options> to the
    // implicit <x-box.footer /> that <x-box> renders when its parent
    // <x-form> exposes a route. Without those two controls the user
    // has nothing but the top save button, which is a UX regression.
    $response = $this->actingAs(User::factory()->superuser()->create())
        ->get(route('statuslabels.edit', Statuslabel::factory()->create()->id))
        ->assertOk();

    $response->assertSee('id="submit_button"', false);
    $response->assertSee(trans('general.cancel'), false);
});
