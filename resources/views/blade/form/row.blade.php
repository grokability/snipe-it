<!-- form-row blade component -->
@props([
    'name' => null,
    'type' => 'text',
    'item' => null,
    'info_tooltip_text' => null,
    'help_text' => null,
    'label' => null,
    'input_div_class' => 'col-md-8',
    'errors_class' => $errors->has('support_url') ? ' has-error' : '',
    'input_icon' => null,
    'input_group_addon' => null,
    'rows' => null,
    'placeholder' => null,
])

<div {{ $attributes->merge(['class' => 'form-group'. $errors_class]) }}>

    <!-- form label -->
    @if (isset($label))
        <x-form.label  :for="$name" class="{{ $label_class ?? 'col-md-3' }}">{{ $label }}</x-form.label>
    @endif


    @php
        $blade_type = in_array($type, ['text', 'email', 'url', 'tel', 'number', 'password']) ? 'text' : $type;
    @endphp

        <div class="{{ $input_div_class }}">
            <x-dynamic-component
                    :$name
                    :$type
                    :aria-label="$name"
                    :component="'input.'.$blade_type"
                    :id="$name"
                    :required="Helper::checkIfRequired($item, $name)"
                    :value="old($name, $item->{$name})"
                    :input_icon="$input_icon"
                    :input_group_addon="$input_group_addon"
                    :rows="$rows"
                    :placeholder="$placeholder"

            />
        </div>

    @if ($info_tooltip_text)
        <!-- Info Tooltip -->
        <div class="col-md-1 text-left" style="padding-left:0; margin-top: 5px;">
            <x-form.tooltip>
                {{ $info_tooltip_text }}
            </x-form.tooltip>
        </div>
    @endif


    @error($name)
    <div class="col-md-8 col-md-offset-3">
        <span class="alert-msg" aria-hidden="true">
            <x-icon type="x" />
            {{ $message }}
        </span>
    </div>
    @enderror

    @if ($help_text)
        <!-- Help Text -->
        <div class="col-md-8 col-md-offset-3">
            <p class="help-block">
                {!! $help_text !!}
            </p>
        </div>
    @endif



</div>