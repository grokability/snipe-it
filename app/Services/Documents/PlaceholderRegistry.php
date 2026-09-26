<?php

namespace App\Services\Documents;

/**
 * The controlled placeholder whitelist (spec §5).
 *
 * Keys are the placeholder paths; values are human labels for the admin palette.
 * The renderer rejects anything not listed here.
 */
class PlaceholderRegistry
{
    public const PLACEHOLDERS = [
        'company.name' => 'Company name',
        'company.logo' => 'Company logo',

        'document.number' => 'Document number',
        'document.type' => 'Document type',
        'document.date_formatted' => 'Document date (selected calendar)',
        'checkout.date_formatted' => 'Checkout date (selected calendar)',
        'checkout.expected_return_formatted' => 'Expected return (selected calendar)',
        'document.date' => 'Document date (Gregorian)',
        'document.date_jalali' => 'Document date (Jalali)',

        'user.name' => 'Employee full name',
        'user.username' => 'Employee username',
        'user.employee_id' => 'Employee number',
        'user.email' => 'Employee email',
        'user.department' => 'Employee department',
        'user.job_title' => 'Employee job title',
        'user.manager' => 'Employee manager',

        'checkout.date' => 'Checkout date (Gregorian)',
        'checkout.date_jalali' => 'Checkout date (Jalali)',
        'checkout.expected_return' => 'Expected return (Gregorian)',
        'checkout.expected_return_jalali' => 'Expected return (Jalali)',
        'checkout.notes' => 'Checkout notes',
        'checkout.acceptance_id' => 'Checkout acceptance reference',

        'asset.asset_tag' => 'Asset tag (first item)',
        'asset.serial' => 'Serial (first item)',
        'asset.name' => 'Asset name (first item)',
        'asset.model' => 'Model (first item)',
        'asset.manufacturer' => 'Manufacturer (first item)',
        'asset.location' => 'Location (first item)',
        'asset.purchase_date' => 'Purchase date (first item)',
        'asset.warranty_expiration' => 'Warranty expiration (first item)',
        'asset.status' => 'Status (first item)',

        'current_user.name' => 'Generating user (IT) name',

        // Inside the {{#assets}} loop only:
        '@loop.asset_tag' => '— asset loop: tag',
        '@loop.name' => '— asset loop: name',
        '@loop.model' => '— asset loop: model',
        '@loop.manufacturer' => '— asset loop: manufacturer',
        '@loop.serial' => '— asset loop: serial',
        '@loop.status' => '— asset loop: status',
        '@loop.condition' => '— asset loop: condition',
    ];

    public static function sampleContext(array $config = []): array
    {
        $config = array_replace(DocumentLayout::HEADER_DEFAULTS, $config);

        return array_merge(array_fill_keys(array_keys(self::PLACEHOLDERS), '…'), [
            'user.name' => trans('documents.general.sample_employee'), 'user.employee_id' => 'EMP-001',
            'user.email' => 'employee@example.com', 'user.department' => 'IT',
            'company.name' => 'Example Company', 'document.number' => 'PREVIEW',
            'checkout.date' => now()->format('Y-m-d'), 'checkout.expected_return' => now()->addMonth()->format('Y-m-d'),
            'document.date' => now()->format($config['date_format']), 'document.date_formatted' => app(DocumentLayout::class)->date(now(), $config),
            'document.date_jalali' => \App\Support\Jalali\Jalali::format(now(), $config['date_format']),
            'asset.name' => trans('documents.general.sample_asset'), 'asset.asset_tag' => 'ASSET-001',
        ]);
    }

    public static function sampleAssets(): array
    {
        return [['asset_tag' => 'ASSET-001', 'name' => trans('documents.general.sample_asset'), 'model' => 'Model 1', 'serial' => 'SN-001']];
    }

    public static function available(): array
    {
        return self::PLACEHOLDERS;
    }

    public static function isKnown(string $key): bool
    {
        return array_key_exists($key, self::PLACEHOLDERS);
    }

    /**
     * Loop placeholders valid inside {{#assets}} ... {{/assets}}.
     *
     * @return string[]
     */
    public static function loopKeys(): array
    {
        return ['asset_tag', 'name', 'model', 'manufacturer', 'serial', 'status', 'condition'];
    }
}
