<?php

namespace App\SyncAdapters\CustomHttp;

use App\Models\Asset;
use App\Models\SyncAdapterConfig;
use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\PushableSyncAdapter;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * User-defined pull-only HTTP adapter. The admin points it at any
 * JSON HTTP API, picks an auth shape (bearer / basic / api-key /
 * none), enters a records-array dot-path plus a per-Snipe-IT-field
 * dot-path, and optionally declares extra fields as a JSON blob. The
 * generic pull() walks the response, extracts values by dot-path,
 * and yields normalized records the framework can save.
 */
class CustomHttpAdapter extends PushableSyncAdapter
{
    /**
     * Cap on pagination loop iterations, to keep a misconfigured
     * next-url or an infinite server response from spinning forever.
     * At the default 500-per-page + 1000 iterations that is 500k
     * records, which comfortably fits any single-tenant fleet.
     */
    private const PAGINATION_MAX_PAGES = 1000;

    /**
     * Snipe-IT MappingTargets::FIELDS entries whose canonical source
     * is a real user-editable column on the Asset (and its Model).
     * These are the fields we can meaningfully write back to a
     * vendor. Network and telemetry fields (mac, ip, os, os_version,
     * last_seen) are vendor-authoritative in Snipe-IT, so
     * they aren't in the push set.
     */

    /**
     * Pickable HostInventoryRecord fields for the field-map repeater
     * dropdown, keyed by internal name mapped to the translated UI
     * label. Resolved through the sync_adapters lang file so
     * translators pick these up alongside the framework's other field
     * labels. Adding a new HostInventoryRecord field means adding one
     * row here and one assignment in normalize().
     *
     * source_id is deliberately NOT in this list. It's required for
     * every sync run (records without a stable vendor id can't be
     * matched on subsequent pulls). It gets its own top-level text
     * input via settingsSchema() instead.
     *
     * @return array<string, string>
     */
    private function fieldPathOptions(): array
    {
        $options = [
            'hostname' => trans('admin/settings/sync_adapters.field_hostname'),
            'serial' => trans('admin/settings/sync_adapters.field_serial'),
            'asset_tag' => trans('admin/settings/sync_adapters.field_asset_tag'),
            'model' => trans('admin/settings/sync_adapters.field_model'),
            'manufacturer' => trans('admin/settings/sync_adapters.field_manufacturer'),
            'mac' => trans('admin/settings/sync_adapters.field_mac'),
            'ip' => trans('admin/settings/sync_adapters.field_ip'),
            'os' => trans('admin/settings/sync_adapters.field_os'),
            'os_version' => trans('admin/settings/sync_adapters.field_os_version'),
            'last_seen' => trans('admin/settings/sync_adapters.field_last_seen'),
            'assigned_user_email' => trans('admin/settings/sync_adapters.field_assigned_user_email'),
            'assigned_user_name' => trans('admin/settings/sync_adapters.field_assigned_user_name'),
        ];

        // Extra destination slots for admins whose vendor exposes
        // per-device metadata that lands in native Asset columns
        // rather than a HostInventoryRecord field.
        $options['native:notes'] = trans('admin/settings/sync_adapters.target_native_notes');
        $options['native:purchase_date'] = \App\SyncAdapters\MappingTargets::labelFor('native:purchase_date');
        $options['native:order_number'] = \App\SyncAdapters\MappingTargets::labelFor('native:order_number');

        // Custom field destinations. Text and textarea-shaped fields
        // land here so admins can route vendor blobs to whatever
        // custom field they've defined on the fieldset.
        foreach (\App\Models\CustomField::whereIn('element', ['text', 'textarea', 'markdown-textarea'])->orderBy('name')->get() as $cf) {
            $options['custom:'.$cf->id] = trans('admin/settings/sync_adapters.target_custom_prefix').': '.$cf->name;
        }

        return $options;
    }

    public static function typeLabel(): string
    {
        return 'Custom HTTP';
    }

    public static function typeSlug(): string
    {
        return 'custom_http';
    }

    public static function docsUrl(): ?string
    {
        return 'https://snipe-it.readme.io/docs/sync-adapters#custom-http-adapter';
    }

