<?php

namespace App\Services\Documents;

use App\Support\Jalali\Jalali;

/**
 * Deterministic placeholder renderer (spec §5, §21).
 *
 * - Only registered placeholders are replaced.
 * - Unknown placeholders become a visible [unknown: x] marker.
 * - No eval, no Blade, no user-supplied code ever executes.
 */
class PlaceholderRenderer
{
    /**
     * Render a template string against a value context.
     *
     * @param  string  $text  template body/header/footer
     * @param  array<string,mixed>  $context  flat map: ['company.name' => 'Acme', ...]
     * @param  array<int,array<string,mixed>>  $assets  one map per asset row (loop context)
     */
    public function render(string $text, array $context, array $assets = []): string
    {
        // 1. Expand the {{#assets}} loop first
        $text = $this->renderAssetLoop($text, $assets);

        // 2. Replace every remaining {{...}} against the registry
        return preg_replace_callback(
            '/\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/',
            function ($m) use ($context) {
                $key = $m[1];
                if (! PlaceholderRegistry::isKnown($key)) {
                    return '[unknown: '.$key.']';
                }

                return (string) ($context[$key] ?? '');
            },
            $text
        );
    }

    /**
     * Build the placeholder context for a document (spec §5 examples).
     *
     * @param  array  $payload  {company, settings, user, checkout, assets, current_user, document}
     * @return array<string,mixed>
     */
    public static function buildContext(array $payload): array
    {
        $company = $payload['company'] ?? null;
        $user = $payload['user'] ?? null;
        $checkout = $payload['checkout'] ?? [];
        $first = $payload['assets'][0] ?? [];
        $current = $payload['current_user'] ?? null;
        $document = $payload['document'] ?? [];

        $context = [
            'company.name' => $company->name ?? '',
            'company.logo' => '', // rendered by PdfService, not by text substitution

            'document.number' => $document['number'] ?? '',
            'document.type' => $document['type'] ?? '',
            'document.date' => $document['date'] ?? '',
            'document.date_jalali' => Jalali::format($document['date'] ?? now()),

            'user.name' => trim(($user->first_name ?? '').' '.($user->last_name ?? '')),
            'user.username' => $user->username ?? '',
            'user.employee_id' => $user->employee_num ?? '',
            'user.email' => $user->email ?? '',
            'user.department' => $user->department?->name ?? '',
            'user.job_title' => $user->jobtitle ?? '',
            'user.manager' => $user->manager?->name ?? trim(($user->manager?->first_name ?? '').' '.($user->manager?->last_name ?? '')),

            'checkout.date' => $checkout['date'] ?? '',
            'checkout.date_jalali' => ! empty($checkout['date']) ? Jalali::format($checkout['date']) : '',
            'checkout.expected_return' => $checkout['expected_return'] ?? '',
            'checkout.expected_return_jalali' => ! empty($checkout['expected_return']) ? Jalali::format($checkout['expected_return']) : '',
            'checkout.notes' => $checkout['notes'] ?? '',
            'checkout.acceptance_id' => $checkout['acceptance_id'] ?? '',

            'current_user.name' => trim(($current?->first_name ?? '').' '.($current?->last_name ?? '')),
        ];

        foreach (['asset_tag', 'serial', 'name', 'model', 'manufacturer', 'location', 'purchase_date', 'warranty_expiration', 'status'] as $field) {
            $context['asset.'.$field] = $first[$field] ?? '';
        }

        return $context;
    }

    private function renderAssetLoop(string $text, array $assets): string
    {
        return preg_replace_callback(
            '/\{\{#assets\}\}(.*?)\{\{\/assets\}\}/s',
            function ($m) use ($assets) {
                $rowTemplate = $m[1];
                $rows = [];

                foreach ($assets as $asset) {
                    $row = preg_replace_callback(
                        '/\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/',
                        function ($rm) use ($asset) {
                            $key = $rm[1];
                            // Accept both {{asset_tag}} and {{asset.asset_tag}} inside the loop
                            $field = str_starts_with($key, 'asset.') ? substr($key, 6) : $key;
                            if (in_array($field, PlaceholderRegistry::loopKeys())) {
                                return (string) ($asset[$field] ?? '');
                            }

                            return $rm[0]; // leave non-loop placeholders for the outer pass
                        },
                        $rowTemplate
                    );
                    $rows[] = $row;
                }

                return implode("\n", $rows);
            },
            $text
        );
    }
}
