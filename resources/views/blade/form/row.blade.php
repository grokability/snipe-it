<!-- form-row blade component -->
@props([
    'name' => null,
    'type' => 'text',
    'item' => null,
    'info_tooltip_text' => null,
    'help_text' => null,
    // Opt-in raw-HTML help. Rendered UNESCAPED — only pass developer-authored
    // strings (translation strings with links, etc.). Never pass anything
    // that could contain user input without escaping it yourself first.
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
    // Datepicker / datetimepicker widget knobs. Only consumed when
    // type="datepicker" or type="datetimepicker".
    'end_date' => null,
    'default_now' => true,
    'side_by_side' => false,
])

@php
    // Bootstrap 3 error styling: .form-group.has-error turns the label
    // red and adds a red border to the input. Computed here instead of
    // as a @props default because $name isn't in scope during Blade's
    // first extractPropNames() pass. Sibling components x-form.checkbox-row
    // and x-form.radio-row do the same thing.
    $errors_class = $errors->has($name) ? ' has-error' : '';
@endphp

<div {{ $attributes->merge(['class' => 'form-group'. $errors_class]) }}>

    <!-- form label -->
    @if (isset($label))
        <x-form.label :for="$name" class="{{ $label_class }}">{{ $label }}</x-form.label>
    @endif


    @php
        $blade_type = in_array($type, ['text', 'email', 'url', 'tel', 'number', 'password']) ? 'text' : $type;

        // Maxlength precedence:
        //   1. Explicit :maxlength="..." from the caller (always wins).
        //   2. Model rules — Helper::fieldMaxLength reads `max:N` from the
        //      model's validation rules so the browser cap matches the DB
        //      column width automatically. Applied to all types except
        //      textarea/number (textareas back TEXT columns with no length
        //      limit; browsers ignore maxlength on type="number").
        //   3. Fallback 191 for text-family types (matches the vast majority
        //      of varchar(191) columns in this schema).
        $effective_maxlength = $maxlength
            ?? ($type !== 'textarea' && $type !== 'number' ? Helper::fieldMaxLength($item, $name) : null)
            ?? (in_array($type, ['text', 'email', 'url', 'tel', 'password']) ? 191 : null);
    @endphp

        <div class="{{ $input_div_class }}">
            {{-- You can pass an <x-slot:input>...</x-slot:input> when the
                 field needs custom markup (e.g. an input plus a select side
                 by side, or a widget the input.* components don't cover).
                 The wrapping label + error + help + aria still come from
                 <x-form.row>, so only the input area is hand-authored. --}}
            @isset($input)
                {{ $input }}
            @elseif ($blade_type === 'colorpicker')
                {{-- Widget-shaped inputs (colorpicker, datepicker, etc.) don't share
                     the text-family prop shape, so dispatch them explicitly with
                     only the props they accept. Avoids leaking type/input_icon/
                     maxlength/etc. as bogus HTML attrs on the widget's outer div. --}}
                <x-input.colorpicker
                    :name="$name"
                    :id="$name"
                    :item="$item"
                    :default="$default"
                />
            @elseif ($blade_type === 'datepicker')
                {{-- $item->{$name} may be a Carbon (models that cast the
                     column to `date`, e.g. License::purchase_date) or a
                     plain string. Normalize to Y-m-d via strtotime so the
                     datepicker JS can parse it. Without this, Carbon
                     stringifies as "Y-m-d H:i:s", the picker fails to
                     parse it, renders blank, and submit wipes the field. --}}
                <x-input.datepicker
                    :name="$name"
                    :id="$name"
                    :value="old($name, $item?->{$name} ? date('Y-m-d', strtotime((string) $item->{$name})) : $default)"
                    :required="Helper::checkIfRequired($item, $name)"
                    :placeholder="$placeholder"
                    :end_date="$end_date"
                />
            @elseif ($blade_type === 'datetimepicker')
                <x-input.datetimepicker
                    :name="$name"
                    :id="$name"
                    :value="old($name, $item?->{$name}?->format('Y-m-d H:i:s') ?? $item?->{$name} ?? $default)"
                    :required="Helper::checkIfRequired($item, $name)"
                    :placeholder="$placeholder"
                    :default_now="$default_now"
                    :side_by_side="$side_by_side"
                />
            @else
                <x-dynamic-component
                    :$name
                    :$type
                    :aria-label="$name"
                    :aria-describedby="$help_text ? $name.'-help' : null"
                    :component="'input.'.$blade_type"
                    :id="$name"
                    :required="Helper::checkIfRequired($item, $name)"
                    :value="old($name, $item->{$name})"
                    :input_icon="$input_icon"
                    :input_group_addon="$input_group_addon"
                    :maxlength="$effective_maxlength"
                    :min="$min"
                    :max="$max"
                    :rows="$rows"
                    :placeholder="$placeholder"
                />
            @endisset
        </div>

    {{-- Optional col-md-1 sibling of the input column for a small
         action button (e.g. the "new" button next to a user select,
         or the wand generator next to the password field). Callers
         pass <x-slot:after_input>...</x-slot:after_input>. Matches
         the manager-picker layout in x-input.user-select. --}}
    @isset($after_input)
        <div class="col-md-1 col-sm-1 text-left">
            {{ $after_input }}
        </div>
    @endisset

    @if ($info_tooltip_text)
        <!-- Info Tooltip -->
        <div class="col-md-1 text-left" style="padding-left:0; margin-top: 5px;">
            <x-form.tooltip>
                {{ $info_tooltip_text }}
            </x-form.tooltip>
        </div>
    @endif


    {{-- Error + help wrapper. Width tracks $input_div_class so the help
         line sits directly under the input, not overhanging its right
         edge. Offset stays col-md-offset-3 to align under the input
         column (which sits after the col-md-3 label). Callers that
         want a wider help area can override input_div_class. --}}
    <div class="{{ $input_div_class }} col-md-offset-3">
        <x-form.error :name="$name" />

        @if ($help_text)
            <x-form.help :name="$name" :icon="$help_icon">{{ $help_text }}</x-form.help>
        @elseif ($help_html)
            {{-- Raw HTML help — the caller has opted in, we render unescaped
                 straight to the <p>. See the help_html prop docs above. Note:
                 to pass a trans() string with HTML in it, use the static-
                 attribute form  help_html="{!! trans('...') !!}"  — the
                 dynamic-binding form  :help_html="trans('...')"  runs the
                 value through BladeCompiler::sanitizeComponentAttribute()
                 and turns your <a> tags into &lt;a&gt; entities. --}}
            <p class="help-block" id="{{ $name }}-help">
                @if ($help_icon)
                    <x-icon :type="$help_icon" />
                @endif
                {!! $help_html !!}
            </p>
        @endif
    </div>

</div>
