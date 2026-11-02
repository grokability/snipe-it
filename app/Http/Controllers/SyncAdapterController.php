<?php

namespace App\Http\Controllers;

use App\Models\Actionlog;
use App\SyncAdapters\SyncErrorFormatter;
use App\Traits\LocksLongRunningOperation;
use App\SyncAdapters\PushableSyncAdapter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Monolog\Handler\TestHandler;
use Symfony\Component\HttpFoundation\Response;

class SyncAdapterController extends Controller
{
    use LocksLongRunningOperation;

    public function getAdapters(Request $request): View|RedirectResponse
    {
        $adapterTypes = \App\SyncAdapters\SyncAdapter::typeLabels();
        $adapterCatalog = \App\SyncAdapters\SyncAdapter::typeCatalog();

        // The filter is for UX when per-company adapters exist. Gate on whether the
        // install has any companies at all. 'Shared' means "no company_id" (built-ins and adapters available to all).
        $companies = \App\Models\Company::orderBy('name')->pluck('name', 'id')->all();
        $hasCompanies = count($companies) > 0;
        $selectedCompany = $hasCompanies ? $request->query('company') : null;

        $instanceQuery = \App\Models\SyncAdapterInstance::query()->orderBy('label');
        if ($selectedCompany === 'shared') {
            $instanceQuery->whereNull('company_id');
        } elseif ($selectedCompany !== null && $selectedCompany !== '') {
            // Company-scoped view includes the shared built-ins too, since
            // those are available to every company. Otherwise picking a
            // specific company would hide Fleet, Kandji, and friends.
            $instanceQuery->where(function ($q) use ($selectedCompany) {
                $q->where('company_id', (int) $selectedCompany)
                    ->orWhereNull('company_id');
            });
        }

        // Sort: enabled (green dot) first, then partial (yellow),
        // then inactive (red). Within each bucket, preserve the
        // database ORDER BY label.
        $readinessRank = ['active' => 0, 'partial' => 1, 'inactive' => 2];
        $adapters = $instanceQuery->get()
            ->map(fn ($i) => \App\SyncAdapters\SyncAdapter::factory($i))
            ->filter()
            ->sortBy(fn ($a) => $readinessRank[$a->readinessStatus()] ?? 99)
            ->values()
            ->all();

        // Default selection: an explicit ?adapter=slug wins if present.
        // Otherwise prefer the first-enabled adapter.
        $requestedSlug = $request->query('adapter');
        if ($requestedSlug !== null) {
            $selectedSlug = $requestedSlug;
        } else {
            $default = collect($adapters)->first(fn ($a) => $a->isEnabled()) ?? ($adapters[0] ?? null);
            $selectedSlug = $default?->name();
        }
        $selected = collect($adapters)->first(fn ($a) => $a->name() === $selectedSlug);

        // Explicit ?adapter=slug pointing at a non-existent instance
        // (deleted, typo, stale bookmark). Redirect to the default
        // view with an error
        if ($requestedSlug !== null && $selected === null) {
            return redirect()->route('settings.adapters.index')
                ->with('error', trans('admin/settings/sync_adapters.not_found', ['slug' => $requestedSlug]));
        }

        // Per-adapter count of asset_external_sources rows keyed by
        // source slug. Powers the delete-confirmation message so
        // admins see how many synced assets they are about to
        // "orphan" (assets stay, external-source link stays, sync
        // just stops). One grouped query rather than N per-adapter
        // COUNTs so the settings page stays cheap for installs with
        // many configured adapters.
        $syncedCounts = \App\Models\AssetExternalSource::query()
            ->selectRaw('source, COUNT(*) as c')
            ->groupBy('source')
            ->pluck('c', 'source')
            ->all();

        return view('settings.adapters', compact(
            'adapters', 'adapterTypes', 'adapterCatalog', 'selected', 'companies', 'hasCompanies', 'selectedCompany', 'syncedCounts',
        ));
    }

    /**
     * Create a new adapter instance.
     */
    public function postCreateAdapterInstance(Request $request): RedirectResponse
    {
        if (config('app.lock_passwords')) {
            return redirect()->back()->with('error', trans('general.feature_disabled'));
        }

        $validated = $request->validate([
            'adapter_type' => 'required|string|in:'.implode(',', \App\SyncAdapters\SyncAdapter::typeNames()),
            'label' => 'required|string|max:191|unique:sync_adapter_instances,label',
            'company_id' => 'nullable|integer|exists:companies,id',
        ]);

        // Start inactive. Active + no URL/token means "Active" ticked
        // on the settings page while Sync Now stays disabled because
        // isEnabled() also requires at least minimal config.
        $instance = new \App\Models\SyncAdapterInstance;
        $instance->fill([
            'adapter_type' => $validated['adapter_type'],
            'label' => $validated['label'],
            'company_id' => $validated['company_id'] ?? null,
            'active' => false,
        ]);

        $instance->created_by = auth()->id();
        $instance->save();

        return redirect()->route('settings.adapters.index', ['adapter' => $instance->slug])
            ->with('success', trans('admin/settings/sync_adapters.instance_created'));
    }

