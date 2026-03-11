@extends('custom::layouts.fom')

@section('title0')
    {{ $purchaseRequest->request_number }}
@stop

@section('title')
    @yield('title0') @parent
@stop

@section('header_right')
    <a href="{{ route('fom.purchase-requests.index') }}" class="btn btn-default pull-right">
        <i class="fas fa-arrow-left"></i> {{ __('custom::fom.pr_back') }}
    </a>
@stop

@section('content')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fas fa-shopping-cart"></i>
                        {{ $purchaseRequest->request_number }}
                        @include('custom::partials.purchase-status-badge', ['status' => $purchaseRequest->status])
                    </h3>
                </div>
                <div class="box-body">
                    <table class="table table-condensed">
                        <tr>
                            <td><strong>{{ __('custom::fom.pr_field_part') }}</strong></td>
                            <td>
                                <a href="{{ route('fom.spare-parts.show', $purchaseRequest->spare_part_id) }}">
                                    {{ $purchaseRequest->sparePart?->name }}
                                </a>
                                (<code>{{ $purchaseRequest->sparePart?->part_number }}</code>)
                            </td>
                        </tr>
                        <tr><td><strong>{{ __('custom::fom.pr_field_qty') }}</strong></td><td>{{ $purchaseRequest->requested_quantity }} {{ __('custom::fom.pr_units_suffix') }}</td></tr>
                        <tr>
                            <td><strong>{{ __('custom::fom.pr_field_cost') }}</strong></td>
                            <td>{{ $purchaseRequest->estimated_cost ? number_format($purchaseRequest->estimated_cost, 2, ',', '.') . ' TRY' : '—' }}</td>
                        </tr>
                        <tr><td><strong>{{ __('custom::fom.pr_field_reason') }}</strong></td><td>{{ $purchaseRequest->reason }}</td></tr>
                        <tr><td><strong>{{ __('custom::fom.pr_field_requested_by') }}</strong></td><td>{{ $purchaseRequest->requestedBy?->full_name ?? __('custom::fom.lbl_system') }}</td></tr>
                        <tr><td><strong>{{ __('custom::fom.pr_field_approved_by') }}</strong></td><td>{{ $purchaseRequest->approvedBy?->full_name ?? '—' }}</td></tr>
                        <tr><td><strong>{{ __('custom::fom.pr_field_supplier') }}</strong></td><td>{{ $purchaseRequest->sparePart?->supplier?->name ?? '—' }}</td></tr>
                        <tr><td><strong>{{ __('custom::fom.pr_field_linked_wo') }}</strong></td>
                            <td>
                                @if($purchaseRequest->workOrder)
                                    <a href="{{ route('fom.work-orders.show', $purchaseRequest->work_order_id) }}">
                                        {{ $purchaseRequest->workOrder->work_order_number }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        <tr><td><strong>{{ __('custom::fom.pr_field_date') }}</strong></td><td>{{ $purchaseRequest->created_at->format('d.m.Y H:i') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ __('custom::fom.pr_actions_box') }}</h3>
                </div>
                <div class="box-body">
                    @if($purchaseRequest->status === 'bekliyor')
                        <form method="POST" action="{{ route('fom.purchase-requests.update', $purchaseRequest->id) }}" style="margin-bottom: 10px;">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-check"></i> {{ __('custom::fom.pr_btn_approve') }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('fom.purchase-requests.update', $purchaseRequest->id) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-times"></i> {{ __('custom::fom.pr_btn_reject') }}
                            </button>
                        </form>
                    @endif

                    @if($purchaseRequest->status === 'onaylandi')
                        <form method="POST" action="{{ route('fom.purchase-requests.update', $purchaseRequest->id) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="action" value="order">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-truck"></i> {{ __('custom::fom.pr_btn_order') }}
                            </button>
                        </form>
                    @endif

                    @if(in_array($purchaseRequest->status, ['onaylandi', 'siparis_verildi']))
                        <hr>
                        <form method="POST" action="{{ route('fom.purchase-requests.receive', $purchaseRequest->id) }}">
                            @csrf
                            <div class="form-group">
                                <label>{{ __('custom::fom.pr_receive_label') }}</label>
                                <input type="number" name="received_quantity" class="form-control" min="1"
                                       value="{{ $purchaseRequest->requested_quantity }}" required>
                            </div>
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-box-open"></i> {{ __('custom::fom.pr_btn_receive') }}
                            </button>
                        </form>
                    @endif

                    @if($purchaseRequest->status === 'teslim_alindi')
                        <div class="text-center text-success" style="padding: 20px;">
                            <i class="fas fa-check-circle fa-3x"></i>
                            <p style="margin-top: 10px;"><strong>{{ __('custom::fom.pr_completed') }}</strong></p>
                        </div>
                    @endif

                    @if($purchaseRequest->status === 'iptal')
                        <div class="text-center text-danger" style="padding: 20px;">
                            <i class="fas fa-times-circle fa-3x"></i>
                            <p style="margin-top: 10px;"><strong>{{ __('custom::fom.pr_cancelled') }}</strong></p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@stop
