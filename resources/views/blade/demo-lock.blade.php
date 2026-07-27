{{-- Inline "this is locked because the app is in demo mode" notice.
     Self-gates on the editableOnDemo policy so callers don't need to
     wrap their own @if. Two calling shapes:

       <x-demo-lock :item="$user" />
         Per-form-field use. Additionally requires $item->id to be
         truthy - keeps the notice off "create new" forms where nothing
         is saved yet so there's nothing to lock down.

       <x-demo-lock />
         Feature-level use (e.g. importer side panel). No item context
         is needed; the notice renders whenever demo mode is on.

     Slot overrides the default text (trans('admin/users/table.lock_passwords')).

     Deliberately does NOT carry the .help-block class that x-form.help
     applies: the snipeValidatorOptions.highlight callback in
     layouts/default.blade.php calls $group.find('.help-block').remove()
     on any form-group in error state, which would strip this notice on
     validation. The .demo-lock-notice class is here so a future style
     override can target it without matching every .text-warning use. --}}
@props([
    'item' => null,
])

@if (! Gate::allows('editableOnDemo') && ($item === null || $item->id))
    <div {{ $attributes->merge(['class' => 'demo-lock-notice', 'role' => 'note']) }}>
        <x-icon type="locked"/>
        {{ $slot->isEmpty() ? trans('admin/users/table.lock_passwords') : $slot }}
    </div>
@endif