    /**
     * Clone an existing adapter instance. Creates a new inactive
     * instance with the same adapter_type as the source and copies
     * every SyncAdapterConfig row across so the admin lands on a
     * near-identical setup, ready for a per-clone tweak (typically
     * a different company_id or a swapped URL / token). The clone
     * starts inactive so the admin reviews before enabling sync.
     */
    public function postCloneAdapterInstance(Request $request, \App\Models\SyncAdapterInstance $instance): RedirectResponse
    {
        if (config('app.lock_passwords')) {
            return redirect()->back()->with('error', trans('general.feature_disabled'));
        }

        $validated = $request->validate([
            'label' => 'required|string|max:191|unique:sync_adapter_instances,label',
            'company_id' => 'nullable|integer|exists:companies,id',
        ]);

        $clone = new \App\Models\SyncAdapterInstance;
        $clone->fill([
            'adapter_type' => $instance->adapter_type,
            'label' => $validated['label'],
            'company_id' => $validated['company_id'] ?? null,
            'active' => false,
        ]);
        $clone->created_by = auth()->id();
        $clone->save();

        // Copy the whole config blob to the new instance. Encrypted
        // secrets copy as-is since both rows use the same APP_KEY.
        // Runtime state (last_synced_at, etc.) lives on its own
        // instance columns, not in config, so nothing survives from
        // the source's operational history.
        $clone->config = $instance->config;
        $clone->save();

        return redirect()->route('settings.adapters.index', ['adapter' => $clone->slug])
            ->with('success', trans('admin/settings/sync_adapters.instance_cloned', ['label' => $instance->label]));
    }

    /**
     * Save handler for an instance's own config form. Instance-bound
     * route param resolves the target instance. the shipped adapter
     * class knows how to persist its own fields.
     */
    public function postAdapterConfig(Request $request, \App\Models\SyncAdapterInstance $instance): RedirectResponse
    {
        if (config('app.lock_passwords')) {
            return redirect()->back()->with('error', trans('general.feature_disabled'));
        }

        $adapter = $instance->adapter();
        if ($adapter === null) {
            abort(404);
        }

        // Adapter declares its own validation (ExternalUrl on the URL
        // field blocks loopback / RFC-1918 / metadata targets so the
        // sync path can't be abused as an SSRF or port-scan primitive).
        // Label uniqueness is enforced independently so a rename can't
        // collide with an existing instance's label. `sometimes` because
        // the field is optional at the wire level: not sending label
        // leaves the current label in place (below).
        $request->validate(
            $adapter->validationRules() + [
                'label' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:191',
                    \Illuminate\Validation\Rule::unique('sync_adapter_instances', 'label')->ignore($instance->id),
                ],
                'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            ]
        );

        // "active" checkbox lives on the shared partial with a slug-
        // prefixed name so tab-panes don't collide on ids. Unchecked
        // comes through as absent from the request, so default to false.
        $instance->active = $request->boolean($instance->slug.'_active');
        $instance->label = $request->input('label', $instance->label);
        // Empty string in the company-select posts as '' rather than
        // absent, so normalise to null for the shared-across-companies
        // sentinel value.
        $companyId = $request->input('company_id');
        $instance->company_id = $companyId === '' ? null : $companyId;
        $instance->save();

        $adapter->saveConfig($request);

        return redirect()->route('settings.adapters.index', ['adapter' => $instance->slug])
            ->with('success', trans('admin/settings/message.update.success'));
    }

