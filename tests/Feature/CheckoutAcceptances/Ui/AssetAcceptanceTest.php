<?php

use App\Events\CheckoutAccepted;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\CustomField;
use App\Models\User;
use App\Notifications\AcceptanceItemAcceptedNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake();
});

test('asset checkout accept page renders', function () {
    $checkoutAcceptance = CheckoutAcceptance::factory()->pending()->create();

    $this->actingAs($checkoutAcceptance->assignedTo)
        ->get(route('account.accept.item', $checkoutAcceptance))
        ->assertViewIs('account.accept.create');
});

test('cannot accept asset already accepted', function () {
    Event::fake([CheckoutAccepted::class]);

    $checkoutAcceptance = CheckoutAcceptance::factory()->accepted()->create();

    expect($checkoutAcceptance->isPending())->toBeFalse();

    $this->actingAs($checkoutAcceptance->assignedTo)
        ->post(route('account.store-acceptance', $checkoutAcceptance), [
            'asset_acceptance' => 'accepted',
            'note' => 'my note',
        ])
        ->assertRedirectToRoute('account.accept')
        ->assertSessionHas('error');

    Event::assertNotDispatched(CheckoutAccepted::class);
});

test('cannot accept asset for another user', function () {
    Event::fake([CheckoutAccepted::class]);

    $checkoutAcceptance = CheckoutAcceptance::factory()->pending()->create();

    expect($checkoutAcceptance->isPending())->toBeTrue();

    $anotherUser = User::factory()->create();

    $this->actingAs($anotherUser)
        ->post(route('account.store-acceptance', $checkoutAcceptance), [
            'asset_acceptance' => 'accepted',
            'note' => 'my note',
        ])
        ->assertRedirectToRoute('account.accept')
        ->assertSessionHas('error');

    expect($checkoutAcceptance->fresh()->isPending())->toBeTrue();

    Event::assertNotDispatched(CheckoutAccepted::class);
});

test('user can accept asset', function () {
    Event::fake([CheckoutAccepted::class]);

    $checkoutAcceptance = CheckoutAcceptance::factory()->pending()->create();

    expect($checkoutAcceptance->isPending())->toBeTrue();

    $this->actingAs($checkoutAcceptance->assignedTo)
        ->post(route('account.store-acceptance', $checkoutAcceptance), [
            'asset_acceptance' => 'accepted',
            'note' => 'my note',
        ])
        ->assertRedirectToRoute('account.accept')
        ->assertSessionHas('success');

    $checkoutAcceptance->refresh();

    expect($checkoutAcceptance->isPending())->toBeFalse();
    expect($checkoutAcceptance->accepted_at)->not->toBeNull();
    expect($checkoutAcceptance->declined_at)->toBeNull();

    Event::assertDispatched(CheckoutAccepted::class);
});

test('user can accept asset with required signature', function () {
    if (! function_exists('imagecreatetruecolor')) {
        $this->markTestSkipped('GD extension is required for signature image generation.');
    }

    $this->settings->enableRequiredSignatures();

    $checkoutAcceptance = CheckoutAcceptance::factory()->pending()->create();

    $canvas = imagecreatetruecolor(24, 12);
    $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
    imagefill($canvas, 0, 0, $transparent);
    imagesavealpha($canvas, true);
    $ink = imagecolorallocate($canvas, 25, 25, 25);
    imageline($canvas, 2, 10, 21, 2, $ink);

    ob_start();
    imagepng($canvas);
    $signaturePng = (string) ob_get_clean();
    $signatureOutput = 'data:image/png;base64,'.base64_encode($signaturePng);

    $this->actingAs($checkoutAcceptance->assignedTo)
        ->post(route('account.store-acceptance', $checkoutAcceptance), [
            'asset_acceptance' => 'accepted',
            'note' => 'signed in test',
            'signature_output' => $signatureOutput,
        ])
        ->assertRedirectToRoute('account.accept')
        ->assertSessionHas('success');

    $checkoutAcceptance->refresh();

    expect($checkoutAcceptance->signature_filename)->not->toBeNull();
    expect($checkoutAcceptance->stored_eula_file)->not->toBeNull();
});

