@extends('custom::layouts.fom')

@section('title0')
    {{ __('custom::fom.shift_history_title') }}
@stop

@section('title')
    @yield('title0') @parent
@stop

@section('header_right')
    <a href="{{ route('fom.shift.handover') }}" class="btn btn-primary pull-right">
        <i class="fas fa-plus"></i> {{ __('custom::fom.shift_new') }}
    </a>
@stop

@section('content')

    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fas fa-history"></i> {{ __('custom::fom.shift_history_title') }}</h3>
        </div>
        <div class="box-body">
            {{-- Filters --}}
            <form method="GET" action="{{ route('fom.shift.history') }}" class="form-inline" style="margin-bottom: 15px;">
                <div class="form-group" style="margin-right: 10px;">
                    <select name="location_id" class="form-control">
                        <option value="">{{ __('custom::fom.shift_all_locations') }}</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-right: 10px;">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="form-group" style="margin-right: 10px;">
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <button type="submit" class="btn btn-default"><i class="fas fa-filter"></i> {{ __('custom::fom.btn_filter') }}</button>
                @if(request()->hasAny(['location_id', 'date_from', 'date_to']))
                    <a href="{{ route('fom.shift.history') }}" class="btn btn-link">{{ __('custom::fom.btn_clear') }}</a>
                @endif
            </form>

            {{-- Table --}}
            @if($handovers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('custom::fom.shift_col_date') }}</th>
                                <th>{{ __('custom::fom.shift_col_shift') }}</th>
                                <th>{{ __('custom::fom.shift_col_location') }}</th>
                                <th>{{ __('custom::fom.shift_col_outgoing') }}</th>
                                <th>{{ __('custom::fom.shift_col_incoming') }}</th>
                                <th>{{ __('custom::fom.shift_col_equipment') }}</th>
                                <th>{{ __('custom::fom.shift_col_damaged') }}</th>
                                <th>{{ __('custom::fom.shift_col_status') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($handovers as $h)
                                @php
                                    $itemCount = $h->items_count ?? $h->items()->count();
                                    $damagedCount = $h->items()->whereIn('condition', ['hasarli', 'eksik'])->count();
                                @endphp
                                <tr>
                                    <td>{{ $h->shift_date->format('d.m.Y') }}</td>
                                    <td><span class="label label-default">{{ $h->shift_type }}</span></td>
                                    <td>{{ $h->location?->name }}</td>
                                    <td>{{ $h->outgoingUser?->first_name ?? '—' }}</td>
                                    <td>{{ $h->status === 'tamamlandi' ? ($h->incomingUser?->first_name ?? '—') : '—' }}</td>
                                    <td>{{ $itemCount }}</td>
                                    <td>
                                        @if($damagedCount > 0)
                                            <span class="label label-danger">{{ $damagedCount }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($h->status === 'tamamlandi')
                                            <span class="label label-success">{{ __('custom::fom.wo_status_tamamlandi') }}</span>
                                        @else
                                            <span class="label label-warning">{{ __('custom::fom.wo_status_bekliyor') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($h->status === 'bekliyor')
                                            <a href="{{ route('fom.shift.accept', $h->id) }}" class="btn btn-sm btn-info" title="{{ __('custom::fom.shift_btn_receive') }}">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-center">{{ $handovers->links() }}</div>
            @else
                <div class="text-center" style="padding: 40px 0;">
                    <i class="fas fa-inbox fa-3x text-muted"></i>
                    <p class="text-muted" style="margin-top: 15px;">{{ __('custom::fom.shift_none_found') }}</p>
                </div>
            @endif
        </div>
    </div>

@stop
