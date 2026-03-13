@props([
    'class' => 'col-md-12',
    'columns' => 1,
])

<!-- Start container+row component -->
<div {{ $attributes->merge(['class' => 'row']) }}>

    <!-- Only one column, so set the general col-md-12 div -->
    @if ($columns == 1)
        <x-page-column class="{{ $class }}">
            {{ $slot }}
        </x-page-column>

    @else

        <!-- the page using this should specify column names via the page-column component -->
        {{ $slot }}
    @endif


</div>