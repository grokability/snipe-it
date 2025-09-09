@props([
    'user'
])

@if ($user)
    @can('view', $user)
        @if(! $user->trashed())
            {{-- if the user is in database, the viewer CAN see them, and the user is not deleted --}}
            <a href="{{ route('users.show', $user->id) }}">{{ $user->display_name }}</a>
        @else
            {{-- if the user is soft deleted, but the viewer can see them, add a strikethrough --}}
            <s><a href="{{ route('users.show', $user->id) }}">{{ $user->display_name }}</a></s>
        @endif
    @else
        @if(! $user->trashed())
            {{-- if the user is in database and not soft-deleted --}}
            <span>{{ $user->display_name }}</span>
        @else
            {{-- if the user exists but is deleted and the viewer cannot click through to see their details --}}
            <s><span>{{ $user->display_name }}</span></s>
        @endif
    @endcan
@endif
