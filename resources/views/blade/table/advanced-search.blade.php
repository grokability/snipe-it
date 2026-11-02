@props([
    'tableId',
    'model' => null,
    'preserveParams' => [],
])

<x-table.advanced-filters
    :tableId="$tableId"
    :model="$model"
/>

<x-table.saved-searches
    :tableId="$tableId"
    :model="$model"
    :preserveParams="$preserveParams"
/>
