{{-- help_html: opt-in raw-HTML help. Rendered UNESCAPED, only pass
     developer-authored strings (translation strings with anchors, <code>,
     <br>, etc.). Never pass anything that could contain user input without
     escaping it first. See x-form.row for the same prop with the same
     semantics.

     section_label: optional left-column section header for single-checkbox
     rows that need to sit under a labeled section (e.g. "SAML Integration"
     or "AD"), where the inner-label ("Enable SAML") is separate from the
     section framing. Rendered as a plain <div>, not a <label>, so screen
     readers get one accessible name from the inner form-control label,
     not two competing ones. Ignored in multi mode, which uses $label for
     the left column.

     checked: explicit checked-state override. When passed (non-null), it
     wins over the old-input / $item fallback below. Needed for contexts
     (like Livewire) where the source of truth is neither the session's old
     input nor a bound Eloquent model but a live component property.

     input_div_class default: skip the col-md-offset-3 when there's a
     left-hand column of any kind (multi with $label, or single with
     $section_label). Keeping the grid classes centralized here means a
     future Bootstrap / AdminLTE upgrade only has to touch this file, not
     every callsite. Note: Blade evaluates @props defaults twice (once via
     extractPropNames before caller attrs are bound, once when applying
     defaults after). The `?? null` guards against "undefined variable" on
     the first pass, isset() is inherently safe against undefined vars,
     is_array() is not. --}}
@props([
    'name' => null,
    'item' => null,
    'label' => null,
    'options' => null,
    'selected' => null,
    'value' => '1',
    'required' => null,
    'disabled' => false,
    'help_text' => null,
    'help_html' => null,
    'help_icon' => null,
    'help_class' => null,
    'info_tooltip_text' => null,
    'section_label' => null,
    'checked' => null,
    'input_div_class' => ((is_array($options ?? null) && isset($label)) || isset($section_label)) ? 'col-md-8' : 'col-md-8 col-md-offset-3',
])

@php
    // Multi-checkbox mode kicks in when the caller supplies an $options map;
    // otherwise this renders as a single boolean checkbox.
    $is_multi = is_array($options);

    // Old-input aware check-state. On a fresh render, session()->hasOldInput()
    // is false, so we fall back to the model (or supplied :selected). On a
    // validation-failure redisplay, hasOldInput() is true and we trust the
    // (possibly missing) old value: an unchecked box comes back correctly
    // unchecked instead of falling through to the stale $item->{$name}.
    $is_redisplay = session()->hasOldInput();

    if (! $is_multi) {
        // Explicit :checked wins over any implicit source. Otherwise fall
        // back to old input on redisplay, then to the bound $item attribute.
        $single_checked = $checked !== null
            ? (bool) $checked
            : ($is_redisplay
                ? (bool) old($name)
                : (bool) ($item?->{$name} ?? false));

        // Helper::checkIfRequired dereferences $item statically via $item::rules(),
        // so it needs a real class/object. Fall back to false when no model was
        // supplied (transient forms have no persistent model).
        $really_required = $required ?? ($item ? Helper::checkIfRequired($item, $name) : false);
    } else {
        // For multi mode, callers can pass :selected as an array of currently-
        // selected values, a comma-joined string (common when the model stores
        // it that way, e.g. Setting's modellist_displays), or a callable
        // (value): bool for per-value predicates. When :selected is omitted
        // the same fallback is applied to $item->{$name}.
        if ($selected === null) {
            $selected = $item?->{$name};
        }

        if (is_string($selected)) {
            $selected = $selected === '' ? [] : array_map('trim', explode(',', $selected));
        }

        $old_values = is_array(old($name)) ? old($name) : [];

        $is_checked = function ($value) use ($is_redisplay, $old_values, $selected) {
            if ($is_redisplay) {
                return in_array($value, $old_values);
            }
            if (is_callable($selected)) {
                return (bool) $selected($value);
            }
            return in_array($value, is_array($selected) ? $selected : []);
        };
    }

    $errors_class = $errors->has($name) ? ' has-error' : '';
@endphp

<div class="form-group{{ $errors_class }}">

    @if (! $is_multi)

        {{-- Single checkbox: optional section-label column on the left,
             then a form-control label wrapping the input. $attributes are
             forwarded to the input itself (not the wrapper) so
             `wire:model.live=...` from Livewire callers binds the actual
             checkbox and not an inert div. Wrapper-level class/style props
             would be silently dropped here; no current caller uses them. --}}
        @if ($section_label)
            <div class="col-md-3 control-label">
                <strong>{{ $section_label }}</strong>
            </div>
        @endif
        <div class="{{ $input_div_class }}">
            <label class="form-control">
                <x-input.checkbox
                    :name="$name"
                    :id="$name"
                    :value="$value"
                    :checked="$single_checked"
                    :required="$really_required"
                    :disabled="$disabled"
                    :aria-label="$name"
                    :aria-describedby="$help_text ? $name.'-help' : null"
                    {{ $attributes }}
                />
                {{ $label }}
            </label>
        </div>

    @else

        {{-- Multi: standard left-hand label + a stack of wrapped checkboxes on the right. --}}
        @if (isset($label))
            <x-form.label :for="$name" class="col-md-3">{{ $label }}</x-form.label>
        @endif

        <div class="{{ $input_div_class }}">
            @foreach ($options as $option_value => $option_label)
                <label class="form-control">
                    <x-input.checkbox
                        :name="$name.'[]'"
                        :value="$option_value"
                        :checked="$is_checked($option_value)"
                        :disabled="$disabled"
                        :aria-label="$name"
                        :aria-describedby="$help_text ? $name.'-help' : null"
                    />
                    {{ $option_label }}
                </label>
            @endforeach
        </div>

    @endif

    @if ($info_tooltip_text)
        <div class="col-md-1 text-left" style="padding-left:0; margin-top: 5px;">
            <x-form.tooltip>
                {{ $info_tooltip_text }}
            </x-form.tooltip>
        </div>
    @endif

    @if ($errors->has($name))
        <div class="col-md-8 col-md-offset-3">
            <x-form.error :name="$name" />
        </div>
    @endif

    @if ($help_text)
        <div class="col-md-8 col-md-offset-3">
            <x-form.help :name="$name" :icon="$help_icon" :class="$help_class">{!! $help_text !!}</x-form.help>
        </div>
    @elseif ($help_html)
        {{-- Raw HTML help. Caller has opted in via help_html rather than
             help_text. Callers passing a trans() string with HTML MUST use
             the static-attribute form  help_html="{!! trans('...') !!}",
             the dynamic-binding form  :help_html="trans('...')"  runs the
             value through BladeCompiler::sanitizeComponentAttribute() and
             turns <a> tags into &lt;a&gt; entities. See x-form.row for the
             same wiring. --}}
        <div class="col-md-8 col-md-offset-3">
            <p class="help-block" id="{{ $name }}-help">
                @if ($help_icon)
                    <x-icon :type="$help_icon" />
                @endif
                {!! $help_html !!}
            </p>
        </div>
    @endif

</div>
