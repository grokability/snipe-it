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

        @if ((isset($content)) && (!$content->isEmpty()))
            {{ $content }}
        @endif

        @if (($slot) && (!$slot->isEmpty()))
            {{ $slot }}
        @endif
        </div>
    </div>


</div>
<!-- /.tab-pane -->