    /**
     * Manually trigger a sync from the "Sync Now" button.
     * Realistically, this will likely time out in a real production instance,
     * so this is used mostly for testing now and will likely be removed later
     */
    public function postAdapterSync(Request $request, \App\Models\SyncAdapterInstance $instance): RedirectResponse
    {
        $adapter = $instance->adapter();
        if ($adapter === null) {
            abort(404);
        }

        // Hidden mirror from the panel's dry-run checkbox so the admin's
        // live intent is honored for this one click without needing to
        // Save the config form first.
        if ($request->has('dry_run_override')) {
            $adapter->withPullDryRunOverride($request->boolean('dry_run_override'));
        }

        if (! $adapter->isEnabled()) {
            // Capture even the fail-fast exit so the per-run log + file
            // log carry a record of why Sync Now did nothing. The flash
            // message alone is one-shot and vanishes on reload.
            $message = trans('admin/settings/sync_adapters.not_configured');
            $this->captureSyncRunLog($instance->slug, 'pull', function () use ($instance, $message) {
                Log::channel('sync-adapters')->warning($instance->slug.' sync aborted before run: not_configured - '.$message);
            });

            return redirect()->route('settings.adapters.index', ['adapter' => $instance->slug])
                ->with('error', $message);
        }

        // Per-instance mutex: a pull or push already in progress for THIS
        // adapter blocks a second trigger. Different adapters stay
        // independent (Fleet pulling doesn't block a Jamf pull, etc).
        if (!$this->acquireOperationLock($instance->lockKey())) {
            return redirect()->route('settings.adapters.index', ['adapter' => $instance->slug])
                ->with('error', $this->operationAlreadyRunningMessage('admin/settings/sync_adapters.sync_adapter_run'));
        }

        try {

            // Drop PHP's execution-time cap. Web-server proxy timeouts still apply tho
            set_time_limit(0);

            $seen = 0;
            $skipped = 0;
            $errors = 0;
            $startedAt = microtime(true);

            Log::channel('sync-adapters')->info("{$instance->slug} sync starting (UI)");

            $dryRun = $adapter->isPullDryRun();
            if ($dryRun) {
                Log::channel('sync-adapters')->info($instance->slug . ' sync [dry-run]: no assets will be created or updated');
            }

            // captureSyncRunLog's token return goes unused on the caller
            // side - the panel reads sync_run_log_last_token directly from
            // session rather than from a flashed value, so we don't need
            // the token here. The capture itself is still what we're after:
            // it isolates this run's log lines, caches them under the
            // persistent session key, and purges any prior run's cache.
            // --limit from the panel caps how many records this run
            // processes before stopping. Null = no limit. Non-positive
            // (<=0) treated as no-limit to prevent silent zero-record runs.
            $limitInput = $request->input('limit');
            $limit = ($limitInput !== null && $limitInput !== '' && (int) $limitInput > 0) ? (int) $limitInput : null;
            if ($limit !== null) {
                Log::channel('sync-adapters')->info($instance->slug . ' sync: limit set to ' . $limit . ', will stop after that many records');
            }

            $abortedRedirect = null;
            $this->captureSyncRunLog($instance->slug, 'pull', function () use ($adapter, $instance, $dryRun, $limit, &$seen, &$skipped, &$errors, &$abortedRedirect) {
                try {
                    Actionlog::withActionSource('sync:' . $instance->slug, function () use ($adapter, $instance, $dryRun, $limit, &$seen, &$skipped, &$errors) {
                        foreach ($adapter->pull() as $record) {
                            if ($limit !== null && ($seen + $skipped + $errors) >= $limit) {
                                Log::channel('sync-adapters')->info($instance->slug . ' sync: limit ' . $limit . ' reached, stopping early');
                                break;
                            }
                            try {
                                if ($dryRun) {
                                    // Mirror of the push dry-run shape: log the
                                    // normalized record and tally it as "seen"
                                    // without handing it to syncFromRecord.
                                    Log::channel('sync-adapters')->info(sprintf(
                                        '%s sync [dry-run]: would upsert record source=%s id=%s hostname=%s serial=%s',
                                        $instance->slug,
                                        $record->sourceKey,
                                        $record->sourceId,
                                        $record->hostname ?? '(none)',
                                        $record->hardwareSerial ?? '(none)',
                                    ));
                                    $seen++;

                                    continue;
                                }

                                $result = \App\SyncAdapters\SyncAdapter::syncFromRecord($record);
                                if ($result === null) {
                                    $skipped++;
                                } else {
                                    $seen++;
                                }
                            } catch (\Throwable $e) {
                                $errors++;
                                Log::channel('sync-adapters')->warning($instance->slug . ' sync: failed to upsert host ' . $record->sourceId . ': ' . $e->getMessage());
                            }
                        }
                    });
                } catch (\Throwable $e) {
                    Log::channel('sync-adapters')->warning(
                        $instance->slug . ' sync aborted: ' . SyncErrorFormatter::summary($e),
                        SyncErrorFormatter::logContext($e),
                    );

                    $failMessage = trans('admin/settings/sync_adapters.sync_failed', [
                        'summary' => SyncErrorFormatter::summary($e),
                    ]);
                    $instance->last_synced_at = now();
                    $instance->last_sync_result = $failMessage;
                    $instance->save();

                    $abortedRedirect = redirect()->route('settings.adapters.index', ['adapter' => $instance->slug])
                        ->with('error', $failMessage);
                }
            });

            if ($abortedRedirect !== null) {
                return $abortedRedirect;
            }

            $result = trans('admin/settings/sync_adapters.sync_complete', [
                'count' => $seen,
                'skipped' => $skipped,
                'errors' => $errors,
            ]);
            $instance->last_synced_at = now();
            $instance->last_sync_result = $result;
            $instance->save();

            $elapsed = number_format(microtime(true) - $startedAt, 1);
            Log::channel('sync-adapters')->info("{$instance->slug} sync complete: {$seen} record(s) processed, {$skipped} skipped, {$errors} error(s), elapsed {$elapsed}s");

            //   at least one seen, no errors -> success (green)
            //   at least one seen, some errors -> warning (orange, admin should check log)
            //   nothing seen at all, at least one error -> error (red)
            //   nothing seen AND no errors -> warning: vendor returned no rows,
            //     which may be legitimate (empty inventory) but is also the
            //     canary for a misconfigured pull that silently returned empty.
            //     Warning flags it for the admin to notice without being a
            //     hard error, so a genuinely empty vendor doesn't scream red.
            $flashType = match (true) {
                $seen > 0 && $errors === 0 => 'success',
                $seen > 0 && $errors > 0 => 'warning',
                $seen === 0 && $errors > 0 => 'error',
                default => 'warning',
            };

            // The download-run-log link reads sync_run_log_last_token from
            // the persistent session instead of a flash key, so a reload
            // inside the 10-min TTL keeps the link visible. captureSyncRunLog
            // put() the token in session already.
            return redirect()->route('settings.adapters.index', ['adapter' => $instance->slug])
                ->with($flashType, $result);
        } finally {
            $this->releaseOperationLock($instance->lockKey());
        }
    }

