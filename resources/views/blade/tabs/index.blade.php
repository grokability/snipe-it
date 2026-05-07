@props([
    'tabnav',
    'tabpanes',
])

<!-- start tab container -->
<div class="nav-tabs-custom">

    <ul class="nav nav-tabs hidden-print nav-tabs-dropdown" role="tablist">
        @if (!$tabnav->isEmpty())
            {{ $tabnav }}
        @endif
    </ul>

    <div class="tab-content">
        @if (!$tabpanes->isEmpty())
            {{ $tabpanes }}
        @endif
    </div>


</div>
<!-- end tab container -->