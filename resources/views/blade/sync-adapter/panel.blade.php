{{-- "Last sync + Pull Now / Push Now buttons + CLI note" panel. Rendered as a sibling of the config-form shell
     rather than nested inside it because HTML disallows nested form
     elements and the sync trigger POSTs to a different route than
     the config save. Every auth-shape partial under
     resources/views/settings/adapters/ picks up the sync UI by
     rendering this component alongside the config-form shell. --}}
@props(['adapter'])

@php
    $slug = $adapter->name();
    $locked = config('app.lock_passwords') === true;
    // Per-instance mutex read: a Pull or Push in flight for this
    // adapter holds the key, so the buttons go disabled with a tooltip
    // until it clears. Server rejects duplicate triggers regardless.
    $runInProgress = \Illuminate\Support\Facades\Cache::store('file')->has($adapter->instance()->lockKey());
    $canSync = $adapter->isEnabled() && ! $locked && ! $runInProgress;

    // Download-link gating. We render the link only when BOTH the
    // persistent session pointer AND the cache entry it points at are
    // live - a reload after a cache-TTL expiry (10 min) or after the
    // download endpoint consumed the entry would otherwise show a dead
    // link.
    $runLogToken = session('sync_run_log_last_token');
    $runLogAvailable = is_string($runLogToken)
        && $runLogToken !== ''
        && \Illuminate\Support\Facades\Cache::has('sync_run_log:'.$runLogToken);
@endphp

