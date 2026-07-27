@props([
'input_group_addon' => null,
'input_icon' => null,
'required' => false,
'item' => null,
'ignoreAutofill' => false,
])
<!-- input-text blade component -->
@if ($input_group_addon)
        <div class="input-group">
@endif
    <input
        {{ $attributes->merge(['class' => 'form-control']) }}
        @if ($ignoreAutofill)
            autocomplete="off"
            data-1p-ignore="true"
            data-lpignore="true"
            data-bwignore="true"
            data-form-type="other"
        @endif
        @required($required)
    />

@if ($input_group_addon)
    <span class="input-group-addon">
      <x-icon :type="$input_icon" class="fa-fw" />
    </span>
</div>
@endif

