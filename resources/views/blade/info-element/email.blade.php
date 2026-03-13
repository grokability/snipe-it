@if (!$slot->isEmpty())
    <a href="mailto:{{ $slot }}">{{ $slot }}</a>
@endif