<div class="sync-adapter-panel form-horizontal">
    {{-- content_class="col-md-8" overrides the default col-md-6 so
         this row's content column matches the width of the input
         rows above it in the config form. The download-run-log link
         appended at the end can then sit on the same line as the
         "Last synced" message without wrapping prematurely. --}}
    <x-form.static :label="trans('admin/settings/sync_adapters.last_synced_label')" content_class="col-md-8">
        @if ($adapter->lastSyncedAt())
            {{ $adapter->lastSyncedAt()->diffForHumans() }}. {{ $adapter->lastSyncResult() }}
        @else
            {{ trans('admin/settings/sync_adapters.never_synced') }}
        @endif
            {{-- Download-run-log link, scoped to the one run the admin just
                 triggered, cached for 10 min under a random token, deleted
                 on first download. The link persists across reloads for the
                 full TTL (not just the next render) by reading the persistent
                 session pointer rather than a flash key. --}}
            @if ($runLogAvailable)
                <a
                    href="{{ route('settings.adapters.sync_log.download', ['token' => $runLogToken]) }}"
                    download
                >
                    <i class="fa-solid fa-file-arrow-down" aria-hidden="true"></i>
                    {{ trans('admin/settings/sync_adapters.download_sync_run_log_button') }}
                </a>
            @endif
    </x-form.static>

    {{-- Dry-run checkboxes sit just above the Pull/Push buttons they
         govern. The `form` attribute binds each input back to the
         config-save form so toggle+Save persists. Pull Now / Push Now
         submit their OWN forms and pick up the current checkbox state
         via the js-sync-dry-run-mirror hidden input below. --}}
    <div class="form-group">
        <div class="col-md-8 col-md-offset-3">
            <label class="form-control">
                <x-input.checkbox
                    :name="$slug . '_pull_dry_run'"
                    :id="$slug . '_pull_dry_run'"
                    value="1"
                    :checked="$adapter->isPullDryRun()"
                    :disabled="$locked"
                    form="adapter-form-{{ $slug }}"
                />
                {{ trans('admin/settings/sync_adapters.pull_dry_run_label') }}
            </label>
            @if ($adapter instanceof \App\SyncAdapters\PushableSyncAdapter && $adapter->canPush())
                <label class="form-control">
                    <x-input.checkbox
                        :name="$slug . '_push_dry_run'"
                        :id="$slug . '_push_dry_run'"
                        value="1"
                        :checked="$adapter->isPushDryRun()"
                        :disabled="$locked"
                        form="adapter-form-{{ $slug }}"
                    />
                    {{ trans('admin/settings/sync_adapters.push_dry_run_label') }}
                </label>
            @endif
        </div>
    </div>

    {{-- Single shared Limit input scoped to this adapter panel. Both
         the Pull Now and Push Now form submits read its current value
         via the js-sync-limit-source handler in snipeit.js, which
         injects a hidden name="limit" input into whichever action
         form was submitted. Blank means "no limit". --}}
    <div class="form-group">
        <label for="{{ $slug }}_sync_limit" class="col-md-3 control-label">
            {{ trans('admin/settings/sync_adapters.run_limit_label') }}
        </label>
        <div class="col-md-9">
            <input
                type="number"
                id="{{ $slug }}_sync_limit"
                class="form-control js-sync-limit-source"
                data-adapter-slug="{{ $slug }}"
                min="1"
                step="1"
                placeholder="{{ trans('admin/settings/sync_adapters.run_limit_placeholder') }}"
                style="width: 120px; display: inline-block;"
            >
            <span class="help-block" style="display: inline-block; margin-left: 8px; margin-bottom: 0;">
                {{ trans('admin/settings/sync_adapters.run_limit_help') }}
            </span>
        </div>
    </div>

    <div class="form-group">
        <div class="col-md-8 col-md-offset-3">
            <form
                method="POST"
                action="{{ route('settings.adapters.sync', $slug) }}"
                class="js-sync-action-form js-sync-reads-limit"
                data-adapter-slug="{{ $slug }}"
                style="display: inline;"
            >
                @csrf
                <input
                    type="hidden"
                    name="dry_run_override"
                    value="{{ $adapter->isPullDryRun() ? '1' : '0' }}"
                    class="js-sync-dry-run-mirror"
                    data-mirrors="{{ $slug }}_pull_dry_run"
                >
                <span @if ($runInProgress) data-tooltip="true" title="{{ trans('general.operation_already_running', ['operation' => trans('admin/settings/sync_adapters.sync_adapter_run')]) }}" @endif>
                    <button
                        type="submit"
                        class="btn btn-primary js-sync-action-button"
                        data-dirty-guarded-by="adapter-form-{{ $slug }}"
                        @disabled(! $canSync)
                    >
                        <i class="fa-solid fa-cloud-arrow-down js-sync-action-icon" aria-hidden="true"></i>
                        <span class="js-sync-action-label">{{ trans('admin/settings/sync_adapters.pull_now') }}</span>
                    </button>
                </span>
            </form>

            @if ($adapter instanceof \App\SyncAdapters\PushableSyncAdapter && $adapter->canPush())
                <form
                    method="POST"
                    action="{{ route('settings.adapters.push', $slug) }}"
                    class="js-sync-action-form js-sync-reads-limit"
                    data-adapter-slug="{{ $slug }}"
                    style="display: inline; margin-left: 8px;"
                >
                    @csrf
                    <input
                        type="hidden"
                        name="dry_run_override"
                        value="{{ $adapter->isPushDryRun() ? '1' : '0' }}"
                        class="js-sync-dry-run-mirror"
                        data-mirrors="{{ $slug }}_push_dry_run"
                    >
                    <span @if ($runInProgress) data-tooltip="true" title="{{ trans('general.operation_already_running', ['operation' => trans('admin/settings/sync_adapters.sync_adapter_run')]) }}" @endif>
                        <button
                            type="submit"
                            class="btn btn-default js-sync-action-button"
                            data-dirty-guarded-by="adapter-form-{{ $slug }}"
                            @disabled(! $canSync)
                        >
                            <i class="fa-solid fa-cloud-arrow-up js-sync-action-icon" aria-hidden="true"></i>
                            <span class="js-sync-action-label">{{ trans('admin/settings/sync_adapters.push_now') }}</span>
                        </button>
                    </span>
                </form>
            @endif

            {{-- Test 1 Push / Test 1 Pull / Preview payload sit inside
                 the config-form fieldset under the composed-notes
                 section, since they're diagnostic affordances for
                 field-mapping work. --}}
        </div>
    </div>

    {{-- Full-width tip callout --}}
    <x-callout type="info" icon="tip" live="polite" style="margin-top: 10px;">
        @if ($adapter instanceof \App\SyncAdapters\PushableSyncAdapter && $adapter->canPush())
            {!! trans('admin/settings/sync_adapters.large_fleet_note', [
                'pull_command' => '<code>php artisan snipeit:pull-inventory '.e($slug).'</code>',
                'push_command' => '<code>php artisan snipeit:push-inventory '.e($slug).'</code>',
            ]) !!}
        @else
            {!! trans('admin/settings/sync_adapters.large_fleet_note_pull_only', [
                'pull_command' => '<code>php artisan snipeit:pull-inventory '.e($slug).'</code>',
            ]) !!}
        @endif
    </x-callout>
</div>