    public function settingsSchema(): array
    {
        return [
            [
                'key' => 'auth_method',
                'label' => trans('admin/settings/sync_adapters.label_authentication_method'),
                'type' => 'select',
                'section' => 'auth',
                'options' => self::authMethodOptions(),
                'help' => trans('admin/settings/sync_adapters.custom_auth_method_help'),
            ],
            [
                'key' => 'bearer_token',
                'label' => trans('admin/settings/sync_adapters.label_bearer_token'),
                'secret' => true,
                'required' => false,
                'section' => 'auth',
                'help' => trans('admin/settings/sync_adapters.custom_bearer_token_help'),
                'visible_when' => ['auth_method' => 'bearer'],
            ],
            [
                'key' => 'basic_username',
                'label' => trans('admin/settings/sync_adapters.label_basic_auth_username'),
                'required' => false,
                'section' => 'auth',
                'help' => trans('admin/settings/sync_adapters.custom_basic_username_help'),
                'visible_when' => ['auth_method' => 'basic'],
            ],
            [
                'key' => 'basic_password',
                'label' => trans('admin/settings/sync_adapters.label_basic_auth_password'),
                'secret' => true,
                'required' => false,
                'section' => 'auth',
                'help' => trans('admin/settings/sync_adapters.custom_basic_password_help'),
                'visible_when' => ['auth_method' => 'basic'],
            ],
            [
                'key' => 'api_key_header',
                'label' => trans('admin/settings/sync_adapters.label_api_key_header_name'),
                'required' => false,
                'section' => 'auth',
                'placeholder' => 'X-API-Key',
                'help' => trans('admin/settings/sync_adapters.custom_api_key_header_help'),
                'visible_when' => ['auth_method' => 'api_key'],
            ],
            [
                'key' => 'api_key_value',
                'label' => trans('admin/settings/sync_adapters.label_api_key_value'),
                'secret' => true,
                'required' => false,
                'section' => 'auth',
                'help' => trans('admin/settings/sync_adapters.custom_api_key_value_help'),
                'visible_when' => ['auth_method' => 'api_key'],
            ],
            [
                'key' => 'pull_path',
                'label' => trans('admin/settings/sync_adapters.label_pull_endpoint_path'),
                'required' => false,
                'section' => 'pull',
                'placeholder' => '/api/v1/devices',
                'help' => trans('admin/settings/sync_adapters.custom_pull_path_help'),
                'url_prefix' => true,
            ],
            [
                'key' => 'records_path',
                'label' => trans('admin/settings/sync_adapters.label_records_array_path'),
                'required' => false,
                'section' => 'pull',
                'placeholder' => 'data.devices',
                'help' => trans('admin/settings/sync_adapters.custom_records_path_help'),
            ],
            [
                'key' => 'source_id_path',
                'label' => trans('admin/settings/sync_adapters.label_source_id_path'),
                'required' => true,
                'section' => 'pull',
                'placeholder' => 'id',
                'help' => trans('admin/settings/sync_adapters.custom_source_id_path_help'),
            ],
            // Push spec sits directly under the pull spec because the
            // two are the same shape of thing (endpoint + auth
            // details). Leaving push_path blank makes canPush()
            // return false and the push controls hide themselves, so
            // pull-only admins never have to think about these fields.
            [
                'key' => 'push_method',
                'label' => trans('admin/settings/sync_adapters.label_push_http_method'),
                'type' => 'select',
                'section' => 'push',
                'options' => self::pushMethodOptions(),
                'required' => false,
                'help' => trans('admin/settings/sync_adapters.custom_push_method_help'),
            ],
            [
                'key' => 'push_path',
                'label' => trans('admin/settings/sync_adapters.label_push_endpoint_path'),
                'required' => false,
                'section' => 'push',
                'placeholder' => '/api/v1/devices/{external_id}',
                'help' => trans('admin/settings/sync_adapters.custom_push_path_help'),
                'url_prefix' => true,
            ],
            [
                'key' => 'push_notes_target',
                'label' => trans('admin/settings/sync_adapters.label_push_notes_target_path'),
                'required' => false,
                'section' => 'push',
                'placeholder' => 'notes',
                'help' => trans('admin/settings/sync_adapters.custom_push_notes_target_help'),
            ],
            [
                'key' => 'pagination_style',
                'label' => trans('admin/settings/sync_adapters.label_pagination_style'),
                'type' => 'select',
                'section' => 'pagination',
                'options' => self::paginationStyleOptions(),
                'required' => false,
                'help' => trans('admin/settings/sync_adapters.custom_pagination_style_help'),
            ],
            [
                'key' => 'pagination_page_size',
                'label' => trans('admin/settings/sync_adapters.label_page_size'),
                'required' => false,
                'section' => 'pagination',
                'placeholder' => '500',
                'help' => trans('admin/settings/sync_adapters.custom_pagination_page_size_help'),
                'visible_when' => ['pagination_style' => ['offset_limit', 'page_number']],
            ],
            [
                'key' => 'pagination_limit_param',
                'label' => trans('admin/settings/sync_adapters.label_limit_query_parameter'),
                'required' => false,
                'section' => 'pagination',
                'placeholder' => 'limit',
                'help' => trans('admin/settings/sync_adapters.custom_pagination_limit_param_help'),
                'visible_when' => ['pagination_style' => ['offset_limit', 'page_number']],
            ],
            [
                'key' => 'pagination_offset_param',
                'label' => trans('admin/settings/sync_adapters.label_offset_query_parameter'),
                'required' => false,
                'section' => 'pagination',
                'placeholder' => 'offset',
                'help' => trans('admin/settings/sync_adapters.custom_pagination_offset_param_help'),
                'visible_when' => ['pagination_style' => 'offset_limit'],
            ],
            [
                'key' => 'pagination_page_param',
                'label' => trans('admin/settings/sync_adapters.label_page_query_parameter'),
                'required' => false,
                'section' => 'pagination',
                'placeholder' => 'page',
                'help' => trans('admin/settings/sync_adapters.custom_pagination_page_param_help'),
                'visible_when' => ['pagination_style' => 'page_number'],
            ],
            [
                'key' => 'pagination_page_start',
                'label' => trans('admin/settings/sync_adapters.label_first_page_number'),
                'required' => false,
                'section' => 'pagination',
                'placeholder' => '1',
                'help' => trans('admin/settings/sync_adapters.custom_pagination_page_start_help'),
                'visible_when' => ['pagination_style' => 'page_number'],
            ],
            [
                'key' => 'pagination_next_path',
                'label' => trans('admin/settings/sync_adapters.label_next_page_url_path'),
                'required' => false,
                'section' => 'pagination',
                'placeholder' => 'links.next',
                'help' => trans('admin/settings/sync_adapters.custom_pagination_next_path_help'),
                'visible_when' => ['pagination_style' => 'next_url'],
            ],
            // The field_paths widget renders in its own dedicated
            // fieldset that the shell inserts below the operational
            // checkboxes, not inside the schema-loop. No section here.
            [
                'key' => 'field_paths',
                'label' => trans('admin/settings/sync_adapters.custom_field_paths_label'),
                'type' => 'field_map',
                'required' => false,
                'options' => $this->fieldPathOptions(),
                'help' => trans('admin/settings/sync_adapters.custom_field_paths_help'),
            ],
        ];
    }