    /**
     * Push Snipe-IT-authoritative fields to the vendor for every asset
     * already linked to this adapter instance. Only meaningful for
     * adapters that implement PushableSyncAdapter. Iterates the instance's
     * asset_external_sources rows so we only touch assets the vendor
     * actually knows about. A fresh Snipe-IT asset that's never been
     * synced from this instance gets no push (we'd have no vendor id
     * to write against). Errors on individual assets are logged and
     * counted, so a single bad asset doesn't abort the whole run.
     */
    public function postAdapterPush(Request $request, \App\Models\SyncAdapterInstance $instance): RedirectResponse
    {
        $adapter = $instance->adapter();
        if ($adapter === null) {
            abort(404);
        }

        // Hidden mirror from the panel's push-dry-run checkbox so the
        // admin's live intent is honored for this one click without
        // needing to Save the config form first.
        if ($request->has('dry_run_override') && $adapter instanceof PushableSyncAdapter) {
            $adapter->withPushDryRunOverride($request->boolean('dry_run_override'));
        }

        // All three gate failures below are captured through
        // captureSyncRunLog, not just flashed, so the per-run download
        // + sync-adapters.log file carry a record of why Push Now did
        // nothing. The flash message alone is one-shot and vanishes on
        // reload or on the next action - admins need something they
        // can go back and read.
        if (!$adapter instanceof \App\SyncAdapters\PushableSyncAdapter) {
            $message = trans('admin/settings/sync_adapters.push_not_supported');
            $this->captureSyncRunLog($instance->slug, 'push', function () use ($instance, $message) {
                Log::channel('sync-adapters')->warning($instance->slug.' push aborted before run: push_not_supported - '.$message);
            });

            return redirect()->route('settings.adapters.index', ['adapter' => $instance->slug])
                ->with('error', $message);
        }

        if (! $adapter->isEnabled()) {
            $message = trans('admin/settings/sync_adapters.not_configured');
            $this->captureSyncRunLog($instance->slug, 'push', function () use ($instance, $message) {
                Log::channel('sync-adapters')->warning($instance->slug.' push aborted before run: not_configured - '.$message);
            });

            return redirect()->route('settings.adapters.index', ['adapter' => $instance->slug])
                ->with('error', $message);
        }

        // Nothing to push means the admin has no fields directed
        // 'push' AND no composed-notes template + target configured.
        // Iterating asset rows in that state would just silent-no-op
        // through every one and flash a misleading "Pushed N assets"
        // success. Fail fast with a message that names the actual gap.
        if (! $adapter->hasPushConfiguration()) {
            $message = trans('admin/settings/sync_adapters.push_nothing_configured');
            $this->captureSyncRunLog($instance->slug, 'push', function () use ($instance, $message) {
                Log::channel('sync-adapters')->warning($instance->slug.' push aborted before run: push_nothing_configured - '.$message);
            });

            return redirect()->route('settings.adapters.index', ['adapter' => $instance->slug])
                ->with('error', $message);
        }

        // Shared with the pull side via $instance->lockKey() so Pull Now
        // and Push Now on the same adapter are mutually exclusive.
        // Different adapters stay independent.
        if (!$this->acquireOperationLock($instance->lockKey())) {
            return redirect()->route('settings.adapters.index', ['adapter' => $instance->slug])
                ->with('error', $this->operationAlreadyRunningMessage('admin/settings/sync_adapters.sync_adapter_run'));
        }

        try {

            set_time_limit(0);

            $pushed = 0;
            $skipped = 0;
            $errors = 0;

            // --limit cap, same semantics as the pull side.
            $limitInput = $request->input('limit');
            $limit = ($limitInput !== null && $limitInput !== '' && (int) $limitInput > 0) ? (int) $limitInput : null;
            if ($limit !== null) {
                Log::channel('sync-adapters')->info($instance->slug . ' push: limit set to ' . $limit . ', will stop after that many assets');
            }

            $abortedRedirect = null;
            $this->captureSyncRunLog($instance->slug, 'push', function () use ($adapter, $instance, $limit, &$pushed, &$skipped, &$errors, &$abortedRedirect) {
                try {
                    \App\Models\AssetExternalSource::query()
                        ->where('source', $instance->slug)
                        ->with('asset')
                        ->chunkById(200, function ($rows) use ($adapter, $instance, $limit, &$pushed, &$skipped, &$errors) {
                            foreach ($rows as $row) {
                                if ($limit !== null && ($pushed + $skipped + $errors) >= $limit) {
                                    // Returning false from chunkById's
                                    // callback stops Eloquent paging after
                                    // this chunk.
                                    Log::channel('sync-adapters')->info($instance->slug . ' push: limit ' . $limit . ' reached, stopping early');

                                    return false;
                                }

                                $asset = $row->asset;
                                if ($asset === null) {
                                    continue;
                                }

                                try {
                                    if ($adapter->push($asset)) {
                                        $pushed++;
                                    } else {
                                        $skipped++;
                                    }
                                } catch (\Throwable $e) {
                                    $errors++;
                                    // Log the sanitized summary (short) plus,
                                    // for HTTP client exceptions, the raw
                                    // response body truncated to 2KB so vendor
                                    // errors like "missing required field
                                    // 'query'" or "gitops mode requires spec"
                                    // surface in the log even when the vendor's
                                    // JSON shape isn't one the summary
                                    // extractor recognizes.
                                    Log::channel('sync-adapters')->warning(sprintf(
                                        '%s push: asset %d failed: %s',
                                        $row->source,
                                        $asset->id,
                                        SyncErrorFormatter::summary($e),
                                    ), SyncErrorFormatter::logContext($e));
                                }
                            }
                        });
                } catch (\Throwable $e) {
                    Log::channel('sync-adapters')->warning(
                        sprintf('%s push aborted: %s', $instance->slug, SyncErrorFormatter::summary($e)),
                        SyncErrorFormatter::logContext($e),
                    );

                    $abortedRedirect = redirect()->route('settings.adapters.index', ['adapter' => $instance->slug])
                        ->with('error', trans('admin/settings/sync_adapters.push_failed', [
                            'summary' => SyncErrorFormatter::summary($e),
                        ]));
                }
            });

            if ($abortedRedirect !== null) {
                return $abortedRedirect;
            }

            //   at least one push, no failures -> success (green)
            //   at least one push, some failures -> warning (orange, admin should check log)
            //   no pushes, at least one failure -> error (red)
            //   no pushes AND no failures (every row skipped) -> error too, since the
            //     button did nothing meaningful and the admin should know
            //   nothing to iterate at all (no matching external_sources) -> warning
            // Message includes skipped count so admins see the full breakdown.
            $flashType = match (true) {
                $pushed > 0 && $errors === 0 => 'success',
                $pushed > 0 && $errors > 0 => 'warning',
                $errors > 0 => 'error',
                $skipped > 0 => 'error',
                default => 'warning',
            };

            // Same session-persistent download link affordance as the pull
            // path - captureSyncRunLog() has already put() the token under
            // sync_run_log_last_token, so the panel renders the link until
            // either (a) the next sync/push run purges this cache entry,
            // or (b) the download endpoint consumes it, or (c) the 10-min
            // TTL fires.
            return redirect()->route('settings.adapters.index', ['adapter' => $instance->slug])
                ->with($flashType, trans('admin/settings/sync_adapters.push_complete', [
                    'count' => $pushed,
                    'skipped' => $skipped,
                    'errors' => $errors,
                ]));
        } finally {
            $this->releaseOperationLock($instance->lockKey());
        }
    }