test('user can decline asset', function () {
    Event::fake([CheckoutAccepted::class]);
    $this->settings->disableAlertEmail();

    // otherwise it tries to send an email without having enough information
    $checkoutAcceptance = CheckoutAcceptance::factory()->pending()->create();

    expect($checkoutAcceptance->isPending())->toBeTrue();

    $this->actingAs($checkoutAcceptance->assignedTo)
        ->post(route('account.store-acceptance', $checkoutAcceptance), [
            'asset_acceptance' => 'declined',
            'note' => 'my note',
        ])
        ->assertRedirectToRoute('account.accept')
        ->assertSessionHas('success');

    $checkoutAcceptance->refresh();

    expect($checkoutAcceptance->isPending())->toBeFalse();
    expect($checkoutAcceptance->accepted_at)->toBeNull();
    expect($checkoutAcceptance->declined_at)->not->toBeNull();

    Event::assertNotDispatched(CheckoutAccepted::class);
});

test('action logged when accepting asset', function () {
    $checkoutAcceptance = CheckoutAcceptance::factory()->pending()->create();

    $this->actingAs($checkoutAcceptance->assignedTo)
        ->post(route('account.store-acceptance', $checkoutAcceptance), [
            'asset_acceptance' => 'accepted',
            'note' => 'my note',
        ]);

    expect(Actionlog::query()
        ->where([
            'action_type' => 'accepted',
            'target_id' => $checkoutAcceptance->assignedTo->id,
            'target_type' => User::class,
            'note' => 'my note',
            'item_type' => Asset::class,
            'item_id' => $checkoutAcceptance->checkoutable->id,
        ])
        ->whereNotNull('action_date')
        ->exists())->toBeTrue();
});

test('action logged when declining asset', function () {
    $checkoutAcceptance = CheckoutAcceptance::factory()->pending()->create();

    $this->actingAs($checkoutAcceptance->assignedTo)
        ->post(route('account.store-acceptance', $checkoutAcceptance), [
            'asset_acceptance' => 'declined',
            'note' => 'my note',
        ]);

    expect(Actionlog::query()
        ->where([
            'action_type' => 'declined',
            'target_id' => $checkoutAcceptance->assignedTo->id,
            'target_type' => User::class,
            'note' => 'my note',
            'item_type' => Asset::class,
            'item_id' => $checkoutAcceptance->checkoutable->id,
        ])
        ->whereNotNull('action_date')
        ->exists())->toBeTrue();
});

test('acceptance email includes custom fields marked show in email and not encrypted', function () {
    Event::fake([CheckoutAccepted::class]);
    Notification::fake();
    $this->settings->enableAdminCC();

    $customField = CustomField::factory()->create([
        'name' => 'Cost Center',
        'show_in_email' => '1',
        'field_encrypted' => '0',
    ])->fresh();

    $asset = Asset::factory()->hasMultipleCustomFields([$customField])->create();
    $asset->{$customField->db_column} = 'ENG-42';
    $asset->save();

    $checkoutAcceptance = CheckoutAcceptance::factory()
        ->pending()
        ->for($asset, 'checkoutable')
        ->create();

    $this->actingAs($checkoutAcceptance->assignedTo)
        ->post(route('account.store-acceptance', $checkoutAcceptance), [
            'asset_acceptance' => 'accepted',
        ])
        ->assertRedirectToRoute('account.accept')
        ->assertSessionHas('success');

    Notification::assertSentTo(
        $checkoutAcceptance,
        function (AcceptanceItemAcceptedNotification $notification) {
            $rendered = $notification->toMail()->render();

            return str_contains($rendered, 'Cost Center')
                && str_contains($rendered, 'ENG-42');
        }
    );
});

