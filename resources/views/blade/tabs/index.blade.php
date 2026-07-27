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

    {{-- Optional footer slot for a submit/cancel row that should sit
         INSIDE .nav-tabs-custom so it stays visually attached to the
         tab card (Bootstrap 3 / AdminLTE 2 renders a shared border and
         background around the whole card). Callers pass:
             <x-slot:footer><x-redirect_submit_options .../></x-slot:footer>
         Omit the slot for read-only detail tabs that don't need one. --}}
    @isset($footer)
        {{ $footer }}
    @endisset


</div>
<!-- end tab container -->