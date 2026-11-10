<?php

namespace App\Mcp\Tools;

use App\Models\Asset;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('audit_asset')]
#[Title('Audit Asset')]
#[Description('Record an audit for a Snipe-IT asset, updating the last audit date and optionally the location')]
class AuditAssetTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'asset_tag' => 'nullable|max:100',
            'serial' => 'nullable|string|max:255',
            'id' => 'nullable|integer',
            'note' => 'nullable|string|max:1000',
            'location_id' => 'nullable|integer|exists:locations,id',
            'next_audit_date' => 'nullable|date',
        ]);

        $asset = $this->resolveAsset($request);

        if (! $asset) {
            return Response::make(Response::error(trans('mcp.asset_not_found')));
        }

        if (! Gate::allows('audit', $asset)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $originalValues = $asset->getRawOriginal();
        $settings = Setting::getSettings();

        $asset->last_audit_date = date('Y-m-d H:i:s');

        if ($request->filled('next_audit_date')) {
            $asset->next_audit_date = $request->get('next_audit_date');
        } elseif (! is_null($settings->audit_interval)) {
            $asset->next_audit_date = Carbon::now()->addMonths($settings->audit_interval)->toDateString();
        }

        if ($request->filled('location_id')) {
            $asset->location_id = $request->get('location_id');
        }

        // Bypass the observer to avoid logging a spurious asset-update entry
        // alongside the audit log entry created by logAudit() below
        $asset->unsetEventDispatcher();

        if ($asset->isValid() && $asset->save()) {
            $asset->logAudit($request->get('note'), $request->get('location_id'), null, $originalValues);

            return Response::make(
                Response::text(trans('mcp.asset_audited', ['asset_tag' => $asset->asset_tag]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.asset_audited', ['asset_tag' => $asset->asset_tag]),
                'asset_tag' => $asset->asset_tag,
                'last_audit_date' => $asset->last_audit_date,
                'next_audit_date' => $asset->next_audit_date,
                'location' => $asset->location?->name,
            ]);
        }

        return Response::make(Response::error(trans('mcp.audit_failed', ['error' => $asset->getErrors()->first()])));
    }

    private function resolveAsset(Request $request): ?Asset
    {
        if ($request->filled('asset_tag')) {
            return Asset::where('asset_tag', $request->get('asset_tag'))->with('location')->first();
        }
        if ($request->filled('serial')) {
            return Asset::where('serial', $request->get('serial'))->with('location')->first();
        }
        if ($request->filled('id')) {
            return Asset::with('location')->find($request->get('id'));
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'asset_tag' => $schema->string()->description('Asset tag of the asset to audit'),
            'serial' => $schema->string()->description('Serial number of the asset to audit'),
            'id' => $schema->number()->description('Numeric ID of the asset to audit'),
            'note' => $schema->string()->description('Optional audit note'),
            'location_id' => $schema->number()->description('Location ID where the asset was found (also updates the asset location)'),
            'next_audit_date' => $schema->string()->description('Override the next audit date (YYYY-MM-DD); defaults to now plus the audit_interval from settings'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the audit succeeded'),
            'error' => $schema->boolean()->description('True if the audit failed'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'asset_tag' => $schema->string()->description('Asset tag of the audited asset'),
            'last_audit_date' => $schema->string()->description('Timestamp of the audit just recorded'),
            'next_audit_date' => $schema->string()->description('Date of the next scheduled audit'),
            'location' => $schema->string()->description('Location name where the asset was found'),
        ];
    }
}
