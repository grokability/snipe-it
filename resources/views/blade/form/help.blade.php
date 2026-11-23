@props(['name', 'icon' => null])
{{-- help-block content is always from trans() (admin-controlled lang
     strings, never user input), so render it unescaped so <strong> /
     <code> / <em> markup inside help copy renders instead of printing
     the raw tags. --}}
<p {{ $attributes->merge(['class' => 'help-block', 'id' => $name.'-help']) }}>
    @if ($icon)
        <x-icon :type="$icon" />
    @endif
    {!! $slot !!}
</p>
