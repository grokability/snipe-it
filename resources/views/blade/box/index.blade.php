@props([
    'box_style' => 'default',
    'header' => false,
    'footer' => false,
])
@aware(['name', 'route'])


<!-- Start box component -->
<div {{ $attributes->merge(['class' => 'box box-'.$box_style]) }}>

    @if ($header)
        <div class="box-header with-border">
            <h2 class="box-title">
                {{ $header }}
            </h2>
        </div>
    @endif

    <div class="box-body">

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

    @if ($route)
        <x-box.footer />
    @endif
</div>