    /**
     * Pull the current list of sites from the vendor and return it as
     * JSON so the Site ID select on the adapter settings page can
     * populate inline, without the admin having to save and reload
     * first. Credentials come from the pending form (so a brand-new
     * adapter can list its sites before anything is persisted) and
     * fall back to the stored values when the request omits them.
     * Also writes the result to cached_sites so a page reload keeps
     * the list in place.
     */
    public function postAdapterRefreshSites(Request $request, \App\Models\SyncAdapterInstance $instance): JsonResponse
    {
        if (config('app.lock_passwords')) {
            return response()->json(['error' => trans('general.feature_disabled')], 403);
        }

        $adapter = $instance->adapter();
        if ($adapter === null) {
            abort(404);
        }

        if (! $adapter->supportsSiteScoping()) {
            abort(404);
        }

        try {
            $sites = $adapter->fetchSites([
                'url' => $request->input('url'),
                'api_key' => $request->input('api_key'),
            ]);
        } catch (\Throwable $e) {
            Log::channel('sync-adapters')->warning(
                sprintf('%s refresh-sites aborted: %s', $instance->slug, $e->getMessage()),
                ['exception' => $e],
            );

            return response()->json([
                'error' => trans('admin/settings/sync_adapters.refresh_sites_failed', [
                    'summary' => SyncErrorFormatter::summary($e),
                ]),
            ], 422);
        }

        \App\Models\SyncAdapterConfig::put($instance->id, 'cached_sites', json_encode($sites));

        return response()->json([
            'sites' => $sites,
            'message' => trans('admin/settings/sync_adapters.refresh_sites_ok', [
                'count' => count($sites),
                'label' => $adapter->vendorSiteLabel(),
            ]),
        ]);
    }

