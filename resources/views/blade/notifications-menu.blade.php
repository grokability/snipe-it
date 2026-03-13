<li class="dropdown notifications-menu">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="far fa-bell"></i>
        
	@if(\App\Models\ReturnRequest::whereNull('closed_at')->count() > 0)
        <span class="label label-danger">
            {{ \App\Models\ReturnRequest::whereNull('closed_at')->count() }}
        </span>
        @endif
    </a>

    <ul class="dropdown-menu">
        <li class="header">
            Notifications
        </li>

        <li>
            <ul class="menu">
                @foreach(\App\Models\ReturnRequest::whereNull('closed_at')->take(5)->get() as $request)
                    <li>
                        <a href="{{ route('returns.index') }}">
                            Return request #{{ $request->id }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </li>

        <li class="footer">
            <a href="{{ route('returns.index') }}">View all</a>
        </li>
    </ul>
</li>
