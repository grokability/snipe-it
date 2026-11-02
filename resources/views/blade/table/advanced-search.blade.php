@props([
    'tableId',
    'model' => null,
])

<x-table.advanced-filters
    :tableId="$tableId"
    :model="$model"
/>

<x-table.saved-searches
    :tableId="$tableId"
    :model="$model"
/>
