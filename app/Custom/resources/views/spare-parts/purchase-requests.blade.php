@extends('custom::layouts.fom')

@section('title0')
    {{ __('custom::fom.pr_title') }}
@stop

@section('title')
    @yield('title0') @parent
@stop

@section('content')

    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fas fa-shopping-cart"></i> {{ __('custom::fom.pr_title') }}</h3>
        </div>
        <div class="box-body">
            {{-- Status Tabs --}}
            <ul class="nav nav-tabs" style="margin-bottom: 15px;">
                <li class="{{ !request('status') ? 'active' : '' }}">
                    <a href="{{ route('fom.purchase-requests.index') }}">{{ __('custom::fom.lbl_all') }}</a>
                </li>
                <li class="{{ request('status') == 'bekliyor' ? 'active' : '' }}">
                    <a href="{{ route('fom.purchase-requests.index', ['status' => 'bekliyor']) }}">{{ __('custom::fom.wo_status_bekliyor') }}</a>
                </li>
                <li class="{{ request('status') == 'onaylandi' ? 'active' : '' }}">
                    <a href="{{ route('fom.purchase-requests.index', ['status' => 'onaylandi']) }}">{{ __('custom::fom.btn_approve') }}</a>
                </li>
                <li class="{{ request('status') == 'siparis_verildi' ? 'active' : '' }}">
                    <a href="{{ route('fom.purchase-requests.index', ['status' => 'siparis_verildi']) }}">{{ __('custom::fom.pr_btn_order') }}</a>
                </li>
                <li class="{{ request('status') == 'teslim_alindi' ? 'active' : '' }}">
                    <a href="{{ route('fom.purchase-requests.index', ['status' => 'teslim_alindi']) }}">{{ __('custom::fom.pr_completed') }}</a>
                </li>
            </ul>

            @if($requests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('custom::fom.pr_col_number') }}</th>
                                <th>{{ __('custom::fom.pr_col_part') }}</th>
                                <th>{{ __('custom::fom.pr_col_qty') }}</th>
                                <th>{{ __('custom::fom.pr_col_cost') }}</th>
                                <th>{{ __('custom::fom.pr_col_status') }}</th>
                                <th>{{ __('custom::fom.pr_col_requested_by') }}</th>
                                <th>{{ __('custom::fom.pr_col_date') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $pr)
                                <tr>
                                    <td>
                                        <a href="{{ route('fom.purchase-requests.show', $pr->id) }}">
                                            <strong>{{ $pr->request_number }}</strong>
                                        </a>
                                    </td>
                                    <td>{{ $pr->sparePart?->name ?? '—' }}</td>
                                    <td>{{ $pr->requested_quantity }}</td>
                                    <td>{{ $pr->estimated_cost ? number_format($pr->estimated_cost, 2, ',', '.') . ' TRY' : '—' }}</td>
                                    <td>@include('custom::partials.purchase-status-badge', ['status' => $pr->status])</td>
                                    <td>{{ $pr->requestedBy?->first_name ?? __('custom::fom.lbl_system') }}</td>
                                    <td>{{ $pr->created_at->format('d.m.Y') }}</td>
                                    <td>
                                        @if($pr->status === 'bekliyor')
                                            <form method="POST" action="{{ route('fom.purchase-requests.update', $pr->id) }}" style="display: inline;">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="btn btn-sm btn-success" title="{{ __('custom::fom.btn_approve') }}">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('fom.purchase-requests.update', $pr->id) }}" style="display: inline;">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="btn btn-sm btn-danger" title="{{ __('custom::fom.btn_reject') }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('fom.purchase-requests.show', $pr->id) }}" class="btn btn-sm btn-default" title="{{ __('custom::fom.btn_detail') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-center">{{ $requests->links() }}</div>
            @else
                <div class="text-center" style="padding: 40px 0;">
                    <i class="fas fa-inbox fa-3x text-muted"></i>
                    <p class="text-muted" style="margin-top: 15px;">{{ __('custom::fom.pr_none_found') }}</p>
                </div>
            @endif
        </div>
    </div>

@stop
