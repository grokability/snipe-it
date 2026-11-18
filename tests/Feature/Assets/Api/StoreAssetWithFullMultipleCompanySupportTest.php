<?php

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Statuslabel;

uses(\Tests\Support\ProvidesDataForFullMultipleCompanySupportTesting::class);

test('adheres to full multiple companies support scoping', function ($data) {
    ['actor' => $actor, 'company_attempting_to_associate' => $company, 'assertions' => $assertions] = $data();

    $this->settings->enableMultipleFullCompanySupport();

    $response = $this->actingAsForApi($actor)
        ->postJson(route('api.assets.store'), [
            'asset_tag' => 'random_string',
            'company_id' => $company->id,
            'model_id' => AssetModel::factory()->create()->id,
            'status_id' => Statuslabel::factory()->readyToDeploy()->create()->id,
        ]);

    $asset = Asset::withoutGlobalScopes()->findOrFail($response['payload']['id']);

    $assertions($asset);
})->with('dataForFullMultipleCompanySupportTesting');

test('handles company id being string', function ($data) {
    ['actor' => $actor, 'company_attempting_to_associate' => $company, 'assertions' => $assertions] = $data();

    $this->settings->enableMultipleFullCompanySupport();

    $response = $this->actingAsForApi($actor)
        ->postJson(route('api.assets.store'), [
            'asset_tag' => 'random_string',
            'company_id' => (string) $company->id,
            'model_id' => AssetModel::factory()->create()->id,
            'status_id' => Statuslabel::factory()->readyToDeploy()->create()->id,
        ]);

    $asset = Asset::withoutGlobalScopes()->findOrFail($response['payload']['id']);

    $assertions($asset);
})->with('dataForFullMultipleCompanySupportTesting');