    /** @return array<string, string> */
    private static function authMethodOptions(): array
    {
        return [
            'bearer' => trans('admin/settings/sync_adapters.custom_option_auth_bearer'),
            'basic' => trans('admin/settings/sync_adapters.custom_option_auth_basic'),
            'api_key' => trans('admin/settings/sync_adapters.custom_option_auth_api_key'),
            'none' => trans('admin/settings/sync_adapters.custom_option_auth_none'),
        ];
    }

    /** @return array<string, string> */
    private static function pushMethodOptions(): array
    {
        return [
            'disabled' => trans('admin/settings/sync_adapters.custom_option_push_disabled'),
            'PATCH' => 'PATCH',
            'PUT' => 'PUT',
            'POST' => 'POST',
        ];
    }

    /** @return array<string, string> */
    private static function paginationStyleOptions(): array
    {
        return [
            'none' => trans('admin/settings/sync_adapters.custom_option_pagination_none'),
            'offset_limit' => trans('admin/settings/sync_adapters.custom_option_pagination_offset_limit'),
            'page_number' => trans('admin/settings/sync_adapters.custom_option_pagination_page_number'),
            'next_url' => trans('admin/settings/sync_adapters.custom_option_pagination_next_url'),
        ];
    }

    /**
     * Section titles + help copy for the credential schema. The
     * shell partial groups credential-schema entries by their
     * `section` key and renders each group inside its own <fieldset>
     * with these labels. Sections order is derived from first-
     * appearance in settingsSchema().
     */
    public function baseUrlSection(): ?string
    {
        return 'auth';
    }

    public function settingsSections(): array
    {
        return [
            'auth' => [
                'title' => trans('admin/settings/sync_adapters.custom_section_auth_title'),
                'help' => trans('admin/settings/sync_adapters.custom_section_auth_help'),
            ],
            'pull' => [
                'title' => trans('admin/settings/sync_adapters.custom_section_pull_title'),
                'help' => trans('admin/settings/sync_adapters.custom_section_pull_help'),
            ],
            'pagination' => [
                'title' => trans('admin/settings/sync_adapters.custom_section_pagination_title'),
                'help' => trans('admin/settings/sync_adapters.custom_section_pagination_help'),
            ],
            'push' => [
                'title' => trans('admin/settings/sync_adapters.custom_section_push_title'),
                'help' => trans('admin/settings/sync_adapters.custom_section_push_help'),
            ],
        ];
    }

    /**
     * The unified Vendor Response Paths widget lets admins target
     * custom fields and native columns directly. Each such entry
     * needs a mapping.<key> = <key> self-routing config so the
     * framework's applyExtraFieldMappings loop routes the extracted
     * value to its target. Discovery reads the stored field_paths
     * JSON blob and returns each custom:/native: destination as an
     * extra key. Standard HostInventoryRecord field destinations
     * (hostname, serial, etc.) are handled by the base framework, so
     * they don't appear here.
     */
    /**
     * CustomHttp defines its extras via the Vendor Response Paths
     * widget (admin picks custom:X / native:Y destinations and gives
     * them dot-paths). Mapping targets are self-routed (destination
     * = target) and direction is set per-row in Vendor Response
     * Paths, so the base-class "{Adapter}-specific fields" picker
     * would just duplicate what Vendor Response Paths already
     * captures. Hide it.
     */
    public function rendersExtrasPicker(): bool
    {
        return false;
    }

    public function extraFields(): array
    {
        $out = [];
        foreach ($this->fieldPathMap() as $destination => $path) {
            if ($path === '') {
                continue;
            }
            if (! str_starts_with($destination, 'custom:') && ! str_starts_with($destination, 'native:')) {
                continue;
            }
            $out[$destination] = ['label' => $destination, 'admin_defined' => true];
        }

        return $out;
    }

    /**
     * After the base saveConfig persists field_paths + directions,
     * make sure every custom:/native: destination has a self-routing
     * mapping.<key> = <key> config so the framework's extras loop
     * writes each extracted value to its target at sync time, and
     * clean up any destinations the admin removed from the field_paths
     * widget on this save.
     *
     * Deletion is "present in field_paths before, absent
     * after" rather than the mapping-picker omission the earlier
     * revision keyed on. CustomHttp does not render the mapping-picker
     * widget (rendersExtrasPicker() = false), so no `<slug>_mapping`
     * key is ever POSTed, and the omission check matched every
     * pre-existing extra on every save, dropping legitimate mappings
     * on round-trip. (We pulled supportsAdminDefinedExtras() from this
     * adapter since the "Vendor Response Paths" widget should handle that
     * mapping. It was clunky and confusing with both before.)
     */
    public function saveConfig(\Illuminate\Http\Request $request): void
    {
        $slug = $this->instance->slug;

        // Snapshot the stored field_paths BEFORE parent::saveConfig
        // overwrites the credential, so we can diff it against the
        // post-save state and detect widget-side deletions.
        $existingPathsBefore = $this->fieldPathMap();

        parent::saveConfig($request);

        $pathsAfter = $this->fieldPathMap();
        $postedDirections = (array) $request->input($slug.'_direction', []);

        // Deletions: destinations the admin removed via the field_paths
        // widget's × button are absent from `pathsAfter`. parent::
        // persistCredentialsFromSchema already wrote the pruned
        // field_paths credential, so we just need to drop the orphan
        // direction + self-routing mapping entries.
        foreach (array_keys($existingPathsBefore) as $destination) {
            if (! str_starts_with($destination, 'custom:') && ! str_starts_with($destination, 'native:')) {
                continue;
            }
            if (!array_key_exists($destination, $pathsAfter)) {
                SyncAdapterConfig::forget($this->instance->id, 'direction.'.$destination);
                SyncAdapterConfig::forget($this->instance->id, 'mapping.' . $destination);
            }
        }

        // Current entries: write the self-routing mapping and persist
        // the per-row direction. parent::persistFieldMappings forgets
        // every extras `mapping.<key>` on each save (its own cleanup
        // keys on the mapping-picker POST, which this adapter does not
        // emit), so we re-write them here for the destinations the
        // admin kept.
        foreach (array_keys($pathsAfter) as $destination) {
            if (!str_starts_with($destination, 'custom:') && !str_starts_with($destination, 'native:')) {
                continue;
            }

            SyncAdapterConfig::put($this->instance->id, 'mapping.'.$destination, $destination);

            if (isset($postedDirections[$destination])) {
                $direction = (string) $postedDirections[$destination];
                if (in_array($direction, ['pull', 'push', 'both', 'skip'], true)) {
                    SyncAdapterConfig::put($this->instance->id, 'direction.'.$destination, $direction);
                }
            }
        }
    }