    /**
     * Refresh the cached list of vendor groups for an adapter
     * instance. Calls the adapter's fetchGroups() and persists the
     * result to the instance's config blob so the settings page can render
     * the mapping table without hitting the vendor on every page load.
     *
     * Only meaningful for adapters that opt into supportsGroupScoping().
     * (Some vendors gate groups by subscripton tier like Fleet.)
     * Failures land in the sync-adapters log channel and flash a
     * sanitized error to the admin (same as sync errors).
     */
    public function postAdapterRefreshGroups(\App\Models\SyncAdapterInstance $instance): RedirectResponse
    {
        if (config('app.lock_passwords')) {
            return redirect()->back()->with('error', trans('general.feature_disabled'));
        }

        $adapter = $instance->adapter();
        if ($adapter === null) {
            abort(404);
        }

        if (! $adapter->supportsGroupScoping()) {
            abort(404);
        }

        if (! $adapter->isEnabled()) {
            return redirect()->route('settings.adapters.index', ['adapter' => $instance->slug])
                ->with('error', trans('admin/settings/sync_adapters.not_configured'));
        }

        try {
            $groups = $adapter->fetchGroups();
        } catch (\Throwable $e) {
            Log::channel('sync-adapters')->warning(
                sprintf('%s refresh-groups aborted: %s', $instance->slug, $e->getMessage()),
                ['exception' => $e],
            );

            return redirect()->route('settings.adapters.index', ['adapter' => $instance->slug])
                ->with('error', trans('admin/settings/sync_adapters.refresh_groups_failed', [
                    'summary' => SyncErrorFormatter::summary($e),
                ]));
        }

        \App\Models\SyncAdapterConfig::put($instance->id, 'cached_groups', json_encode($groups));

        return redirect()->route('settings.adapters.index', ['adapter' => $instance->slug])
            ->with('success', trans('admin/settings/sync_adapters.refresh_groups_ok', [
                'count' => count($groups),
                'label' => $adapter->vendorGroupLabel(),
            ]));
    }

    /**
     * Refresh the cached list of vendor-defined custom fields for an
     * adapter that opts into supportsVendorCustomFields(). Parallel
     * to postAdapterRefreshGroups. Hits the vendor's custom-fields
     * listing endpoint, stores the normalized list under
     * the instance's config.vendor_custom_fields key, and redirects back
     * to the adapter settings page with a count.
     */
    public function postAdapterRefreshCustomFields(\App\Models\SyncAdapterInstance $instance): RedirectResponse
    {
        if (config('app.lock_passwords')) {
            return redirect()->back()->with('error', trans('general.feature_disabled'));
        }

        $adapter = $instance->adapter();
        if ($adapter === null) {
            abort(404);
        }

        if (! $adapter->supportsVendorCustomFields()) {
            abort(404);
        }

        if (! $adapter->isEnabled()) {
            return redirect()->route('settings.adapters.index', ['adapter' => $instance->slug])
                ->with('error', trans('admin/settings/sync_adapters.not_configured'));
        }

        try {
            $fields = $adapter->fetchVendorCustomFields();
        } catch (\Throwable $e) {
            Log::channel('sync-adapters')->warning(
                sprintf('%s refresh-custom-fields aborted: %s', $instance->slug, $e->getMessage()),
                ['exception' => $e],
            );

            return redirect()->route('settings.adapters.index', ['adapter' => $instance->slug])
                ->with('error', trans('admin/settings/sync_adapters.refresh_custom_fields_failed', [
                    'summary' => SyncErrorFormatter::summary($e),
                ]));
        }

        \App\Models\SyncAdapterConfig::put($instance->id, 'vendor_custom_fields', json_encode($fields));

        return redirect()->route('settings.adapters.index', ['adapter' => $instance->slug])
            ->with('success', trans('admin/settings/sync_adapters.refresh_custom_fields_ok', [
                'count' => count($fields),
            ]));
    }

    /**
     * Delete an adapter instance. Config rows go away with it. Existing
     * asset_external_sources rows for this instance's slug are left in
     * place (orphaned) so previously-synced assets keep their history.
     */
    public function deleteAdapterInstance(\App\Models\SyncAdapterInstance $instance): RedirectResponse
    {
        if (config('app.lock_passwords')) {
            return redirect()->back()->with('error', trans('general.feature_disabled'));
        }

        // Config lives on the instance row as a JSON column, so the
        // instance delete takes it with it. No separate purge needed.
        $instance->delete();

        return redirect()->route('settings.adapters.index')
            ->with('success', trans('admin/settings/sync_adapters.instance_deleted'));
    }

