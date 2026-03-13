@props([
    'name' => 'default',
])

<!-- tab-pane -->
<div id="{{ $name }}" {{ $attributes->merge(['class' => 'snipetab-pane tab-pane fade']) }}  style="min-height: 400px !important;">

    @if (isset($header))
        <h2 class="box-title{{ (!isset($bulkactions)) ? ' pull-left' : '' }}">
            {{ $header }}
        </h2>
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
<!-- /.tab-pane -->