    /**
     * Push is available whenever the admin picks a real HTTP verb
     * for push_method. Selecting "Disabled (pull only)" (the default
     * for fresh instances) is the opt-out and hides the push
     * controls on the settings page. Using the method as the switch
     * lets admins share the pull path for push (or leave both blank
     * to POST/PATCH the base URL itself) without accidentally
     * turning push off.
     */
    public function canPush(): bool
    {
        $method = $this->safeCredential('push_method');

        return $method !== '' && $method !== 'disabled';
    }

    /**
     * Admin-defined dot-path where composed notes should land in the
     * outgoing payload. Empty means the admin didn't wire up notes,
     * so the framework's composed-notes feature skips this instance.
     */
    public function notesFieldTarget(): ?string
    {
        $target = $this->safeCredential('push_notes_target');

        return $target === '' ? null : $target;
    }

    /**
     * Push Snipe-IT-authoritative field values to the admin's HTTP
     * endpoint. Payload is built by placing each push-directed
     * field's value at its configured dot-path via Arr::set, so the
     * same admin-defined field_* dot-paths that drove pull() also
     * drive the outgoing shape here. Fields the admin didn't
     * configure a dot-path for are silently skipped, matching the
     * pull side's "unmapped means unused" behavior.
     *
     * @param  array<int, string>  $changedFields
     */
    /**
     * Diagnostic: build the exact HTTP shape push() would send for
     * the given asset without actually sending it (and without
     * writing the "would send" log line that dry-run push would).
     * Returned shape is directly serializable by the Preview Push
     * Payload AJAX endpoint. Null when the request shape couldn't be
     * built - the skip reason is already in the sync-adapters log via
     * the pushPrologue / buildPushPayload calls that fire here.
     *
     * TODO/followup: create vs update endpoint shape - POST to
     * /hardware when the asset isn't linked yet vs PATCH to
     * /hardware/{id} when it is. Current preview only exercises the
     * linked-asset path.
     *
     * @return array{method: string, endpoint: string, payload: array<string, mixed>}|null
     */
    public function peekPushPayload(Asset $asset): ?array
    {
        $externalSource = $this->pushPrologue($asset, []);
        if ($externalSource === null || ! $this->canPush()) {
            return null;
        }

        [$payload] = $this->buildPushPayload($asset);
        if ($payload === []) {
            return null;
        }

        return [
            'method' => $this->resolvedPushMethod(),
            'endpoint' => rtrim($this->url(), '/').$this->resolvedPushPath($this->safeCredential('push_path'), (string) $externalSource->external_id),
            'payload' => $payload,
        ];
    }

    public function push(Asset $asset, array $changedFields = []): bool
    {
        $externalSource = $this->pushPrologue($asset, $changedFields);
        if ($externalSource === null) {
            // pushPrologue already logged the specific skip reason.
            return false;
        }
        if (! $this->canPush()) {
            Log::channel('sync-adapters')->info(sprintf(
                '%s push: skipped asset %d (asset_tag=%s) - canPush() returned false (missing URL or push endpoint configuration)',
                $this->name(),
                $asset->id,
                $asset->asset_tag ?? '(none)',
            ));

            return false;
        }

        [$payload, $touched] = $this->buildPushPayload($asset);
        if ($payload === []) {
            Log::channel('sync-adapters')->info(sprintf(
                '%s push: skipped asset %d (asset_tag=%s) - empty payload (no push-directed fields produced a value for this asset)',
                $this->name(),
                $asset->id,
                $asset->asset_tag ?? '(none)',
            ));

            return false;
        }

        // Blank push_path is allowed. Combined with a non-blank base
        // URL it targets that base URL exactly, which some APIs
        // accept for record-scoped writes when the id is inside the
        // request body rather than the path.
        $endpoint = rtrim($this->url(), '/').$this->resolvedPushPath($this->safeCredential('push_path'), (string) $externalSource->external_id);
        $method = $this->resolvedPushMethod();

        if ($this->isPushDryRun()) {
            Log::channel('sync-adapters')->info(sprintf(
                '%s push [dry-run]: would %s %s with %s',
                $this->name(),
                $method,
                $endpoint,
                json_encode($payload, JSON_UNESCAPED_SLASHES),
            ));

            return true;
        }

        $this->dispatchPushRequest($method, $endpoint, $payload, $touched);

        return true;
    }

