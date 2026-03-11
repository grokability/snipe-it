@extends('custom::layouts.fom')

@section('title0')
    {{ __('custom::fom.wo_kanban_board') }}
@stop

@section('title')
    @yield('title0') @parent
@stop

@section('header_right')
    <a href="{{ route('fom.work-orders.index') }}" class="btn btn-default pull-right" style="margin-left: 5px;">
        <i class="fas fa-list"></i> {{ __('custom::fom.wo_list') }}
    </a>
    <a href="{{ route('fom.work-orders.create') }}" class="btn btn-primary pull-right">
        <i class="fas fa-plus"></i> {{ __('custom::fom.wo_new') }}
    </a>
@stop

@push('css')
<style>
    .kanban-column { min-height: 400px; }
    .kanban-card {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 10px;
        margin-bottom: 8px;
        border-left: 4px solid #ddd;
    }
    .kanban-card.priority-kritik { border-left-color: #dd4b39; }
    .kanban-card.priority-yuksek { border-left-color: #f39c12; }
    .kanban-card.priority-normal { border-left-color: #00a65a; }
    .kanban-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .kanban-card .card-asset { font-weight: bold; font-size: 13px; }
    .kanban-card .card-meta { font-size: 11px; color: #999; margin-top: 4px; }
    .kanban-card .card-assignee {
        display: inline-block;
        width: 28px; height: 28px;
        border-radius: 50%;
        background: #2d6a2d;
        color: #fff;
        text-align: center;
        line-height: 28px;
        font-size: 11px;
        font-weight: bold;
        float: right;
    }
    .column-header {
        padding: 8px 12px;
        border-radius: 4px;
        margin-bottom: 10px;
        font-weight: bold;
        color: #fff;
    }
    .column-header .badge { background: rgba(255,255,255,0.3); }
</style>
@endpush

@section('content')

    <div class="row">
        @foreach($columns as $status => $label)
            @php
                $items = $workOrders->get($status, collect());
                $headerBg = match($status) {
                    'bekliyor'     => '#dd4b39',
                    'atandi'       => '#0073b7',
                    'devam_ediyor' => '#f39c12',
                    'tamamlandi'   => '#00a65a',
                    default        => '#999',
                };
            @endphp
            <div class="col-md-3">
                <div class="column-header" style="background-color: {{ $headerBg }};">
                    {{ $label }} <span class="badge">{{ $items->count() }}</span>
                </div>
                <div class="kanban-column">
                    @forelse($items as $wo)
                        <a href="{{ route('fom.work-orders.show', $wo->id) }}" style="text-decoration: none; color: inherit;">
                            <div class="kanban-card priority-{{ $wo->priority }}">
                                @if($wo->assignedTo)
                                    <span class="card-assignee" title="{{ $wo->assignedTo->full_name }}">
                                        {{ strtoupper(substr($wo->assignedTo->first_name, 0, 1)) }}{{ strtoupper(substr($wo->assignedTo->last_name, 0, 1)) }}
                                    </span>
                                @endif
                                <div class="card-asset">{{ $wo->asset?->name ?? '—' }}</div>
                                <div>
                                    @include('custom::partials.priority-badge', ['priority' => $wo->priority])
                                    <small class="text-muted">{{ $wo->work_order_number }}</small>
                                </div>
                                <div class="card-meta">
                                    <i class="fas fa-clock"></i> {{ $wo->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </a>
                    @empty
                        <p class="text-muted text-center" style="padding: 20px 0; font-size: 12px;">
                            {{ __('custom::fom.wo_no_match') }}
                        </p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

@stop
