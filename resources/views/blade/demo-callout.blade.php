{{-- Full-width "this action is locked in demo mode" callout for
     destructive-action confirm screens (delete, merge, bulk edit,
     etc.). Bigger visual weight than <x-demo-lock> so it can't get
     missed above a Submit button and users don't misread the disabled
     state.

     Self-gates on the editableOnDemo policy so callers don't need
     their own @if wrapper. Renders nothing when the app is not in
     demo mode.

     Slot overrides the default text (trans('general.feature_disabled')).
     Uses warning styling + warning-triangle icon + role="status" for
     a consistent visual across every confirm-action page. --}}
@if (! Gate::allows('editableOnDemo'))
    <x-callout type="warning" icon="warning" role="status" {{ $attributes }}>
        {{ $slot->isEmpty() ? trans('general.feature_disabled') : $slot }}
    </x-callout>
@endif