    /**
     * Build the JSON body for a push, driven by the admin's field-map
     * paths (standard fields) plus the composed-notes template if one
     * is configured. Returns the payload and the flat list of dot-paths
     * that were written, for the post-push audit log line.
     *
     * Empty payload means nothing was mapped OR every mapped field on
     * this asset was null: caller uses that as the signal to skip the
     * HTTP round-trip entirely.
     *
     * @return array{0: array<string, mixed>, 1: array<int, string>}
     */
    protected function buildPushPayload(Asset $asset): array
    {
        $payload = [];
        $touched = [];
        $fieldPaths = $this->fieldPathMap();
        foreach ($this->pushDirectedFields() as $field) {
            $path = $fieldPaths[$field] ?? '';
            if ($path === '') {
                // Push direction set but no vendor-side dot-path
                // configured for this field in the Custom HTTP
                // "Field paths" widget. Nothing to write to without a
                // path, so the field is dropped silently otherwise.
                Log::channel('sync-adapters')->info(sprintf(
                    '%s push: asset %d (asset_tag=%s) - field "%s" is directed push but has no vendor-side path configured in field_paths',
                    $this->name(),
                    $asset->id,
                    $asset->asset_tag ?? '(none)',
                    $field,
                ));

                continue;
            }
            $value = $this->assetValueForSourceField($asset, $field);
            if ($value === null) {
                Log::channel('sync-adapters')->info(sprintf(
                    '%s push: asset %d (asset_tag=%s) - field "%s" resolves to null on this asset, nothing to push',
                    $this->name(),
                    $asset->id,
                    $asset->asset_tag ?? '(none)',
                    $field,
                ));

                continue;
            }
            Arr::set($payload, $path, $value);
            $touched[] = $path;
        }

        $this->applyComposedNotesToPayload($asset, $payload, $touched);

        return [$payload, $touched];
    }

    /**
     * Resolve the admin-configured HTTP verb, upper-casing the stored
     * value and falling back to PATCH for anything not in the accepted
     * set. 'disabled' never actually reaches here because canPush()
     * bails upstream, but the fallback covers it defensively along
     * with any unrecognized user input.
     */
    private function resolvedPushMethod(): string
    {
        $method = strtoupper($this->safeCredential('push_method')) ?: 'PATCH';

        return in_array($method, ['PATCH', 'PUT', 'POST'], true) ? $method : 'PATCH';
    }

    /**
     * Fire the HTTP request and log the outcome. Failures fail soft:
     * the exception is logged with the request shape and we
     * return cleanly so a single vendor-side error doesn't abort the
     * enclosing push loop for other assets.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $touched
     */
    private function dispatchPushRequest(string $method, string $endpoint, array $payload, array $touched): void
    {
        $request = $this->applyAuth(Http::withOptions(['allow_redirects' => false])->acceptJson()->timeout(300));

        try {
            $request->send($method, $endpoint, ['json' => $payload])->throw();
        } catch (\Throwable $e) {
            Log::channel('sync-adapters')->warning(sprintf(
                '%s push failed: %s %s: %s',
                $this->name(),
                $method,
                $endpoint,
                $e->getMessage(),
            ));

            return;
        }

        Log::channel('sync-adapters')->info(sprintf(
            '%s push: %s %s wrote paths [%s]',
            $this->name(),
            $method,
            $endpoint,
            implode(', ', $touched),
        ));
    }

    /**
     * Substitute the {external_id} placeholder in the admin's push
     * path template. Kept as an explicit method so unit tests can
     * verify the substitution shape independent of the HTTP round
     * trip.
     */
    private function resolvedPushPath(string $template, string $externalId): string
    {
        if ($template === '') {
            // Blank template means "hit the Base URL directly." Return
            // an empty string so the endpoint assembly below just uses
            // rtrim($this->url(), '/'). Prepending '/' would add a
            // trailing slash the vendor may or may not accept.
            return '';
        }
        $path = str_replace('{external_id}', rawurlencode($externalId), $template);
        if (! str_starts_with($path, '/')) {
            $path = '/'.$path;
        }

        return $path;
    }

    public function pull(): iterable
    {
        $fieldPaths = $this->fieldPathMap();
        $extras = $this->extrasDefinition();

        if ($fieldPaths['source_id'] === '') {
            // Without a source_id path the framework can't key records
            // back to their vendor row, so upserts collapse and every
            // sync pass overwrites the last one. Abort with a clear
            // log line instead of yielding un-keyed records.
            Log::channel('sync-adapters')->warning(sprintf(
                '%s pull aborted: Source ID Path is required. Set the "Source ID Path" input above Vendor Response Paths to the dot-path of your vendor\'s unique record id.',
                $this->name(),
            ));

            return;
        }

        // Track null rates per configured dot-path across every
        // record processed. Paths that return null on 100% of
        // records are almost always typos or renamed vendor fields.
        // dotPathGet returns null silently on miss, so without an
        // end-of-run summary these mistakes hide until an admin
        // notices the destination column is empty on every asset.
        $configuredPaths = [];
        foreach ($fieldPaths as $destination => $path) {
            if ($path !== '' && $destination !== 'source_id') {
                $configuredPaths[$destination] = $path;
            }
        }
        foreach ($extras as $extra) {
            $configuredPaths[$extra['key']] = $extra['path'];
        }
        $pathStats = array_fill_keys(array_keys($configuredPaths), 0);
        $recordCount = 0;

        $globalIndex = 0;
        foreach ($this->paginate() as $records) {
            foreach ($records as $record) {
                if (! is_array($record)) {
                    $globalIndex++;

                    continue;
                }
                $normalized = $this->normalize($record, $fieldPaths, $extras);
                if ($normalized === null) {
                    Log::channel('sync-adapters')->info(sprintf(
                        '%s pull skipped record at index %s: source_id path "%s" did not resolve to a scalar value',
                        $this->name(),
                        $globalIndex,
                        $fieldPaths['source_id'],
                    ));
                    $globalIndex++;

                    continue;
                }
                $recordCount++;
                foreach ($configuredPaths as $destination => $path) {
                    if (self::dotPathGet($record, $path) === null) {
                        $pathStats[$destination]++;
                    }
                }
                yield $normalized;
                $globalIndex++;
            }
        }

        $this->logAlwaysNullPaths($configuredPaths, $pathStats, $recordCount);
    }

