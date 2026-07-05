@props([
    'name' => 'default',
])

<!-- tab-pane -->

<div id="{{ $name }}" {{ $attributes->merge(['class' => 'snipetab-pane tab-pane fade']) }}>

    <div class="row">
        <div class="col-md-12">
            @if (isset($table_header))
            <h3 class="box-title{{ (!isset($bulkactions)) ? ' pull-left' : '' }}">
                {{ $table_header }}
            </h3>
        @endif

        @if (isset($bulkactions))
            <div id="{{ Illuminate\Support\Str::camel($name) }}ToolBar" class="pull-left" style="min-width:500px !important; padding-top: 10px;">
                {{ $bulkactions }}
            </div>
        @endif

        {{-- Render slots unconditionally — ComponentSlot::isEmpty()
             materializes the slot to inspect it, doubling every DB call
             inside (asset/model counts, presenter dataTableLayout, etc.).
             `isset($content)` is fine because it's a named slot check that
             doesn't render anything. --}}
        @isset($content)
            {{ $content }}
        @endisset

        {{ $slot }}
        </div>
    </div>


</div>
<!-- /.tab-pane -->