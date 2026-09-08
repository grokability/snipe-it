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
    <x-form.static :label="trans('admin/settings/general.sync_adapter_last_synced_label')">
        @if ($adapter->lastSyncedAt())
            {{ $adapter->lastSyncedAt()->diffForHumans() }}. {{ $adapter->lastSyncResult() }}
        @else
            {{ trans('admin/settings/general.sync_adapter_never_synced') }}
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
                style="display: inline;"
            >
                @csrf
                <button
                    type="submit"
                    class="btn btn-primary"
                    @disabled(! $canSync)
                >
                    <i class="fa-solid fa-cloud-arrow-down" aria-hidden="true"></i>
                    {{ trans('admin/settings/general.sync_adapter_pull_now') }}
                </button>
            </form>

            {{-- Push Now button only renders for adapters that implement
                 PushableAdapter. Same sibling-form pattern as Pull Now
                 above. Pushes every asset already linked to this
                 instance via asset_external_sources. --}}
            @if ($adapter instanceof \App\SyncAdapters\PushableAdapter)
                <form
                    method="POST"
                    action="{{ route('settings.adapters.push', $slug) }}"
                    style="display: inline; margin-left: 8px;"
                >
                    @csrf
                    <button
                        type="submit"
                        class="btn btn-default"
                        @disabled(! $canSync)
                    >
                        <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
                        {{ trans('admin/settings/general.sync_adapter_push_now') }}
                    </button>
                </form>
            @endif

            <p class="help-block" style="margin-top: 10px;">
                {{-- Show both commands when this adapter supports push
                     (Kandji, Jamf, Mosyle, WS1, Ninja, Intune). Pull-only
                     adapters get the shorter single-command variant so
                     admins don't see a push-inventory hint they can't act
                     on. --}}
                @if ($adapter instanceof \App\SyncAdapters\PushableAdapter && $adapter->canPush())
                    {!! trans('admin/settings/general.sync_adapter_large_fleet_note', [
                        'pull_command' => '<code>php artisan snipeit:pull-inventory '.e($slug).'</code>',
                        'push_command' => '<code>php artisan snipeit:push-inventory '.e($slug).'</code>',
                    ]) !!}
                @else
                    {!! trans('admin/settings/general.sync_adapter_large_fleet_note_pull_only', [
                        'pull_command' => '<code>php artisan snipeit:pull-inventory '.e($slug).'</code>',
                    ]) !!}
                @endif
            </p>
        </div>
    </div>
</div>