    /**
     * Emit a sync-adapters-log warning listing every configured
     * dot-path that returned null on every record we saw. Fires only
     * when we actually processed records so an empty vendor response
     * doesn't get mis-flagged as a typo.
     *
     * @param  array<string, string>  $configuredPaths  destination => dot-path
     * @param  array<string, int>  $pathStats  destination => null-count
     */
    private function logAlwaysNullPaths(array $configuredPaths, array $pathStats, int $recordCount): void
    {
        if ($recordCount === 0) {
            return;
        }

        $missing = [];
        foreach ($pathStats as $destination => $nullCount) {
            if ($nullCount === $recordCount) {
                $missing[] = $destination.' → '.$configuredPaths[$destination];
            }
        }
        if ($missing === []) {
            return;
        }

        $adapter = $this->name();
        $list = implode(', ', $missing);
        Log::channel('sync-adapters')->warning(
            "{$adapter} pull: processed {$recordCount} record(s), but these Vendor Response Paths returned no data on any record (typo or wrong path?): {$list}"
        );
    }

    /**
     * Walk the vendor's paginated response and yield one page's records
     * array per iteration.
     *
     * Yields nothing (returns cleanly) on the terminal conditions:
     * body fetch failed, records path did not resolve to an array,
     * server signaled the last page (short page for offset/page,
     * missing next-URL for next_url), or the PAGINATION_MAX_PAGES
     * cap was hit.
     *
     * @return iterable<int, array<int, mixed>>
     */
    private function paginate(): iterable
    {
        $recordsPath = $this->safeCredential('records_path');
        $paginationStyle = $this->safeCredential('pagination_style') ?: 'none';
        $pageSize = max(1, (int) ($this->safeCredential('pagination_page_size') ?: 500));
        $limitParam = $this->safeCredential('pagination_limit_param') ?: 'limit';
        $offsetParam = $this->safeCredential('pagination_offset_param') ?: 'offset';
        $pageParam = $this->safeCredential('pagination_page_param') ?: 'page';
        // Explicit null/empty check because "0" is a valid pageStart
        // (zero-based APIs) but "0" ?: 1 would coerce to 1.
        $pageStartRaw = $this->safeCredential('pagination_page_start');
        $pageStart = $pageStartRaw === '' ? 1 : (int) $pageStartRaw;
        $nextPath = $this->safeCredential('pagination_next_path');

        $offset = 0;
        $pageNumber = $pageStart;
        $nextUrl = null;
        $pageIndex = 0;

        while ($pageIndex < self::PAGINATION_MAX_PAGES) {
            // Offset-limit style keeps re-hitting the same pull_path with
            // increasing offsets. Page-number style does the same with
            // an incrementing page counter (1-based by default, though
            // pagination_page_start lets 0-based APIs opt in). next-url
            // style follows the URL the previous response handed back.
            // First iteration of every style starts on the configured
            // pull_path.
            $body = match ($paginationStyle) {
                'offset_limit' => $this->fetchResponseBody(null, [$limitParam => $pageSize, $offsetParam => $offset]),
                'page_number' => $this->fetchResponseBody(null, [$limitParam => $pageSize, $pageParam => $pageNumber]),
                default => $this->fetchResponseBody($nextUrl, []),
            };
            if ($body === null) {
                return;
            }

            $records = self::dotPathGet($body, $recordsPath);
            if (! is_array($records)) {
                Log::channel('sync-adapters')->warning(sprintf(
                    '%s pull expected an array at records path "%s", got %s',
                    $this->name(),
                    $recordsPath,
                    get_debug_type($records),
                ));

                return;
            }

            yield $records;

            $pageIndex++;

            // Terminate: none = single page. offset_limit / page_number
            // = last page seen (fewer records than page_size). next_url
            // = the response body no longer contains a next-page URL.
            if ($paginationStyle === 'none') {
                return;
            }
            if ($paginationStyle === 'offset_limit') {
                if (count($records) < $pageSize) {
                    return;
                }
                $offset += $pageSize;

                continue;
            }
            if ($paginationStyle === 'page_number') {
                if (count($records) < $pageSize) {
                    return;
                }
                $pageNumber++;

                continue;
            }
            if ($paginationStyle === 'next_url') {
                $nextUrl = self::stringOrNull(self::dotPathGet($body, $nextPath));
                if ($nextUrl === null || $nextUrl === '') {
                    return;
                }

                continue;
            }

            // Unknown style. Rare (validation should have caught it),
            // but fall through so the loop can't spin forever.
            return;
        }

        Log::channel('sync-adapters')->warning(sprintf(
            '%s pull halted after hitting the %d-page safety cap. Check your pagination configuration.',
            $this->name(),
            self::PAGINATION_MAX_PAGES,
        ));
    }

