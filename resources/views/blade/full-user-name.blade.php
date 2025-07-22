@props([
    'user'
])

@if($user)
    @php
        $fullName = $user->present()->fullName();
    @endphp

    @can('view', $user)
        @if(! $user->trashed())
            {{-- if the user is in database but soft-deleted --}}
            <a href="{{ route('users.show', $user->id) }}">{{ $fullName }}</a>
        @else
            {{-- if the user exists --}}
            <s><a href="{{ route('users.show', $user->id) }}">{{ $fullName }}</a></s>
        @endif
    @else
        @if(! $user->trashed())
            {{-- if the user is in database but soft-deleted --}}
            <span>{{ $fullName }}</span>
        @else
            {{-- if the user exists --}}
            <s><span>{{ $fullName }}</span></s>
        @endif
    @endcan
@endif
