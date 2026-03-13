@extends('layouts.default')

@section('title')
Notifications
@endsection

@section('content')
<div class="box box-primary">
  <div class="box-body">
    <h3>Pending</h3>

    @forelse ($pending as $notification)
      @php
        $type = $notification->data['type'] ?? null;
        $openUrl = route('notifications.open', $notification->id);

        $title = $notification->data['title'] ?? ($notification->data['item_name'] ?? 'Notification');
        $message = $notification->data['message'] ?? null;

        $requestedBy = $notification->data['requested_by'] ?? null;
        $itemName = $notification->data['item_name'] ?? null;
        $itemTag = $notification->data['item_tag'] ?? null;

        // ✅ για admin: δείξε σε ποιον ανήκει
        $toUser = null;
        if (!empty($isAdmin) && method_exists($notification, 'notifiable')) {
          $toUser = $notification->notifiable;
        }
      @endphp

      <div class="alert {{ $notification->read_at ? 'alert-default' : 'alert-info' }}">
        <strong>
          @if ($type === 'acceptance_required')
            Acceptance required
            @if($itemTag) - {{ $itemTag }} @endif
            @if($itemName) ({{ $itemName }}) @endif
          @else
            {{ $title }}
          @endif
        </strong>

        @if(!empty($isAdmin) && $toUser)
          <div class="text-muted" style="margin-top:4px;">
            To: {{ $toUser->display_name ?? $toUser->username ?? ('User#'.$toUser->id) }}
          </div>
        @endif

        <br>

        @if ($type === 'acceptance_required')
          <span>{{ $message ?? 'You have received an asset. Please Accept or Decline it.' }}</span>
          <br>
          <span class="label label-warning">Action required</span>
          <br><br>
        @else
          @if ($itemName) Item: {{ $itemName }}<br>@endif
          @if ($requestedBy) Requested by: {{ $requestedBy }}<br>@endif
          @if ($message) {{ $message }}<br>@endif
          <small class="text-muted">Created at: {{ optional($notification->created_at)->timezone('Europe/Athens')->format('Y-m-d H:i') }}</small>
          <br><br>
        @endif

        <a class="btn btn-xs btn-primary" href="{{ $openUrl }}">Open</a>

        @if (is_null($notification->read_at))
          <form method="POST" action="{{ route('notifications.read', $notification->id) }}" style="display:inline;">
            @csrf
            <button class="btn btn-xs btn-default">Mark as read</button>
          </form>
        @endif

        @if (!empty($isAdmin))
          <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" style="display:inline;">
            @csrf
            @method('DELETE')
            <button class="btn btn-xs btn-danger" onclick="return confirm('Delete notification?')">Delete</button>
          </form>
        @endif
      </div>
    @empty
      <p>No pending notifications.</p>
    @endforelse

    <hr>

    <h3>Completed</h3>

    @forelse ($completed as $notification)
      @php
        $openUrl = route('notifications.open', $notification->id);
        $title = $notification->data['title'] ?? ($notification->data['item_name'] ?? 'Notification');
        $requestedBy = $notification->data['requested_by'] ?? null;
        $message = $notification->data['message'] ?? null;

        $toUser = null;
        if (!empty($isAdmin) && method_exists($notification, 'notifiable')) {
          $toUser = $notification->notifiable;
        }
      @endphp

      <div class="alert alert-default">
        <strong>{{ $title }}</strong>

        @if(!empty($isAdmin) && $toUser)
          <div class="text-muted" style="margin-top:4px;">
            To: {{ $toUser->display_name ?? $toUser->username ?? ('User#'.$toUser->id) }}
          </div>
        @endif

        <br>

        @if ($requestedBy) Requested by: {{ $requestedBy }}<br>@endif
        @if ($message) {{ $message }}<br>@endif

        <span class="label label-success">Completed</span>
        @if (is_null($notification->read_at))
          <span class="label label-warning">Unread</span>
        @endif

        <br><br>

        <a class="btn btn-xs btn-primary" href="{{ $openUrl }}">Open</a>

        @if (is_null($notification->read_at))
          <form method="POST" action="{{ route('notifications.read', $notification->id) }}" style="display:inline;">
            @csrf
            <button class="btn btn-xs btn-default">Mark as read</button>
          </form>
        @endif

        @if (!empty($isAdmin))
          <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" style="display:inline;">
            @csrf
            @method('DELETE')
            <button class="btn btn-xs btn-danger" onclick="return confirm('Delete notification?')">Delete</button>
          </form>
        @endif
      </div>
    @empty
      <p>No completed notifications.</p>
    @endforelse
  </div>
</div>
@endsection