    /**
     * Download the captured log for a single sync-adapter run.
     * SyncAdapterController::captureSyncRunLog() wraps each Sync Now /
     * Push Now request with a Monolog TestHandler attached to the
     * sync-adapters channel, serializes the captured records to a text
     * blob, caches it under a random token, and flashes the token into
     * the session so the panel can render a one-time download link.
     * The cache entry is deleted after a successful download to make
     * the link single-use. Scoped to just the one run the admin
     * triggered, so prior-run noise doesn't bleed into the file.
     */
    /**
     * Preview the push payload for the first asset linked to this
     * adapter. AJAX diagnostic that builds the payload via a dry-run
     * push, captures the resulting log line, and returns it. Nothing
     * is sent to the vendor.
     */
    public function postPreviewPushPayload(\App\Models\SyncAdapterInstance $instance): JsonResponse
    {
        $adapter = $instance->adapter();
        if (!$adapter instanceof \App\SyncAdapters\PushableSyncAdapter) {
            return response()->json(['error' => trans('admin/settings/sync_adapters.push_not_supported')], 400);
        }
        if (! $adapter->isEnabled()) {
            return response()->json(['error' => trans('admin/settings/sync_adapters.not_configured')], 400);
        }

        $externalSource = \App\Models\AssetExternalSource::query()
            ->where('source', $instance->slug)
            ->with('asset')
            ->first();
        if ($externalSource === null || $externalSource->asset === null) {
            return response()->json(['error' => trans('admin/settings/sync_adapters.diagnostics_no_eligible_asset')], 400);
        }

        // Prefer a per-adapter peekPushPayload() that builds the HTTP
        // shape without executing anything - more useful than the
        // dry-run log capture because the admin sees the raw JSON
        // body as a nested object they can expand instead of a
        // single-line "would PATCH ... with {...}" string.
        $response = [
            'asset_id' => $externalSource->asset->id,
            'asset_tag' => $externalSource->asset->asset_tag,
        ];

        // Keep the method_exists check: peekPushPayload lives on
        // CustomHttpAdapter today, not on the PushableSyncAdapter
        // interface. Other adapters fall back to the dry-run log
        // capture below.
        if (method_exists($adapter, 'peekPushPayload')) {
            $captured = $this->captureAndReturn(function () use ($adapter, $externalSource, &$response) {
                $peeked = $adapter->peekPushPayload($externalSource->asset);
                if ($peeked !== null) {
                    $response['method'] = $peeked['method'];
                    $response['endpoint'] = $peeked['endpoint'];
                    $response['payload'] = $peeked['payload'];
                }
            });
            $response['output'] = $captured;

            return response()->json($response);
        }

        // Fallback for adapters that haven't implemented peek yet: run
        // push() with the dry-run override on and surface the captured
        // log, which includes the "would send" line for each adapter
        // that honors the dry-run flag.
        $adapter->withPushDryRunOverride(true);
        $response['output'] = $this->captureAndReturn(function () use ($adapter, $externalSource) {
            $adapter->push($externalSource->asset);
        });

        return response()->json($response);
    }

    /**
     * Push one real asset to the vendor and return the captured log
     * output (which includes the vendor response summary on
     * success, and the error details on failure).
     */
    public function postTest1Push(\App\Models\SyncAdapterInstance $instance): JsonResponse
    {
        $adapter = $instance->adapter();
        if (!$adapter instanceof \App\SyncAdapters\PushableSyncAdapter) {
            return response()->json(['error' => trans('admin/settings/sync_adapters.push_not_supported')], 400);
        }
        if (! $adapter->isEnabled()) {
            return response()->json(['error' => trans('admin/settings/sync_adapters.not_configured')], 400);
        }

        $externalSource = \App\Models\AssetExternalSource::query()
            ->where('source', $instance->slug)
            ->with('asset')
            ->first();
        if ($externalSource === null || $externalSource->asset === null) {
            return response()->json(['error' => trans('admin/settings/sync_adapters.diagnostics_no_eligible_asset')], 400);
        }

        // Explicit "not a dry run" - the admin picked Test 1 Push to
        // verify end-to-end against the real vendor.
        $adapter->withPushDryRunOverride(false);

        $captured = $this->captureAndReturn(function () use ($adapter, $externalSource) {
            $adapter->push($externalSource->asset);
        });

        return response()->json([
            'asset_id' => $externalSource->asset->id,
            'asset_tag' => $externalSource->asset->asset_tag,
            'output' => $captured,
        ]);
    }

    /**
     * Pull one record from the vendor, upsert as an asset, and return
     * the captured log output + the normalized HostInventoryRecord.
     */
    public function postTest1Pull(\App\Models\SyncAdapterInstance $instance): JsonResponse
    {
        $adapter = $instance->adapter();
        if ($adapter === null) {
            abort(404);
        }
        if (! $adapter->isEnabled()) {
            return response()->json(['error' => trans('admin/settings/sync_adapters.not_configured')], 400);
        }

        // Explicit "not a dry run" for the pull-side counterpart.
        $adapter->withPullDryRunOverride(false);

        $firstRecord = null;
        $assetId = null;

        $captured = $this->captureAndReturn(function () use ($adapter, &$firstRecord, &$assetId) {
            foreach ($adapter->pull() as $record) {
                $firstRecord = $record;
                $result = \App\SyncAdapters\SyncAdapter::syncFromRecord($record);
                if ($result !== null) {
                    $assetId = $result->id;
                }
                break;
            }
        });

        if ($firstRecord === null) {
            return response()->json([
                'output' => $captured.PHP_EOL.trans('admin/settings/sync_adapters.diagnostics_no_vendor_records'),
            ]);
        }

        return response()->json([
            'record' => [
                'source_key' => $firstRecord->sourceKey,
                'source_id' => $firstRecord->sourceId,
                'hostname' => $firstRecord->hostname,
                'serial' => $firstRecord->hardwareSerial,
                'model' => $firstRecord->hardwareModel,
                'manufacturer' => $firstRecord->manufacturer,
                'asset_tag' => $firstRecord->assetTag,
                'last_seen' => $firstRecord->lastSeen?->toIso8601String(),
            ],
            'upserted_asset_id' => $assetId,
            'output' => $captured,
        ]);
    }

