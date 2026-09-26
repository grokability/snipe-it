@props([
    'count' => null,
    'class' => false,
])

@can('view', \App\Models\Document::class)
<x-tabs.nav-item
    :$class
    name="documents"
    icon_type="files"
    label="{{ trans('documents.general.documents') }}"
    count="{{ $count }}"
    tooltip="{{ trans('documents.general.documents') }}"
/>
@endcan
