@extends('custom::layouts.fom')

@section('title0')
    {{ __('custom::fom.wo_title') }}
@stop

@section('title')
    @yield('title0') @parent
@stop

@section('header_right')
    <a href="{{ route('fom.work-orders.create') }}" class="btn btn-primary pull-right">
        <i class="fas fa-plus"></i> {{ __('custom::fom.wo_new') }}
    </a>
    <a href="{{ route('fom.work-orders.board') }}" class="btn btn-default pull-right" style="margin-right: 5px;">
        <i class="fas fa-columns"></i> {{ __('custom::fom.wo_kanban') }}
    </a>
@stop

@section('content')

    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fas fa-wrench"></i> {{ __('custom::fom.wo_title') }}</h3>
        </div>
        <div class="box-body">
            {{-- Filter Bar --}}
            <form method="GET" action="{{ route('fom.work-orders.index') }}" class="form-inline" style="margin-bottom: 15px;">
                <div class="form-group" style="margin-right: 10px;">
                    <select name="status" class="form-control">
                        <option value="">{{ __('custom::fom.wo_all_statuses') }}</option>
                        <option value="bekliyor" {{ request('status') == 'bekliyor' ? 'selected' : '' }}>{{ __('custom::fom.wo_status_bekliyor') }}</option>
                        <option value="atandi" {{ request('status') == 'atandi' ? 'selected' : '' }}>{{ __('custom::fom.wo_status_atandi') }}</option>
                        <option value="devam_ediyor" {{ request('status') == 'devam_ediyor' ? 'selected' : '' }}>{{ __('custom::fom.wo_status_devam_ediyor') }}</option>
                        <option value="tamamlandi" {{ request('status') == 'tamamlandi' ? 'selected' : '' }}>{{ __('custom::fom.wo_status_tamamlandi') }}</option>
                        <option value="iptal" {{ request('status') == 'iptal' ? 'selected' : '' }}>{{ __('custom::fom.wo_status_iptal') }}</option>
                    </select>
                </div>
                <div class="form-group" style="margin-right: 10px;">
                    <select name="priority" class="form-control">
                        <option value="">{{ __('custom::fom.wo_all_priorities') }}</option>
                        <option value="kritik" {{ request('priority') == 'kritik' ? 'selected' : '' }}>{{ __('custom::fom.priority_kritik') }}</option>
                        <option value="yuksek" {{ request('priority') == 'yuksek' ? 'selected' : '' }}>{{ __('custom::fom.priority_yuksek') }}</option>
                        <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>{{ __('custom::fom.priority_normal') }}</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-default"><i class="fas fa-filter"></i> {{ __('custom::fom.btn_filter') }}</button>
                @if(request()->hasAny(['status', 'priority']))
                    <a href="{{ route('fom.work-orders.index') }}" class="btn btn-link">{{ __('custom::fom.btn_clear') }}</a>
                @endif
            </form>

            {{-- Table --}}
            @if($workOrders->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('custom::fom.wo_col_number') }}</th>
                                <th>{{ __('custom::fom.wo_col_asset') }}</th>
                                <th>{{ __('custom::fom.wo_col_location') }}</th>
                                <th>{{ __('custom::fom.wo_col_priority') }}</th>
                                <th>{{ __('custom::fom.wo_col_status') }}</th>
                                <th>{{ __('custom::fom.wo_col_reported_by') }}</th>
                                <th>{{ __('custom::fom.wo_col_assigned_to') }}</th>
                                <th>{{ __('custom::fom.wo_col_date') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($workOrders as $wo)
                                <tr>
                                    <td>
                                        <a href="{{ route('fom.work-orders.show', $wo->id) }}">
                                            <strong>{{ $wo->work_order_number }}</strong>
                                        </a>
                                    </td>
                                    <td>
                                        {{ $wo->asset?->name ?? '—' }}
                                        <br><small class="text-muted">{{ $wo->asset?->asset_tag }}</small>
                                    </td>
                                    <td>{{ $wo->location?->name ?? '—' }}</td>
                                    <td>@include('custom::partials.priority-badge', ['priority' => $wo->priority])</td>
                                    <td>@include('custom::partials.status-badge', ['status' => $wo->status])</td>
                                    <td>{{ $wo->reportedBy?->first_name ?? '—' }}</td>
                                    <td>{{ $wo->assignedTo?->first_name ?? '—' }}</td>
                                    <td title="{{ $wo->created_at }}">{{ $wo->created_at->diffForHumans() }}</td>
                                    <td>
                                        <a href="{{ route('fom.work-orders.show', $wo->id) }}" class="btn btn-sm btn-default" title="{{ __('custom::fom.btn_detail') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="text-center">
                    {{ $workOrders->links() }}
                </div>
            @else
                <div class="text-center" style="padding: 40px 0;">
                    <i class="fas fa-inbox fa-3x text-muted"></i>
                    <p class="text-muted" style="margin-top: 15px;">
                        @if(request()->hasAny(['status', 'priority']))
                            {{ __('custom::fom.wo_no_match') }}
                        @else
                            {{ __('custom::fom.wo_none_yet') }}
                        @endif
                    </p>
                    <a href="{{ route('fom.work-orders.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('custom::fom.wo_create_first') }}
                    </a>
                </div>
            @endif
        </div>
    </div>

@stop
