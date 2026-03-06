@extends('layouts/default')

@section('title')
Confirm Delivery
@stop

@section('content')
<div class="col-md-8 col-md-offset-2">
    <h2>Confirm Delivery for: {{ $asset->asset_tag }}</h2>
    <h4>{{ $asset->name }}</h4>

    <form method="POST" action="{{ route('account.delivery.accept.store', $asset->id) }}">
        @csrf

        <div class="form-group">
            <label>Receiver Name (Όνομα Παραλήπτη):</label>
            <input type="text" name="receiver_name" class="form-control" required>
        </div>

        <button class="btn btn-success btn-block">Accept Delivery</button>
    </form>
</div>
@stop

