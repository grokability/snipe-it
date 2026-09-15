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
    $canSync = $adapter->isEnabled() && ! $locked;
@endphp

<div class="sync-adapter-panel form-horizontal">
    <x-form.static :label="trans('admin/settings/sync_adapters.last_synced_label')">
        @if ($adapter->lastSyncedAt())
            {{ $adapter->lastSyncedAt()->diffForHumans() }}. {{ $adapter->lastSyncResult() }}
        @else
            {{ trans('admin/settings/sync_adapters.never_synced') }}
        @endif
    </x-form.static>

    <div class="form-group">
        <div class="col-md-8 col-md-offset-3">
            {{-- Inline form so the Pull Now click POSTs to the sync route
                 without submitting the config form (which posts to save).
                 Sibling of the config form since HTML disallows nested
                 form elements. --}}
            <form
                method="POST"
                action="{{ route('settings.adapters.sync', $slug) }}"
                class="js-sync-action-form"
                style="display: inline;"
            >
                @csrf
                <button
                    type="submit"
                    class="btn btn-primary js-sync-action-button"
                    data-dirty-guarded-by="adapter-form-{{ $slug }}"
                    @disabled(! $canSync)
                >
                    <i class="fa-solid fa-cloud-arrow-down js-sync-action-icon" aria-hidden="true"></i>
                    <span class="js-sync-action-label">{{ trans('admin/settings/sync_adapters.pull_now') }}</span>
                </button>
            </form>

            {{-- Push Now button renders for adapters that implement
                 PushableAdapter AND report canPush() true at runtime
                 (Fleet Free returns false because its label writes are
                 Premium-only. CustomHttpAdapter returns false when the
                 admin hasn't set a push endpoint yet). --}}
            @if ($adapter instanceof \App\SyncAdapters\PushableAdapter && $adapter->canPush())
                <form
                    method="POST"
                    action="{{ route('settings.adapters.push', $slug) }}"
                    class="js-sync-action-form"
                    style="display: inline; margin-left: 8px;"
                >
                    @csrf
                    <button
                        type="submit"
                        class="btn btn-default js-sync-action-button"
                        data-dirty-guarded-by="adapter-form-{{ $slug }}"
                        @disabled(! $canSync)
                    >
                        <i class="fa-solid fa-cloud-arrow-up js-sync-action-icon" aria-hidden="true"></i>
                        <span class="js-sync-action-label">{{ trans('admin/settings/sync_adapters.push_now') }}</span>
                    </button>
                </form>
            @endif

            {{-- The submit handler that swaps the icon for a spinner
                 and disables sibling sync-action buttons lives in
                 resources/assets/js/snipeit.js. Fully driven by the
                 .js-sync-action-form / .js-sync-action-button classes
                 above so nothing on this partial needs inline JS. --}}

            <p class="help-block" style="margin-top: 10px;">
                {{-- Show both commands when this adapter supports push
                     (Kandji, Jamf, Mosyle, WS1, Ninja, Intune). Pull-only
                     adapters get the shorter single-command variant so
                     admins don't see a push-inventory hint they can't act
                     on. --}}
                @if ($adapter instanceof \App\SyncAdapters\PushableAdapter && $adapter->canPush())
                    {!! trans('admin/settings/sync_adapters.large_fleet_note', [
                        'pull_command' => '<code>php artisan snipeit:pull-inventory '.e($slug).'</code>',
                        'push_command' => '<code>php artisan snipeit:push-inventory '.e($slug).'</code>',
                    ]) !!}
                @else
                    {!! trans('admin/settings/sync_adapters.large_fleet_note_pull_only', [
                        'pull_command' => '<code>php artisan snipeit:pull-inventory '.e($slug).'</code>',
                    ]) !!}
                @endif
            </p>
        </div>
    </div>
</div>
