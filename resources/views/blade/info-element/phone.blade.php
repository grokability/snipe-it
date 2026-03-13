@if (!$slot->isEmpty())
    <a href="tel:{{ $slot }}">{{ $slot }}</a>
@endif
