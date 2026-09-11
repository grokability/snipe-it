@props([
    'box_style' => 'default',
    'header' => false,
    'top_submit' => false,
    'sr_only_title' => false,
])
@aware(['name', 'route'])


<!-- Start box component -->
<div {{ $attributes->merge(['class' => 'box box-'.$box_style]) }}>

    @if ($header || $top_submit)
        <div class="box-header with-border">
            @if ($top_submit || isset($header_right))
                {{-- Two-column header shape: title on the left, a right-
                     aligned control on the right. `top_submit` bakes in
                     a Save button. The `header_right` slot lets you
                     supply any other right-aligned control (filter
                     dropdown, quick action, etc.). Title truncates with
                     an ellipsis so a long display_name can't push the
                     right-hand element off the row. --}}
                <div class="row">
                    <div class="col-md-10">
                        @if ($header)
                            <h2 class="box-title" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: block; padding-top: 7px;">
                                {{ $header }}
                            </h2>
                        @endif
                    </div>
                    <div class="col-md-2">
                        @if ($top_submit)
                            <button type="submit" class="btn btn-success pull-right" name="submit">
                                <x-icon type="checkmark"/>
                                {{ trans('general.save') }}
                            </button>
                        @else
                            <div class="pull-right">
                                {{ $header_right }}
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <h2 class="box-title">
                    {{ $header }}
                </h2>
            @endif
        </div>
    @endif

    <div class="box-body">

        @if (isset($table_header))
            <h3
                @if ($name) id="{{ $name }}-title" @endif
                class="{{ $sr_only_title ? 'sr-only' : 'box-title'.((!isset($bulkactions)) ? ' pull-left' : '') }}">
                {{ $table_header }}
            </h3>
        @endif


        @if (isset($bulkactions))
            <div id="{{ Illuminate\Support\Str::camel($name) }}ToolBar" class="pull-left" style="min-width:500px !important; padding-top: 10px;">
                {{ $bulkactions }}
            </div>
        @endif

            {{-- Render slot unconditionally — ComponentSlot::isEmpty()
                 materializes the slot to inspect it, doubling every DB call
                 inside. An empty slot renders nothing visible. --}}
            {{ $slot }}

    </div>

    @if (isset($customfooter))
        {{ $customfooter }}
    @elseif ($route)
        <x-box.footer />
    @endif
</div>
