@props([
    'type' => null,
    'target',
    'routeArgs' => null,
    'queryParams' => [],
])

@php
    $modalHref = route('modal.show', $routeArgs ?? $type);
    if (! empty($queryParams)) {
        $modalHref .= (str_contains($modalHref, '?') ? '&' : '?').http_build_query($queryParams);
    }
@endphp

<a
    href="{{ $modalHref }}"
    data-toggle="modal"
    data-target="#createModal"
    data-select="{{ $target }}"
    class="btn btn-theme"
    data-tooltip="true"
    data-placement="top"
    data-title="{{ trans('general.create') }}"
    aria-label="{{ trans('general.create') }}"
>
    <x-icon type="create" class="fa-fw"/>
</a>
