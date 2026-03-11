@extends('custom::layouts.fom')

@section('title0')
    {{ __('custom::fom.dash_title') }}
@stop

@section('title')
    @yield('title0') @parent
@stop

@section('header_right')
    <a href="{{ route('fom.work-orders.create') }}" class="btn btn-primary pull-right" style="margin-left: 5px;">
        <i class="fas fa-plus"></i> {{ __('custom::fom.btn_new_fault') }}
    </a>
    <a href="{{ route('fom.shift.handover') }}" class="btn btn-default pull-right" style="margin-left: 5px;">
        <i class="fas fa-exchange-alt"></i> {{ __('custom::fom.btn_start_handover') }}
    </a>
    <a href="{{ route('fom.spare-parts.low-stock') }}" class="btn btn-default pull-right">
        <i class="fas fa-exclamation-triangle"></i> {{ __('custom::fom.btn_low_stock') }}
    </a>
@stop

@section('content')

    {{-- Stat Cards --}}
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon {{ $openTotal > 0 ? 'bg-red' : 'bg-green' }}">
                    <i class="fas fa-wrench"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('custom::fom.dash_open_wo') }}</span>
                    <span class="info-box-number">{{ $openTotal }}</span>
                    @if($openTotal > 0)
                        <small>
                            @if($openWorkOrders->get('kritik', 0) > 0)
                                <span class="text-red"><i class="fas fa-circle"></i> {{ $openWorkOrders->get('kritik', 0) }} {{ __('custom::fom.priority_kritik') }}</span>
                            @endif
                            @if($openWorkOrders->get('yuksek', 0) > 0)
                                <span class="text-orange"><i class="fas fa-circle"></i> {{ $openWorkOrders->get('yuksek', 0) }} {{ __('custom::fom.priority_yuksek') }}</span>
                            @endif
                        </small>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-blue">
                    <i class="fas fa-exchange-alt"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('custom::fom.dash_shift') }}</span>
                    <span class="info-box-number">{{ $handoversCompleted }}/{{ $handoversCompleted + $handoversPending }}</span>
                    <small>
                        @if($handoversPending > 0)
                            <span class="text-orange">{{ $handoversPending }} {{ __('custom::fom.wo_status_bekliyor') }}</span>
                        @else
                            <span class="text-green">{{ __('custom::fom.dash_completed') }}</span>
                        @endif
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon {{ $lowStockCount > 0 ? 'bg-yellow' : 'bg-green' }}">
                    <i class="fas fa-exclamation-triangle"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('custom::fom.dash_stock_alerts') }}</span>
                    <span class="info-box-number">{{ $lowStockCount }}</span>
                    <small>{{ $lowStockCount > 0 ? $lowStockCount . ' ' . __('custom::fom.dash_parts_low') : __('custom::fom.dash_stocks_ok') }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-green">
                    <i class="fas fa-shopping-cart"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('custom::fom.dash_purchase_req') }}</span>
                    <span class="info-box-number">{{ $pendingPurchases }}</span>
                    <small>{{ __('custom::fom.dash_pending_req') }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Priority Distribution --}}
        <div class="col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-chart-bar"></i> {{ __('custom::fom.dash_priority_dist') }}</h3>
                </div>
                <div class="box-body">
                    @if($openTotal > 0)
                        @php
                            $kritik = $openWorkOrders->get('kritik', 0);
                            $yuksek = $openWorkOrders->get('yuksek', 0);
                            $normal = $openWorkOrders->get('normal', 0);
                        @endphp
                        <div style="margin-bottom: 10px;">
                            <span class="text-red"><i class="fas fa-circle"></i> {{ __('custom::fom.priority_kritik') }}: {{ $kritik }}</span>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar progress-bar-danger" style="width: {{ $openTotal > 0 ? ($kritik / $openTotal * 100) : 0 }}%"></div>
                            </div>
                        </div>
                        <div style="margin-bottom: 10px;">
                            <span class="text-orange"><i class="fas fa-circle"></i> {{ __('custom::fom.priority_yuksek') }}: {{ $yuksek }}</span>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar progress-bar-warning" style="width: {{ $openTotal > 0 ? ($yuksek / $openTotal * 100) : 0 }}%"></div>
                            </div>
                        </div>
                        <div>
                            <span class="text-green"><i class="fas fa-circle"></i> {{ __('custom::fom.priority_normal') }}: {{ $normal }}</span>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar progress-bar-success" style="width: {{ $openTotal > 0 ? ($normal / $openTotal * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    @else
                        <p class="text-muted text-center" style="padding: 20px 0;">
                            <i class="fas fa-check-circle text-green"></i> {{ __('custom::fom.dash_no_open_wo') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent Work Orders --}}
        <div class="col-md-8">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-clock"></i> {{ __('custom::fom.dash_recent_wo') }}</h3>
                    <div class="box-tools">
                        <a href="{{ route('fom.work-orders.index') }}" class="btn btn-sm btn-default">
                            {{ __('custom::fom.btn_view_all') }} <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="box-body no-padding">
                    @if($recentWorkOrders->count() > 0)
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('custom::fom.dash_wo_col_wo') }}</th>
                                    <th>{{ __('custom::fom.dash_wo_col_asset') }}</th>
                                    <th>{{ __('custom::fom.dash_wo_col_priority') }}</th>
                                    <th>{{ __('custom::fom.dash_wo_col_status') }}</th>
                                    <th>{{ __('custom::fom.dash_wo_col_date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentWorkOrders as $wo)
                                    <tr>
                                        <td>
                                            <a href="{{ route('fom.work-orders.show', $wo->id) }}">
                                                {{ $wo->work_order_number }}
                                            </a>
                                        </td>
                                        <td>{{ $wo->asset?->name ?? __('custom::fom.lbl_dash') }}</td>
                                        <td>
                                            @include('custom::partials.priority-badge', ['priority' => $wo->priority])
                                        </td>
                                        <td>
                                            @include('custom::partials.status-badge', ['status' => $wo->status])
                                        </td>
                                        <td title="{{ $wo->created_at }}">{{ $wo->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted text-center" style="padding: 30px 0;">
                            {{ __('custom::fom.dash_no_wo_yet') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

@stop
