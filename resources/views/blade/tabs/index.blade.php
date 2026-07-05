@props([
    'tabnav',
    'tabpanes',
])

<!-- start tab container -->
<div class="nav-tabs-custom">

    {{-- Do NOT guard the slot renders with $slot->isEmpty() — ComponentSlot
         ::isEmpty() materializes the slot once just to inspect it, and the
         actual {{ $slot }} below materializes it a second time. Every count()
         / DB call inside a slot then fires twice. Render unconditionally;
         an empty slot renders empty content and the wrapper markup is fine. --}}
    <ul class="nav nav-tabs hidden-print nav-tabs-dropdown" role="tablist">
        {{ $tabnav }}
    </ul>

    <div class="tab-content">
        {{ $tabpanes }}
    </div>


</div>
<!-- end tab container -->