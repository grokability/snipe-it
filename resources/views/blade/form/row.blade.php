<!-- form-row blade component -->
@props([
    'name' => null,
    'type' => 'text',
    'item' => null,
    'info_tooltip_text' => null,
    'help_text' => null,
    'help_html' => null,
    'help_icon' => null,
    'label' => null,
    'label_class' => 'col-md-3',
    'input_div_class' => 'col-md-7',
    'input_icon' => null,
    'input_group_addon' => null,
    'maxlength' => null,
    'min' => null,
    'max' => null,
    'rows' => null,
    'placeholder' => null,
    'default' => null,
    'end_date' => null,
    'default_now' => true,
    'side_by_side' => false,
    'stacked' => false,
    'id' => null,
    'required' => null,
])

{{-- Inherit `stacked` from an ancestor <x-modals stacked> so callers don't
     repeat the flag on every row inside a vertical modal. @aware
     unconditionally overwrites $stacked, so we snapshot the row's explicit
     value first and OR the two after aware runs. Row-explicit stacked=true
     still wins if the modal is horizontal. --}}
@php
    $callerStacked = $stacked;
@endphp
@aware(['stacked' => false])
@php
    $stacked = $callerStacked || $stacked;
    $errors_class = $errors->has($name) ? ' has-error' : '';
    $input_id = $id ?? $name;
@endphp

<div {{ $attributes->merge(['class' => 'form-group'.$errors_class]) }}>

    {{-- x-slot:labelHtml overrides the label prop when you need custom markup
        (JS-driven data-label-* attributes, an inline icon, etc).
        Otherwise the label prop renders through x-form.label (horizontal) or a plain <label>
        (stacked, usually for modals). --}}
    @isset($labelHtml)
        {{ $labelHtml }}
    @elseif (isset($label))
        @if ($stacked)
            <label for="{{ $input_id }}">{{ $label }}</label>
        @else
            <x-form.label :for="$input_id" class="{{ $label_class }}">{{ $label }}</x-form.label>
        @endif
    @endif

    @if (! $stacked)
        <div class="{{ $input_div_class }}">
    @endif
        @isset($input)
            <x-form.field
                :name="$name"
                :type="$type"
                :item="$item"
                :id="$input_id"
                :required="$required"
                :default="$default"
                :input_icon="$input_icon"
                :input_group_addon="$input_group_addon"
                :maxlength="$maxlength"
                :min="$min"
                :max="$max"
                :rows="$rows"
                :placeholder="$placeholder"
                :help_text="$help_text"
                :end_date="$end_date"
                :default_now="$default_now"
                :side_by_side="$side_by_side"
            >
                <x-slot:input>{{ $input }}</x-slot:input>
            </x-form.field>
        @else
            <x-form.field
                :name="$name"
                :type="$type"
                :item="$item"
                :id="$input_id"
                :required="$required"
                :default="$default"
                :input_icon="$input_icon"
                :input_group_addon="$input_group_addon"
                :maxlength="$maxlength"
                :min="$min"
                :max="$max"
                :rows="$rows"
                :placeholder="$placeholder"
                :help_text="$help_text"
                :end_date="$end_date"
                :default_now="$default_now"
                :side_by_side="$side_by_side"
            />
        @endisset
    @if (! $stacked)
        </div>
    @endif

    {{-- Optional col-md-1 sibling of the input column for a small action
         button (e.g. the "new" button next to a user select, or the wand
         generator next to the password field). Callers pass
         <x-slot:after_input>...</x-slot:after_input>. --}}
    @isset($after_input)
        <div @class(['col-md-1 col-sm-1 text-left' => ! $stacked])>
            {{ $after_input }}
        </div>
    @endisset

    @if ($info_tooltip_text)
        <div @class(['col-md-1 text-left' => ! $stacked]) style="padding-left:0; margin-top: 5px;">
            <x-form.tooltip>
                {{ $info_tooltip_text }}
            </x-form.tooltip>
        </div>
    @endif

    @if (! $stacked)
        {{-- Force the help block onto a new grid row regardless of how
             narrow the input column is. Without this, callers using
             narrow input_div_class values (e.g. col-lg-3 for a number +
             days addon) hit a case where label + input + help offset +
             help width sum to exactly 12, and the help renders beside
             the input instead of underneath it. --}}
        <div class="clearfix"></div>
    @endif

    {{-- Error + help block width matches the input's width so long
         lines wrap under the input rather than spilling past into
         the empty column beyond it. Uses $input_div_class (default
         col-md-7) plus the label's col-md-offset-3 so it aligns
         under the input on horizontal-form rows. Stacked layouts
         (mobile / modal) drop the offset because they're single-
         column. --}}
    <div @class([$input_div_class.' col-md-offset-3' => ! $stacked])>
        <x-form.error :name="$name" />

        @if ($help_text)
            {{-- $help_text is already HTML-entity-escaped by Blade's
                 attribute-binding sanitize pass. Using {!! !!} outputs
                 those entities as-is so the browser renders them as real
                 characters. A plain {{ }} would escape a second time. --}}
            <x-form.help :name="$input_id" :icon="$help_icon">{!! $help_text !!}</x-form.help>
        @elseif ($help_html)
            {{-- Raw HTML help. Caller has opted in, rendered unescaped
                 straight to the <p>. To pass a trans() string with HTML
                 in it, use the static-attribute form
                 help_html="{!! trans('...') !!}". The dynamic-binding
                 form runs the value through
                 BladeCompiler::sanitizeComponentAttribute() and turns
                 the <a> tags into &lt;a&gt; entities. --}}
            <p class="help-block" id="{{ $input_id }}-help">
                @if ($help_icon)
                    <x-icon :type="$help_icon" />
                @endif
                {!! $help_html !!}
            </p>
        @endif
    </div>

</div>
