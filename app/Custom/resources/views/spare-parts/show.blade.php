@extends('custom::layouts.fom')

@section('title0')
    {{ $part->name }}
@stop

@section('title')
    @yield('title0') @parent
@stop

@section('header_right')
    <a href="{{ route('fom.spare-parts.stock-in', $part->id) }}" class="btn btn-success pull-right" style="margin-left: 5px;">
        <i class="fas fa-plus"></i> {{ __('custom::fom.si_title') }}
    </a>
    <a href="{{ route('fom.spare-parts.index') }}" class="btn btn-default pull-right">
        <i class="fas fa-arrow-left"></i> {{ __('custom::fom.sp_back') }}
    </a>
@stop

@section('content')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Part Info --}}
    <div class="row">
        <div class="col-md-8">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-cog"></i> {{ $part->name }}</h3>
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
                    @endphp
                    <span class="label {{ $statusClass }} pull-right">{{ $statusLabel }}</span>
                </div>
                <div class="box-body">
                    <table class="table table-condensed">
                        <tr><td><strong>{{ __('custom::fom.sp_detail_part_no') }}</strong></td><td><code>{{ $part->part_number }}</code></td></tr>
                        <tr><td><strong>{{ __('custom::fom.sp_detail_category') }}</strong></td><td>{{ $part->category?->name ?? '—' }}</td></tr>
                        <tr><td><strong>{{ __('custom::fom.sp_detail_manufacturer') }}</strong></td><td>{{ $part->manufacturer?->name ?? '—' }}</td></tr>
                        <tr><td><strong>{{ __('custom::fom.sp_detail_supplier') }}</strong></td><td>{{ $part->supplier?->name ?? '—' }}</td></tr>
                        <tr><td><strong>{{ __('custom::fom.sp_detail_location') }}</strong></td><td>{{ $part->location?->name ?? '—' }}</td></tr>
                        <tr><td><strong>{{ __('custom::fom.sp_detail_unit_cost') }}</strong></td><td>{{ $part->unit_cost ? number_format($part->unit_cost, 2, ',', '.') . ' TRY' : '—' }}</td></tr>
                        <tr><td><strong>{{ __('custom::fom.sp_detail_lead_time') }}</strong></td><td>{{ $part->lead_time_days ? $part->lead_time_days . ' ' . __('custom::fom.sp_detail_day_suffix') : '—' }}</td></tr>
                    </table>
                    @if($part->description)
                        <p>{{ $part->description }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Stock Level --}}
        <div class="col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ __('custom::fom.sp_stock_level') }}</h3>
                </div>
                <div class="box-body text-center">
                    @php
                        $pct = $part->minimum_quantity > 0
                            ? min(100, ($part->quantity_on_hand / ($part->minimum_quantity * 2)) * 100)
                            : 100;
                        $barClass = match($part->stock_status) {
                            'critical' => 'progress-bar-danger',
                            'low'      => 'progress-bar-warning',
                            default    => 'progress-bar-success',
                        };
                    @endphp
                    <h2 style="margin: 0;">{{ $part->quantity_on_hand }}</h2>
                    <small class="text-muted">{{ __('custom::fom.sp_current_stock') }}</small>
                    <div class="progress" style="height: 25px; margin: 15px 0 10px;">
                        <div class="progress-bar {{ $barClass }}" style="width: {{ $pct }}%; line-height: 25px;">
                            {{ $part->quantity_on_hand }} / {{ $part->minimum_quantity * 2 }}
                        </div>
                    </div>
                    <small>{{ __('custom::fom.sp_col_minimum') }}: <strong>{{ $part->minimum_quantity }}</strong></small>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="box box-default">
        <div class="box-header with-border">
            <ul class="nav nav-tabs" id="part-tabs">
                <li class="active"><a href="#movements" data-toggle="tab"><i class="fas fa-exchange-alt"></i> {{ __('custom::fom.sp_tab_movements') }}</a></li>
                <li><a href="#models" data-toggle="tab"><i class="fas fa-industry"></i> {{ __('custom::fom.sp_tab_models') }}</a></li>
                <li><a href="#purchases" data-toggle="tab"><i class="fas fa-shopping-cart"></i> {{ __('custom::fom.sp_tab_purchases') }}</a></li>
            </ul>
        </div>
        <div class="box-body">
            <div class="tab-content">
                {{-- Movements --}}
                <div class="tab-pane active" id="movements">
                    @if($movements->count() > 0)
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('custom::fom.sp_mov_col_date') }}</th>
                                    <th>{{ __('custom::fom.sp_mov_col_type') }}</th>
                                    <th>{{ __('custom::fom.sp_mov_col_qty') }}</th>
                                    <th>{{ __('custom::fom.sp_mov_col_ref') }}</th>
                                    <th>{{ __('custom::fom.sp_mov_col_by') }}</th>
                                    <th>{{ __('custom::fom.sp_mov_col_notes') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($movements as $mv)
                                    @php
                                        $typeLabel = match($mv->movement_type) {
                                            'giris' => [__('custom::fom.movement_giris'), 'text-green'],
                                            'cikis' => [__('custom::fom.movement_cikis'), 'text-red'],
                                            'sayim' => [__('custom::fom.movement_sayim'), 'text-blue'],
                                            'iade'  => [__('custom::fom.movement_iade'), 'text-orange'],
                                            default => [$mv->movement_type, ''],
                                        };
                                    @endphp
                                    <tr>
                                        <td>{{ $mv->created_at?->format('d.m.Y H:i') }}</td>
                                        <td><span class="{{ $typeLabel[1] }}"><strong>{{ $typeLabel[0] }}</strong></span></td>
                                        <td class="{{ $mv->quantity > 0 ? 'text-green' : 'text-red' }}">
                                            <strong>{{ $mv->quantity > 0 ? '+' : '' }}{{ $mv->quantity }}</strong>
                                        </td>
                                        <td>
                                            @if($mv->reference_type)
                                                {{ $mv->reference_type }} #{{ $mv->reference_id }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ $mv->performedBy?->first_name ?? '—' }}</td>
                                        <td><small>{{ $mv->notes ?? '—' }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="text-center">{{ $movements->links() }}</div>
                    @else
                        <p class="text-muted text-center" style="padding: 20px;">{{ __('custom::fom.sp_no_movements') }}</p>
                    @endif
                </div>

                {{-- Compatible Models --}}
                <div class="tab-pane" id="models">
                    @if($part->assetModels->count() > 0)
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('custom::fom.sp_model_col_model') }}</th>
                                    <th>{{ __('custom::fom.sp_model_col_number') }}</th>
                                    <th>{{ __('custom::fom.sp_model_col_critical') }}</th>
                                    <th>{{ __('custom::fom.sp_model_col_rec_qty') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($part->assetModels as $model)
                                    <tr>
                                        <td>{{ $model->name }}</td>
                                        <td>{{ $model->model_number }}</td>
                                        <td>
                                            @if($model->pivot->is_critical)
                                                <span class="label label-danger">{{ __('custom::fom.stock_critical') }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $model->pivot->recommended_qty }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted text-center" style="padding: 20px;">{{ __('custom::fom.sp_no_models') }}</p>
                    @endif
                </div>

                {{-- Purchase Requests --}}
                <div class="tab-pane" id="purchases">
                    @php $prs = $part->purchaseRequests()->latest()->get(); @endphp
                    @if($prs->count() > 0)
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('custom::fom.pr_col_number') }}</th>
                                    <th>{{ __('custom::fom.pr_col_qty') }}</th>
                                    <th>{{ __('custom::fom.pr_col_cost') }}</th>
                                    <th>{{ __('custom::fom.pr_col_status') }}</th>
                                    <th>{{ __('custom::fom.pr_col_date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($prs as $pr)
                                    <tr>
                                        <td><a href="{{ route('fom.purchase-requests.show', $pr->id) }}">{{ $pr->request_number }}</a></td>
                                        <td>{{ $pr->requested_quantity }}</td>
                                        <td>{{ $pr->estimated_cost ? number_format($pr->estimated_cost, 2, ',', '.') . ' TRY' : '—' }}</td>
                                        <td>@include('custom::partials.purchase-status-badge', ['status' => $pr->status])</td>
                                        <td>{{ $pr->created_at->format('d.m.Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted text-center" style="padding: 20px;">{{ __('custom::fom.sp_no_purchases') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

@stop
