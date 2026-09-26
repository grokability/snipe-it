<?php

namespace Tests\Unit\Documents;

use App\Models\Asset;
use App\Models\User;
use App\Services\Documents\PlaceholderRenderer;
use Tests\TestCase;

class PlaceholderRendererTest extends TestCase
{
    public function test_replaces_registered_placeholders(): void
    {
        $renderer = new PlaceholderRenderer;

        $out = $renderer->render(
            'Hello {{user.name}}, {{company.name}} asset {{document.number}}.',
            ['user.name' => 'Hesam', 'company.name' => 'Acme', 'document.number' => 'CO-2026-000001'],
        );

        $this->assertSame('Hello Hesam, Acme asset CO-2026-000001.', $out);
    }

    public function test_unknown_placeholder_is_marked_not_silently_dropped(): void
    {
        $out = (new PlaceholderRenderer)->render('{{user.name}} {{totally.made_up}}', ['user.name' => 'A']);

        $this->assertSame('A [unknown: totally.made_up]', $out);
    }

    public function test_function_like_tokens_are_never_evaluated(): void
    {
        $out = (new PlaceholderRenderer)->render('{{phpinfo()}} {{asset.tag}} {{user.name}}', ['user.name' => 'A']);

        // Only [a-zA-Z0-9_.] keys match the pattern: phpinfo() stays literal,
        // and asset.tag (not in the registry) degrades to the unknown marker.
        $this->assertStringContainsString('{{phpinfo()}}', $out);
        $this->assertStringContainsString('[unknown: asset.tag]', $out);
        $this->assertStringContainsString('A', $out);
    }

    public function test_asset_loop_expands_per_asset(): void
    {
        // Rows are joined with a newline by the renderer itself
        $out = (new PlaceholderRenderer)->render(
            '{{#assets}}{{asset.asset_tag}}: {{asset.name}}{{/assets}}',
            [],
            [
                ['asset_tag' => 'NB-1', 'name' => 'Laptop', 'serial' => 'S1'],
                ['asset_tag' => 'NB-2', 'name' => 'Dock', 'serial' => 'S2'],
            ],
        );

        $this->assertSame("NB-1: Laptop\nNB-2: Dock", $out);
    }

    public function test_asset_loop_accepts_bare_keys_too(): void
    {
        $out = (new PlaceholderRenderer)->render(
            '{{#assets}}{{asset_tag}} ({{serial}}){{/assets}}',
            [],
            [
                ['asset_tag' => 'NB-1', 'name' => 'Laptop', 'serial' => 'S1'],
            ],
        );

        $this->assertSame('NB-1 (S1)', $out);
    }

    public function test_values_are_substituted_verbatim_escaping_is_callers_job(): void
    {
        // The renderer is transport-agnostic; PdfService escapes every value
        // before writeHTML (spec §21). Raw here means raw.
        $out = (new PlaceholderRenderer)->render('{{user.name}}', ['user.name' => '<b>x</b>']);

        $this->assertSame('<b>x</b>', $out);
    }

    public function test_build_context_flattens_payload(): void
    {
        $user = new User;
        $user->first_name = 'Sara';
        $user->last_name = 'Ahmadi';
        $user->username = 'sahmadi';
        $user->employee_num = '42';
        $user->email = 'sara@example.com';

        $asset = new Asset;
        $asset->asset_tag = 'LT-100';

        $context = PlaceholderRenderer::buildContext([
            'company' => (object) ['name' => 'Acme'],
            'user' => $user,
            'checkout' => ['date' => '2026-09-23', 'notes' => 'onboarding'],
            'assets' => [['asset_tag' => 'LT-100', 'serial' => 'SN-9', 'name' => 'ThinkPad', 'model' => 'T14', 'manufacturer' => 'Lenovo', 'location' => 'Tehran', 'purchase_date' => '2025-01-01', 'warranty_expiration' => '2028-01-01', 'status' => 'Deployed']],
            'current_user' => $user,
            'document' => ['number' => 'CO-2026-000001', 'type' => 'checkout', 'date' => '2026-09-23'],
        ]);

        $this->assertSame('Acme', $context['company.name']);
        $this->assertSame('Sara Ahmadi', $context['user.name']);
        $this->assertSame('42', $context['user.employee_id']);
        $this->assertSame('CO-2026-000001', $context['document.number']);
        $this->assertSame('1405/07/01', $context['document.date_jalali']);
        $this->assertSame('1405/07/01', $context['checkout.date_jalali']);
        $this->assertSame('LT-100', $context['asset.asset_tag']);
        $this->assertSame('onboarding', $context['checkout.notes']);
    }
}