    /**
     * Issue the configured HTTP GET and return the decoded body.
     * Any auth-misconfiguration, network, or non-2xx response fails
     * soft: log a warning on the sync-adapters channel and return
     * null so we yield nothing rather than crashing the
     * sync run.
     *
     * $overrideUrl is used by next-url pagination to follow the
     * absolute URL the previous page returned rather than rebuilding
     * from base + pull_path. $queryParams is used by offset-limit
     * pagination to append pagination parameters to the standard
     * pull_path each iteration.
     *
     * @param  array<string, mixed>  $queryParams
     */
    private function fetchResponseBody(?string $overrideUrl = null, array $queryParams = []): mixed
    {
        $baseUrl = rtrim($this->url(), '/');
        if ($baseUrl === '') {
            Log::channel('sync-adapters')->warning(sprintf(
                '%s pull aborted: base URL is empty',
                $this->name(),
            ));

            return null;
        }

        if ($overrideUrl !== null) {
            // next_url pagination follows a URL from the response body.
            // Strip to path+query and pin the scheme+host from the
            // configured base URL so a hostile upstream can't redirect
            // the next request (and its auth header) to an attacker-
            // chosen target.
            $origin = $this->configuredOrigin($baseUrl);
            if ($origin === null) {
                Log::channel('sync-adapters')->warning($this->name().' pull aborted: base URL is unparseable');

                return null;
            }

            $parsed = parse_url($overrideUrl);
            if (! is_array($parsed)) {
                Log::channel('sync-adapters')->warning($this->name().' pull aborted: next-page URL is malformed');

                return null;
            }

            // Reject path-relative cursors with no leading slash
            // ("page2" rather than "/page2"). Rebasing those produced
            // an unparseable "https://hostpage2" that aborted the sync
            // on the next iteration. Fails safe but we'd rather be
            // explicit (reported post-fix by Zer0Gate as a robustness
            // note). Only path+query is kept from the supplied cursor
            // on purpose, so any path prefix on the configured base
            // URL is dropped. Absolute cursors are the standard shape
            // and this matches that.
            $path = $parsed['path'] ?? '/';
            if (! str_starts_with($path, '/')) {
                Log::channel('sync-adapters')->warning($this->name().' pull aborted: next-page URL is path-relative with no leading slash');

                return null;
            }

            $endpoint = $origin.$path.(isset($parsed['query']) ? '?'.$parsed['query'] : '');
        } else {
            $pullPath = $this->safeCredential('pull_path');
            if ($pullPath !== '' && ! str_starts_with($pullPath, '/')) {
                $pullPath = '/'.$pullPath;
            }
            $endpoint = $baseUrl.$pullPath;
        }

        [$endpoint, $mergedQuery] = $this->mergeQueryIntoEndpoint($endpoint, $queryParams);

        $request = Http::withOptions(['allow_redirects' => false])->acceptJson()->timeout(300);
        $request = $this->applyAuth($request);

        // ->throw() propagates on non-2xx so the controller can catch
        // and render a red-flash sanitized summary. Swallowing here
        // masked 401 (bad bearer) and 5xx as "Synced 0 host(s), 0
        // error(s)", which reads as false success to an admin.
        //
        // Only pass the second arg when non-empty: Laravel's HTTP
        // client sets Guzzle's `query` option whenever any value is
        // present (including `[]`), and Guzzle then rewrites the URL's
        // query with `http_build_query` on that value. An empty array
        // therefore strips the query already on $endpoint. One-arg
        // get() preserves it.
        $response = $mergedQuery === []
            ? $request->get($endpoint)
            : $request->get($endpoint, $mergedQuery);

        return $response->throw()->json();
    }

    /**
     * Fold any query already present on $endpoint into $queryParams
     * so pagination keys don't silently discard the admin's
     * filtered-pull_path query (reporter #19755), and the next_url
     * cursor's own query (e.g. `$skiptoken=XYZ`) survives the second
     * hop. Pagination keys win on conflict so adapter-driven iteration
     * can override an identically-named admin param.
     *
     * @param  array<string, mixed>  $queryParams
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function mergeQueryIntoEndpoint(string $endpoint, array $queryParams): array
    {
        $queryStart = strpos($endpoint, '?');
        if ($queryStart === false) {
            return [$endpoint, $queryParams];
        }

        $existing = [];
        parse_str(substr($endpoint, $queryStart + 1), $existing);

        return [substr($endpoint, 0, $queryStart), array_replace($existing, $queryParams)];
    }

    /**
     * Extract scheme+host(+port) from the configured base URL so a
     * server-supplied next-page URL can be re-based onto the admin's
     * configured host. Returns null when the base URL is unparseable.
     */
    private function configuredOrigin(string $baseUrl): ?string
    {
        $parts = parse_url($baseUrl);
        if (! is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }

        return $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');
    }

    /**
     * Attach the configured auth shape to the pending request.
     * Silent no-op on 'none' (or an unrecognized method), so a
     * misconfiguration surfaces as a 401 from the vendor rather than
     * a runtime error inside the adapter.
     */
    private function applyAuth(\Illuminate\Http\Client\PendingRequest $request): \Illuminate\Http\Client\PendingRequest
    {
        $method = $this->safeCredential('auth_method');

        return match ($method) {
            'bearer' => $request->withToken($this->safeCredential('bearer_token')),
            'basic' => $request->withBasicAuth(
                $this->safeCredential('basic_username'),
                $this->safeCredential('basic_password'),
            ),
            'api_key' => $request->withHeaders([
                ($this->safeCredential('api_key_header') ?: 'X-API-Key') => $this->safeCredential('api_key_value'),
            ]),
            default => $request,
        };
    }

