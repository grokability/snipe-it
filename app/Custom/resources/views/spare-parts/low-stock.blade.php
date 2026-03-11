@extends('custom::layouts.fom')

@section('title0')
    {{ __('custom::fom.ls_title') }}
@stop

@section('title')
    @yield('title0') @parent
@stop

@section('header_right')
    <a href="{{ route('fom.spare-parts.index') }}" class="btn btn-default pull-right">
        <i class="fas fa-arrow-left"></i> {{ __('custom::fom.ls_all_parts') }}
    </a>
@stop

@section('content')

    @if($parts->count() > 0)
        <div class="callout callout-warning">
            <h4><i class="fas fa-exclamation-triangle"></i> {{ $parts->total() }} {{ __('custom::fom.ls_alert_heading') }}</h4>
            <p>{{ __('custom::fom.ls_alert_body') }}</p>
        </div>
    @endif

    <div class="box box-warning">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fas fa-exclamation-triangle"></i> {{ __('custom::fom.ls_box_title') }}</h3>
        </div>
        <div class="box-body">
            @if($parts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('custom::fom.sp_col_name') }}</th>
                                <th>{{ __('custom::fom.sp_col_number') }}</th>
                                <th>{{ __('custom::fom.sp_current_stock') }}</th>
                                <th>{{ __('custom::fom.sp_col_minimum') }}</th>
                                <th>{{ __('custom::fom.ls_col_deficit') }}</th>
                                <th>{{ __('custom::fom.ls_col_lead_time') }}</th>
                                <th>{{ __('custom::fom.sp_col_location') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parts as $part)
                                @php
                                    $deficit = max(0, $part->minimum_quantity - $part->quantity_on_hand);
                                    $rowClass = $part->quantity_on_hand <= 0 ? 'danger' : 'warning';
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td>
                                        <a href="{{ route('fom.spare-parts.show', $part->id) }}">
                                            <strong>{{ $part->name }}</strong>
                                        </a>
                                    </td>
                                    <td><code>{{ $part->part_number }}</code></td>
                                    <td>
                                        <strong class="{{ $part->quantity_on_hand <= 0 ? 'text-red' : 'text-orange' }}">
                                            {{ $part->quantity_on_hand }}
                                        </strong>
                                    </td>
                                    <td>{{ $part->minimum_quantity }}</td>
                                    <td><span class="text-red">-{{ $deficit }}</span></td>
                                    <td>{{ $part->lead_time_days ? $part->lead_time_days . ' ' . __('custom::fom.sp_detail_day_suffix') : '—' }}</td>
                                    <td>{{ $part->location?->name ?? '—' }}</td>
                                    <td>
                                        <a href="{{ route('fom.spare-parts.stock-in', $part->id) }}" class="btn btn-sm btn-success" title="{{ __('custom::fom.si_title') }}">
                                            <i class="fas fa-plus"></i>
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
                    <i class="fas fa-check-circle fa-3x text-green"></i>
                    <p class="text-muted" style="margin-top: 15px;">{{ __('custom::fom.ls_all_ok') }}</p>
                </div>
            @endif
        </div>
    </div>

@stop
