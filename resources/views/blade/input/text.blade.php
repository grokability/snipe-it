@props([
'input_group_addon' => null,
'input_icon' => null,
'required' => false,
'item' => null,
])
<!-- input-text blade component -->
@if ($input_group_addon)
        <div class="input-group">
@endif
    <input
        {{ $attributes->merge(['class' => 'form-control']) }}
        @required($required)
    />

@if ($input_group_addon)
    <span class="input-group-addon">
      <x-icon :type="$input_icon" />
    </span>
</div>
@endif