test('acceptance note cannot inject markdown image tag', function () {
    // Regression for GHSA (F41 / Adam Nurudini). A low-privilege user
    // could submit an acceptance note like `![x](/var/www/html/.env)`,
    // which Blade's HTML escape leaves intact (the chars are not HTML
    // metacharacters). CommonMark then expanded it into a real <img>
    // tag inside the outbound notification, and laravel-mail-auto-embed
    // resolved the src via file_get_contents() or curl, exfiltrating
    // arbitrary local files or issuing SSRF requests. The fix registers
    // a CommonMark extension that overrides the Image renderer so no
    // <img> tag survives markdown parsing.
    Event::fake([CheckoutAccepted::class]);
    Notification::fake();
    $this->settings->enableAdminCC();
    $checkoutAcceptance = CheckoutAcceptance::factory()->pending()->create();

    $lfrPayload = '![x](/etc/hostname)';
    $ssrfPayload = '![y](http://169.254.169.254/latest/meta-data/)';
    $filePayload = '![z](file:///var/www/html/.env)';

    $this->actingAs($checkoutAcceptance->assignedTo)
        ->post(route('account.store-acceptance', $checkoutAcceptance), [
            'asset_acceptance' => 'accepted',
            'note' => "{$lfrPayload} {$ssrfPayload} {$filePayload}",
        ])
        ->assertRedirectToRoute('account.accept')
        ->assertSessionHas('success');

    Notification::assertSentTo(
        $checkoutAcceptance,
        function (AcceptanceItemAcceptedNotification $notification) {
            $rendered = $notification->toMail()->render();

            // None of the payload targets should survive as an <img src>
            // in the rendered email. laravel-mail-auto-embed resolves any
            // surviving img src via file_get_contents() (LFR) or curl (SSRF).
            // Legitimate header/logo <img> tags with app-controlled src
            // paths are unaffected. This test targets only user-controlled
            // src values that Adam's F41 report demonstrated.
            foreach (['/etc/hostname', '169.254.169.254', '/var/www/html/.env', 'file://'] as $payload) {
                $this->assertStringNotContainsString(
                    $payload,
                    $rendered,
                    "Rendered mail contains attacker-controlled path \"{$payload}\" - CommonMark image markdown was not blocked."
                );
            }

            return true;
        }
    );
});

test('admin can complete sign in place acceptance and is redirected to selected destination', function () {
    Event::fake([CheckoutAccepted::class]);

    $assignee = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $asset = Asset::factory()->create();

    $checkoutAcceptance = CheckoutAcceptance::factory()
        ->pending()
        ->for($assignee, 'assignedTo')
        ->for($asset, 'checkoutable')
        ->create();

    $this->actingAs($admin)
        ->withSession([
            'sign_in_place_acceptance_id' => $checkoutAcceptance->id,
            'sign_in_place_item_id' => $asset->id,
            'sign_in_place_resource_type' => 'Assets',
            'redirect_option' => 'target',
            'checkout_to_type' => 'user',
        ])
        ->post(route('account.store-acceptance', $checkoutAcceptance), [
            'asset_acceptance' => 'accepted',
            'note' => 'signed in person',
        ])
        ->assertRedirect(route('users.show', $assignee));

    expect($checkoutAcceptance->refresh()->accepted_at)->not->toBeNull();
    Event::assertDispatched(CheckoutAccepted::class);
});

test('stale sign in place post on already accepted item redirects to intended destination', function () {
    $assignee = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $asset = Asset::factory()->create();

    $checkoutAcceptance = CheckoutAcceptance::factory()
        ->accepted()
        ->for($assignee, 'assignedTo')
        ->for($asset, 'checkoutable')
        ->create();

    $this->actingAs($admin)
        ->withSession([
            'sign_in_place' => true,
            'redirect_option' => 'target',
            'checkout_to_type' => 'user',
        ])
        ->post(route('account.store-acceptance', $checkoutAcceptance), [
            'asset_acceptance' => 'accepted',
        ])
        ->assertRedirect(route('users.show', $assignee));
});

