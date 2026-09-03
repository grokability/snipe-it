{{-- "Last sync + Sync Now button + CLI note" panel. Rendered as a sibling of the config-form shell
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
            {{-- Inline form so the Sync Now click POSTs to the sync route
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
                    <x-icon type="sync"/>
                    {{ trans('admin/settings/general.sync_adapter_sync_now') }}
                </button>
            </form>

            <p class="help-block" style="margin-top: 10px;">
                {!! trans('admin/settings/general.sync_adapter_large_fleet_note', [
                    'command' => '<code>php artisan snipeit:sync-inventory '.e($slug).'</code>',
                ]) !!}
            </p>
        </div>
    </div>
</div>