    /**
     * Turn one raw record from the vendor's payload into a
     * HostInventoryRecord. Every value walks through dotPathGet, so
     * missing keys just leave the corresponding field null and the
     * framework's normal null-guarding downstream handles the rest.
     *
     * @param  array<string, mixed>  $record
     * @param  array<string, string>  $fieldPaths
     * @param  array<int, array{key: string, label: string, path: string}>  $extras
     */
    private function normalize(array $record, array $fieldPaths, array $extras): ?HostInventoryRecord
    {
        // stringOrNull returns null for arrays / objects, so a
        // vendor payload that unexpectedly nests something at the
        // source_id path (or that returned the whole record because
        // the path was blank) yields null instead of a fatal (string)
        // cast on an array. Skip the record.
        $sourceId = self::stringOrNull(self::dotPathGet($record, $fieldPaths['source_id']));
        if ($sourceId === null || $sourceId === '') {
            return null;
        }

        $lastSeen = null;
        $lastSeenRaw = self::dotPathGet($record, $fieldPaths['last_seen']);
        if (is_string($lastSeenRaw) && $lastSeenRaw !== '') {
            try {
                $lastSeen = Carbon::parse($lastSeenRaw);
            } catch (\Throwable) {
                // Malformed date string, leave null rather than throw.
            }
        }

        $extraValues = [];
        foreach ($extras as $extra) {
            $raw = self::dotPathGet($record, $extra['path']);
            // Decode HTML entities on scalar extras for the same
            // reason we do it on standard fields above. Non-scalar
            // values pass through unchanged so downstream code (the
            // framework's mapping layer) can still stringify them.
            $extraValues[$extra['key']] = is_scalar($raw)
                ? html_entity_decode((string) $raw, ENT_QUOTES | ENT_HTML5, 'UTF-8')
                : $raw;
        }

        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: $sourceId,
            hostname: self::stringOrNull(self::dotPathGet($record, $fieldPaths['hostname'])),
            hardwareSerial: self::stringOrNull(self::dotPathGet($record, $fieldPaths['serial'])),
            hardwareModel: self::stringOrNull(self::dotPathGet($record, $fieldPaths['model'])),
            manufacturer: self::stringOrNull(self::dotPathGet($record, $fieldPaths['manufacturer'])),
            primaryMac: self::stringOrNull(self::dotPathGet($record, $fieldPaths['mac'])),
            primaryIp: self::stringOrNull(self::dotPathGet($record, $fieldPaths['ip'])),
            os: self::stringOrNull(self::dotPathGet($record, $fieldPaths['os'])),
            osVersion: self::stringOrNull(self::dotPathGet($record, $fieldPaths['os_version'])),
            lastSeen: $lastSeen,
            assetTag: self::stringOrNull(self::dotPathGet($record, $fieldPaths['asset_tag'])),
            assignedUserEmail: self::stringOrNull(self::dotPathGet($record, $fieldPaths['assigned_user_email'])),
            assignedUserName: self::stringOrNull(self::dotPathGet($record, $fieldPaths['assigned_user_name'])),
            extra: $extraValues,
        );
    }

    private static function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (! is_scalar($value)) {
            return null;
        }

        // Decode HTML entities before storing. Some APIs
        // (Snipe-IT's own /api/v1/hardware is one of them) run their
        // JSON string values through htmlspecialchars() in the
        // transformer, so a model name like `MacBook Pro 13"` shows
        // up on the wire as `MacBook Pro 13&quot;`. Without decoding
        // we would round-trip the entity into the asset table and it
        // would display as literal text on the asset page. Decode is
        // idempotent for well-behaved APIs that never HTML-escape,
        // so this is safe as a default.
        return html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Wrapper around credential() that returns '' rather than
     * throwing when the key isn't stored yet. The base credential()
     * throws on missing values because most adapters treat that as
     * misconfiguration. Custom is more forgiving: half-configured
     * instances just yield empty fields on unmapped paths.
     */
    private function safeCredential(string $key): string
    {
        try {
            return (string) $this->credential($key);
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * Decode the field_paths JSON blob into a {snipe_field: path}
     * map, keyed by every option in fieldPathOptions() plus the
     * standalone source_id_path credential. Missing fields default
     * to ''.
     *
     * @return array<string, string>
     */
    private function fieldPathMap(): array
    {
        $baseline = array_fill_keys(array_keys($this->fieldPathOptions()), '');
        // source_id lives outside the repeater as its own required
        // input, so merge it in here to give a uniform
        // $map['source_id'] lookup.
        $baseline['source_id'] = $this->safeCredential('source_id_path');

        $raw = $this->safeCredential('field_paths');
        if (trim($raw) === '') {
            return $baseline;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return $baseline;
        }

        foreach ($decoded as $key => $value) {
            if (! is_string($key) || ! is_string($value)) {
                continue;
            }
            // Standard fields and current custom/native destinations
            // pass the baseline check. Admin-added custom:/native:
            // entries whose target has since been deleted from
            // Snipe-IT still deserialize so extraFields() can surface
            // the orphan and the admin can clean up.
            if (array_key_exists($key, $baseline)
                || str_starts_with($key, 'custom:')
                || str_starts_with($key, 'native:')) {
                $baseline[$key] = $value;
            }
        }

        return $baseline;
    }

    /**
     * List of admin-defined extras for the pull-time normalize() to
     * iterate. Since path is the mapping key on Custom HTTP (the
     * admin-extras-picker rebuild removed the separate key/label
     * fields), each entry's key IS its path. Discovery mirrors
     * extraFields(): walk stored field_paths, keep only custom: /
     * native: destinations that actually have a non-empty dot-path.
     *
     * @return array<int, array{key: string, label: string, path: string}>
     */
    private function extrasDefinition(): array
    {
        $out = [];
        foreach ($this->fieldPathMap() as $destination => $path) {
            if ($path === '') {
                continue;
            }
            if (! str_starts_with($destination, 'custom:') && ! str_starts_with($destination, 'native:')) {
                continue;
            }
            $out[] = [
                'key' => $destination,
                'label' => $destination,
                'path' => $path,
            ];
        }

        return $out;
    }
}