    /**
     * Wrap $callable with a Monolog TestHandler attached to the
     * sync-adapters channel and return a plain-text block of the
     * captured log lines. Lighter than captureSyncRunLog(): no
     * caching, no download link - just text back to the AJAX caller
     * for inline display.
     */
    private function captureAndReturn(callable $callable): string
    {
        $channel = Log::channel('sync-adapters');
        if (! $channel instanceof \Illuminate\Log\Logger) {
            $callable();

            return '';
        }
        $logger = $channel->getLogger();
        if (! $logger instanceof \Monolog\Logger) {
            $callable();

            return '';
        }

        $handler = new TestHandler;
        $logger->pushHandler($handler);
        try {
            $callable();
        } catch (\Throwable $e) {
            Log::channel('sync-adapters')->error('diagnostic run threw: '.$e->getMessage());
        } finally {
            $logger->popHandler();
        }

        $lines = [];
        foreach ($handler->getRecords() as $record) {
            $lines[] = sprintf(
                '[%s] %s: %s',
                $record->datetime->format('H:i:s'),
                $record->level->getName(),
                $record->message,
            );
        }

        return implode(PHP_EOL, $lines);
    }

    public function downloadSyncRunLog(string $token): Response
    {
        $cacheKey = 'sync_run_log:'.$token;
        $payload = Cache::get($cacheKey);
        if (! is_array($payload) || ! isset($payload['body'], $payload['filename'])) {
            abort(404);
        }

        Cache::forget($cacheKey);

        // Clear the session pointer if it's still aimed at this token
        // so a reload after download doesn't render a dead link.
        if (session('sync_run_log_last_token') === $token) {
            session()->forget('sync_run_log_last_token');
        }

        return response($payload['body'], 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$payload['filename'].'"',
        ]);
    }

    /**
     * Run $callable with a Monolog TestHandler attached to the
     * sync-adapters channel so every log line emitted during this
     * request is captured, isolated from the cumulative file log.
     * Captured text + a slug-stamped filename go into the cache under
     * 'sync_run_log:{token}' with a 10-minute TTL, and the token is
     * put() into session('sync_run_log_last_token') so the panel's
     * download-run-log link can read it directly on any render -
     * reloads during the TTL keep the link live, and the next sync
     * run purges this run's cache entry before capturing its own.
     */
    protected function captureSyncRunLog(string $instanceSlug, string $kind, callable $callable): void
    {
        $channel = Log::channel('sync-adapters');

        // Log::channel() is typed as Psr\Log\LoggerInterface, which
        // doesn't expose getLogger(). At runtime it's an
        // Illuminate\Log\Logger wrapping the Monolog instance - but
        // phpstan can't see that without the instanceof narrow. Fall
        // through without capture if the facade ever returns something
        // else so we don't drop the run on a type surprise.
        if (! $channel instanceof \Illuminate\Log\Logger) {
            $callable();

            return;
        }

        $logger = $channel->getLogger();
        if (! $logger instanceof \Monolog\Logger) {
            $callable();

            return;
        }

        // Pull-and-forget the previous run's token pointer so we can
        // free its cache entry early instead of waiting out the TTL.
        // Only the latest run should be downloadable at any time -
        // the panel's download link gets overwritten on each new run,
        // so a stale cache entry would just orphan until expiry
        // without any UI reaching it.
        $previousToken = session()->pull('sync_run_log_last_token');
        if (is_string($previousToken) && $previousToken !== '') {
            Cache::forget('sync_run_log:'.$previousToken);
        }

        $handler = new TestHandler;
        $logger->pushHandler($handler);

        try {
            $callable();
        } finally {
            $logger->popHandler();
        }

        if ($handler->getRecords() === []) {
            return;
        }

        $lines = [];
        foreach ($handler->getRecords() as $record) {
            // Mirror the line shape of Laravel's default log formatter so
            // the downloaded file reads like a slice of the cumulative
            // sync-adapters.log file - timestamp, channel.level, message,
            // then any context as JSON.
            $datetime = $record->datetime->format('Y-m-d H:i:s');
            $channel = $record->channel;
            $level = $record->level->getName();
            $message = $record->message;
            $context = $record->context !== [] ? ' '.json_encode($record->context) : '';
            $lines[] = "[{$datetime}] {$channel}.{$level}: {$message}{$context}";
        }

        $token = Str::random(32);
        $filename = sprintf('%s-%s-%s.log', $instanceSlug, $kind, now()->format('Ymd-His'));
        Cache::put('sync_run_log:'.$token, [
            'body' => implode(PHP_EOL, $lines).PHP_EOL,
            'filename' => $filename,
        ], now()->addMinutes(10));

        // The panel's download link reads this persistent pointer on
        // every render, so a reload inside the TTL keeps the link
        // live. The next sync run pulls-and-forgets it to purge this
        // run's cache entry before capturing its own.
        session()->put('sync_run_log_last_token', $token);
    }
}
