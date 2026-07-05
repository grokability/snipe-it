@props([
    'route' => null,
    'item' => null,
    'class' => 'form-horizontal',
    'id' => 'create-form',
])
@aware(['name', 'footer'])

<form id="{{ $id }}" class="{{ $class }}" method="post" action="{{ $route }}" autocomplete="off" role="form" enctype="multipart/form-data">

    @csrf

    @if ($item?->id)
        {{ method_field('PUT') }}
    @endif

    {{ $slot }}

</form>