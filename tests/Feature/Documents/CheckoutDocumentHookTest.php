<?php

namespace Tests\Feature\Documents;

use App\Events\CheckoutableCheckedOut;
use App\Models\Asset;
use App\Models\Document;
use App\Models\Setting;
use App\Models\User;
use App\Services\Documents\TemplateService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutDocumentHookTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function publishActiveCheckoutTemplate(): void
    {
        $templates = app(TemplateService::class);
        $admin = User::factory()->superuser()->create();
        $template = $templates->create([
            'name' => 'Checkout '.Str::random(6),
            'type' => 'checkout',
            'language' => 'en',
            'body' => 'Issued to {{user.name}}.',
        ], $admin);
        $templates->publishVersion($template, [], $admin);
    }

    public function test_checkout_to_user_generates_a_pending_document()
    {
        $this->publishActiveCheckoutTemplate();

        // No Event::fake here: the checkout action log is written by a
        // CheckoutableCheckedOut listener, and the document must link to it.
        $asset = Asset::factory()->create();
        $employee = User::factory()->create();

        $response = $this->actingAs(User::factory()->checkoutAssets()->create())
            ->post(route('hardware.checkout.store', $asset), [
                'checkout_to_type' => 'user',
                'assigned_user' => $employee->id,
            ]);

        $response->assertSessionHasNoErrors();

        $document = Document::where('assigned_to_id', $employee->id)->first();
        $this->assertNotNull($document, 'Checkout should have generated a document.');
        $this->assertSame('checkout', $document->type);
        $this->assertSame(Document::STATUS_PENDING_SIGNATURE, $document->status);
        $this->assertSame(1, $document->items()->count());
        $this->assertNotNull($document->checkout_log_id, 'Document should link to the checkout action log.');
        $this->assertNotNull($document->pdf_path);
    }

    public function test_checkout_without_any_template_still_succeeds()
    {
        Event::fake([CheckoutableCheckedOut::class]);

        $asset = Asset::factory()->create();
        $employee = User::factory()->create();

        $this->actingAs(User::factory()->checkoutAssets()->create())
            ->post(route('hardware.checkout.store', $asset), [
                'checkout_to_type' => 'user',
                'assigned_user' => $employee->id,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('documents', ['assigned_to_id' => $employee->id]);
        $this->assertNotNull($asset->fresh()->assigned_to, 'Checkout itself must succeed without a template.');
    }

    public function test_required_document_with_missing_template_warns_but_checks_out()
    {
        $settings = Setting::getSettings();
        $settings->require_checkout_document = 1;
        $settings->save();
        Setting::$_cache = null;

        Event::fake([CheckoutableCheckedOut::class]);

        $asset = Asset::factory()->create();
        $employee = User::factory()->create();

        $this->actingAs(User::factory()->checkoutAssets()->create())
            ->post(route('hardware.checkout.store', $asset), [
                'checkout_to_type' => 'user',
                'assigned_user' => $employee->id,
            ])
            ->assertSessionHas('warning');

        // The checkout stands — document problems never block it (spec §4)
        $this->assertNotNull($asset->fresh()->assigned_to);
    }
}
