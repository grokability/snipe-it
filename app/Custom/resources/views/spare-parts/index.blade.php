@extends('custom::layouts.fom')

@section('title0')
    {{ __('custom::fom.sp_title') }}
@stop

@section('title')
    @yield('title0') @parent
@stop

@section('header_right')
    <a href="{{ route('fom.spare-parts.create') }}" class="btn btn-primary pull-right">
        <i class="fas fa-plus"></i> {{ __('custom::fom.sp_btn_new') }}
    </a>
@stop

@section('content')

    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fas fa-cogs"></i> {{ __('custom::fom.sp_title') }}</h3>
        </div>
        <div class="box-body">
            {{-- Stock Status Tabs --}}
            <ul class="nav nav-tabs" style="margin-bottom: 15px;">
                <li class="{{ !request('stock_status') ? 'active' : '' }}">
                    <a href="{{ route('fom.spare-parts.index') }}">{{ __('custom::fom.lbl_all') }}</a>
                </li>
                <li class="{{ request('stock_status') == 'critical' ? 'active' : '' }}">
                    <a href="{{ route('fom.spare-parts.index', ['stock_status' => 'critical']) }}">
                        <span class="text-red"><i class="fas fa-times-circle"></i> {{ __('custom::fom.stock_critical') }}</span>
                    </a>
                </li>
                <li class="{{ request('stock_status') == 'low' ? 'active' : '' }}">
                    <a href="{{ route('fom.spare-parts.index', ['stock_status' => 'low']) }}">
                        <span class="text-orange"><i class="fas fa-exclamation-triangle"></i> {{ __('custom::fom.stock_low') }}</span>
                    </a>
                </li>
                <li class="{{ request('stock_status') == 'ok' ? 'active' : '' }}">
                    <a href="{{ route('fom.spare-parts.index', ['stock_status' => 'ok']) }}">
                        <span class="text-green"><i class="fas fa-check-circle"></i> {{ __('custom::fom.stock_ok') }}</span>
                    </a>
                </li>
            </ul>

            {{-- Search --}}
            <form method="GET" action="{{ route('fom.spare-parts.index') }}" class="form-inline" style="margin-bottom: 15px;">
                @if(request('stock_status'))
                    <input type="hidden" name="stock_status" value="{{ request('stock_status') }}">
                @endif
                <div class="input-group" style="max-width: 350px;">
                    <input type="text" name="search" class="form-control" placeholder="{{ __('custom::fom.sp_search_placeholder') }}" value="{{ request('search') }}">
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                    </span>
                </div>
            </form>

            @if($parts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('custom::fom.sp_col_name') }}</th>
                                <th>{{ __('custom::fom.sp_col_number') }}</th>
                                <th>{{ __('custom::fom.sp_col_category') }}</th>
                                <th>{{ __('custom::fom.sp_col_stock') }}</th>
                                <th>{{ __('custom::fom.sp_col_minimum') }}</th>
                                <th>{{ __('custom::fom.sp_col_status') }}</th>
                                <th>{{ __('custom::fom.sp_col_location') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parts as $part)
                                @php
                                    $statusClass = match($part->stock_status) {
                                        'critical' => 'label-danger',
                                        'low'      => 'label-warning',
                                        default    => 'label-success',
                                    };
                                    $statusLabel = match($part->stock_status) {
                                        'critical' => __('custom::fom.stock_critical'),
                                        'low'      => __('custom::fom.stock_low'),
                                        default    => __('custom::fom.stock_ok'),
                                    };
                                    $qtyClass = match($part->stock_status) {
                                        'critical' => 'text-red',
                                        'low'      => 'text-orange',
                                        default    => '',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('fom.spare-parts.show', $part->id) }}">
                                            <strong>{{ $part->name }}</strong>
                                        </a>
                                    </td>
                                    <td><code>{{ $part->part_number }}</code></td>
                                    <td>{{ $part->category?->name ?? '—' }}</td>
                                    <td class="{{ $qtyClass }}"><strong>{{ $part->quantity_on_hand }}</strong></td>
                                    <td>{{ $part->minimum_quantity }}</td>
                                    <td><span class="label {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                    <td>{{ $part->location?->name ?? '—' }}</td>
                                    <td>
                                        <a href="{{ route('fom.spare-parts.stock-in', $part->id) }}" class="btn btn-sm btn-success" title="{{ __('custom::fom.si_title') }}">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                        <a href="{{ route('fom.spare-parts.show', $part->id) }}" class="btn btn-sm btn-default" title="{{ __('custom::fom.btn_detail') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-center">{{ $parts->links() }}</div>
            @else
                <div class="text-center" style="padding: 40px 0;">
                    <i class="fas fa-inbox fa-3x text-muted"></i>
                    <p class="text-muted" style="margin-top: 15px;">{{ __('custom::fom.sp_none_found') }}</p>
                    <a href="{{ route('fom.spare-parts.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('custom::fom.sp_define_first') }}
                    </a>
                </div>
            @endif
        </div>
    </div>

@stop