test('stale sign in place post with missing assignee does not throw route error', function () {
    $admin = User::factory()->admin()->create();
    $asset = Asset::factory()->create();

    $assignee = User::factory()->create();

    $checkoutAcceptance = CheckoutAcceptance::factory()
        ->accepted()
        ->for($assignee, 'assignedTo')
        ->for($asset, 'checkoutable')
        ->create();

    CheckoutAcceptance::whereKey($checkoutAcceptance->id)->update(['assigned_to_id' => null]);

    $this->actingAs($admin)
        ->withSession([
            'sign_in_place' => true,
            'redirect_option' => 'target',
            'checkout_to_type' => 'user',
        ])
        ->post(route('account.store-acceptance', $checkoutAcceptance), [
            'asset_acceptance' => 'accepted',
        ])
        ->assertRedirectToRoute('account.accept')
        ->assertSessionHas('error');
});

test('sign in place acceptance page uses checkout flow breadcrumbs', function () {
    $assignee = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $asset = Asset::factory()->create();

    $checkoutAcceptance = CheckoutAcceptance::factory()
        ->pending()
        ->for($assignee, 'assignedTo')
        ->for($asset, 'checkoutable')
        ->create();

    $response = $this->actingAs($admin)
        ->withSession([
            'sign_in_place_acceptance_id' => $checkoutAcceptance->id,
            'sign_in_place_item_id' => $asset->id,
            'sign_in_place_resource_type' => 'Assets',
        ])
        ->get(route('account.accept.item', $checkoutAcceptance));

    $response->assertOk()
        ->assertSeeInOrder([
            trans('general.assets'),
            $asset->display_name,
            trans('general.checkout'),
            sprintf('%s for %s', trans('general.sign_in_place'), $assignee->display_name),
        ], false)
        ->assertSee(route('hardware.index'), false)
        ->assertSee(route('hardware.show', $asset), false)
        ->assertDontSee(route('users.show', $assignee), false)
        ->assertDontSee(route('hardware.checkout.create', $asset), false);
});

test('oversized note is rejected before persistence or notification', function () {
    // Regression for the DoS reported by PizzaStev3 (Ahmed Mohammed).
    // An unbounded `note` on the acceptance store endpoint was reaching
    // synchronous CommonMark rendering in the decline notification and
    // burning per-request PHP-worker CPU (40k bytes ~= 847ms, 80k ~= 2.6s).
    // Server-side `max:1000` on the note field closes this at the input
    // boundary regardless of parser version. Primary fix is the
    // league/commonmark 2.9.0 bump; this test locks in the input-side guard.
    Event::fake([CheckoutAccepted::class]);
    Notification::fake();

    $checkoutAcceptance = CheckoutAcceptance::factory()->pending()->create();
    $oversizedNote = 'F9'.str_repeat(' ', 40_000).'éZ';

    $this->actingAs($checkoutAcceptance->assignedTo)
        ->from(route('account.accept.item', $checkoutAcceptance))
        ->post(route('account.store-acceptance', $checkoutAcceptance), [
            'asset_acceptance' => 'declined',
            'note' => $oversizedNote,
        ])
        ->assertRedirect(route('account.accept.item', $checkoutAcceptance))
        ->assertSessionHasErrors('note');

    expect($checkoutAcceptance->fresh()->isPending())->toBeTrue('Acceptance state should not advance when note validation fails.');
    expect($checkoutAcceptance->fresh()->declined_at)->toBeNull();
    expect($checkoutAcceptance->fresh()->accepted_at)->toBeNull();

    Event::assertNotDispatched(CheckoutAccepted::class);
    Notification::assertNothingSent();
});

test('normal length note still works', function () {
    // Negative side of the oversized-note regression: a normal short note
    // must still round-trip through the acceptance flow. Guards against
    // over-tightening the max: rule below reasonable UX.
    Event::fake([CheckoutAccepted::class]);

    $checkoutAcceptance = CheckoutAcceptance::factory()->pending()->create();

    $this->actingAs($checkoutAcceptance->assignedTo)
        ->post(route('account.store-acceptance', $checkoutAcceptance), [
            'asset_acceptance' => 'accepted',
            'note' => str_repeat('a', 500),
        ])
        ->assertRedirectToRoute('account.accept')
        ->assertSessionHas('success');

    expect($checkoutAcceptance->fresh()->isPending())->toBeFalse();

    Event::assertDispatched(CheckoutAccepted::class);
});
