@extends('custom::layouts.fom')

@section('title0')
    {{ $workOrder->work_order_number }}
@stop

@section('title')
    @yield('title0') @parent
@stop

@section('header_right')
    <a href="{{ route('fom.work-orders.index') }}" class="btn btn-default pull-right">
        <i class="fas fa-arrow-left"></i> {{ __('custom::fom.wo_title') }}
    </a>
@stop

@section('content')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Header --}}
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fas fa-wrench"></i>
                        {{ $workOrder->work_order_number }}
                        @include('custom::partials.priority-badge', ['priority' => $workOrder->priority])
                        @include('custom::partials.status-badge', ['status' => $workOrder->status])
                    </h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-condensed">
                                <tr>
                                    <td><strong>{{ __('custom::fom.wo_field_asset') }}</strong></td>
                                    <td>{{ $workOrder->asset?->name }} <small class="text-muted">({{ $workOrder->asset?->asset_tag }})</small></td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('custom::fom.wo_field_location') }}</strong></td>
                                    <td>{{ $workOrder->location?->name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('custom::fom.wo_field_reported_by') }}</strong></td>
                                    <td>{{ $workOrder->reportedBy?->full_name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('custom::fom.wo_field_assigned_tech') }}</strong></td>
                                    <td>{{ $workOrder->assignedTo?->full_name ?? '—' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-condensed">
                                <tr>
                                    <td><strong>{{ __('custom::fom.wo_field_created_at') }}</strong></td>
                                    <td>{{ $workOrder->created_at->format('d.m.Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('custom::fom.wo_field_started_at') }}</strong></td>
                                    <td>{{ $workOrder->started_at ? $workOrder->started_at->format('d.m.Y H:i') : '—' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('custom::fom.wo_field_completed_at') }}</strong></td>
                                    <td>{{ $workOrder->completed_at ? $workOrder->completed_at->format('d.m.Y H:i') : '—' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('custom::fom.wo_field_time_spent') }}</strong></td>
                                    <td>{{ $workOrder->time_spent_minutes ? $workOrder->time_spent_minutes . ' ' . __('custom::fom.lbl_dash') : '—' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="well" style="margin-top: 10px;">
                        <strong><i class="fas fa-align-left"></i> {{ __('custom::fom.wo_field_description') }}</strong>
                        <p style="margin-top: 5px;">{{ $workOrder->description }}</p>
                    </div>

                    {{-- Resolution Notes --}}
                    @if($workOrder->resolution_notes)
                        <div class="well" style="background-color: #dff0d8; border-color: #d6e9c6;">
                            <strong><i class="fas fa-check-circle text-green"></i> {{ __('custom::fom.wo_field_resolution') }}</strong>
                            <p style="margin-top: 5px;">{{ $workOrder->resolution_notes }}</p>
                        </div>
                    @endif

                    {{-- Photo --}}
                    @if($workOrder->photo_path)
                        <div style="margin-top: 10px;">
                            <strong><i class="fas fa-camera"></i> {{ __('custom::fom.wo_field_photo') }}</strong><br>
                            <img src="{{ Storage::disk('public')->url($workOrder->photo_path) }}" alt="{{ __('custom::fom.wo_fault_photo_alt') }}"
                                 style="max-width: 400px; margin-top: 5px; border-radius: 4px; border: 1px solid #ddd;">
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="row">
        <div class="col-md-12">
            @if($workOrder->status === 'bekliyor')
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fas fa-user-plus"></i> {{ __('custom::fom.wo_assign_title') }}</h3>
                    </div>
                    <form method="POST" action="{{ route('fom.work-orders.update', $workOrder->id) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="action" value="assign">
                        <div class="box-body">
                            <div class="form-group">
                                <label for="assigned_to">{{ __('custom::fom.wo_assign_select') }}</label>
                                <select name="assigned_to" id="assigned_to" class="form-control" required>
                                    <option value="">{{ __('custom::fom.wo_assign_select') }}</option>
                                    @foreach($technicians as $tech)
                                        <option value="{{ $tech->id }}">
                                            {{ $tech->first_name }} {{ $tech->last_name }}
                                            @if($tech->jobtitle) — {{ $tech->jobtitle }} @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info"><i class="fas fa-user-check"></i> {{ __('custom::fom.wo_btn_assign') }}</button>
                        </div>
                    </form>
                </div>
            @endif

            @if($workOrder->status === 'atandi')
                <form method="POST" action="{{ route('fom.work-orders.update', $workOrder->id) }}" style="display: inline;">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="action" value="start">
                    <button type="submit" class="btn btn-warning btn-lg">
                        <i class="fas fa-play"></i> {{ __('custom::fom.wo_btn_start') }}
                    </button>
                </form>
            @endif

            @if($workOrder->status === 'devam_ediyor')
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fas fa-check-circle"></i> {{ __('custom::fom.wo_complete_title') }}</h3>
                    </div>
                    <form method="POST" action="{{ route('fom.work-orders.update', $workOrder->id) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="action" value="complete">
                        <div class="box-body">
                            <div class="form-group">
                                <label for="resolution_notes">{{ __('custom::fom.wo_resolution_label') }} <span class="text-red">*</span></label>
                                <textarea name="resolution_notes" id="resolution_notes" class="form-control" rows="3"
                                          required minlength="5" placeholder="{{ __('custom::fom.wo_resolution_placeholder') }}"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="time_spent_minutes">{{ __('custom::fom.wo_time_label') }} <span class="text-red">*</span></label>
                                <input type="number" name="time_spent_minutes" id="time_spent_minutes" class="form-control"
                                       required min="1" placeholder="{{ __('custom::fom.wo_time_placeholder') }}" style="max-width: 200px;">
                            </div>

                            {{-- Parts Used --}}
                            <hr>
                            <h4><i class="fas fa-cogs"></i> {{ __('custom::fom.wo_parts_title') }}</h4>
                            <p class="text-muted" style="font-size: 12px;">{{ __('custom::fom.wo_parts_note') }}</p>
                            <div id="parts-container">
                                <div class="parts-row row" style="margin-bottom: 8px;">
                                    <div class="col-md-6">
                                        <select name="parts[0][spare_part_id]" class="form-control">
                                            <option value="">{{ __('custom::fom.wo_select_part') }}</option>
                                            @foreach($spareParts as $sp)
                                                <option value="{{ $sp->id }}">{{ $sp->name }} ({{ $sp->part_number }}) — Stok: {{ $sp->quantity_on_hand }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" name="parts[0][quantity]" class="form-control" min="1" placeholder="{{ __('custom::fom.wo_qty_placeholder') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-sm btn-default" onclick="addPartRow()">
                                            <i class="fas fa-plus"></i> {{ __('custom::fom.wo_btn_add_row') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check"></i> {{ __('custom::fom.wo_btn_complete') }}
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            @if(in_array($workOrder->status, ['bekliyor', 'atandi']))
                <form method="POST" action="{{ route('fom.work-orders.update', $workOrder->id) }}" style="display: inline; margin-left: 10px;"
                      onsubmit="return confirm('{{ __('custom::fom.wo_cancel_confirm') }}');">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="action" value="cancel">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> {{ __('custom::fom.wo_btn_cancel') }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Parts Used --}}
    @if($workOrder->parts->count() > 0)
        <div class="row" style="margin-top: 15px;">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fas fa-cogs"></i> {{ __('custom::fom.wo_parts_tab') }}</h3>
                    </div>
                    <div class="box-body no-padding">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('custom::fom.wo_parts_col_part') }}</th>
                                    <th>{{ __('custom::fom.wo_parts_col_part_no') }}</th>
                                    <th>{{ __('custom::fom.wo_parts_col_qty') }}</th>
                                    <th>{{ __('custom::fom.wo_parts_col_notes') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($workOrder->parts as $part)
                                    <tr>
                                        <td>{{ $part->sparePart?->name ?? '—' }}</td>
                                        <td>{{ $part->sparePart?->part_number ?? '—' }}</td>
                                        <td>{{ $part->quantity_used }}</td>
                                        <td>{{ $part->notes ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Timeline --}}
    <div class="row" style="margin-top: 15px;">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-history"></i> {{ __('custom::fom.wo_timeline') }}</h3>
                </div>
                <div class="box-body">
                    <ul class="timeline">
                        <li>
                            <i class="fas fa-plus bg-blue"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> {{ $workOrder->created_at->format('d.m.Y H:i') }}</span>
                                <h3 class="timeline-header">{{ __('custom::fom.wo_tl_created') }}</h3>
                                <div class="timeline-body">
                                    {{ $workOrder->reportedBy?->full_name }} {{ __('custom::fom.wo_tl_reported_by') }}
                                </div>
                            </div>
                        </li>
                        @if($workOrder->assigned_to)
                            <li>
                                <i class="fas fa-user bg-aqua"></i>
                                <div class="timeline-item">
                                    <h3 class="timeline-header">{{ __('custom::fom.wo_tl_assigned') }}</h3>
                                    <div class="timeline-body">
                                        {{ $workOrder->assignedTo?->full_name }} {{ __('custom::fom.wo_tl_assigned_to') }}
                                    </div>
                                </div>
                            </li>
                        @endif
                        @if($workOrder->started_at)
                            <li>
                                <i class="fas fa-play bg-orange"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> {{ $workOrder->started_at->format('d.m.Y H:i') }}</span>
                                    <h3 class="timeline-header">{{ __('custom::fom.wo_tl_started') }}</h3>
                                </div>
                            </li>
                        @endif
                        @if($workOrder->completed_at)
                            <li>
                                <i class="fas fa-check bg-green"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> {{ $workOrder->completed_at->format('d.m.Y H:i') }}</span>
                                    <h3 class="timeline-header">{{ __('custom::fom.wo_tl_completed') }}</h3>
                                    <div class="timeline-body">
                                        {{ __('custom::fom.wo_tl_duration') }}: {{ $workOrder->time_spent_minutes }} {{ __('custom::fom.lbl_dash') }}
                                    </div>
                                </div>
                            </li>
                        @endif
                        @if($workOrder->status === 'iptal')
                            <li>
                                <i class="fas fa-times bg-red"></i>
                                <div class="timeline-item">
                                    <h3 class="timeline-header">{{ __('custom::fom.wo_tl_cancelled') }}</h3>
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

@push('js')
<script nonce="{{ csrf_token() }}">
var partRowIdx = 1;
function addPartRow() {
    var optionsHtml = document.querySelector('#parts-container select').innerHTML;
    var row = document.createElement('div');
    row.className = 'parts-row row';
    row.style.marginBottom = '8px';
    row.innerHTML =
        '<div class="col-md-6"><select name="parts[' + partRowIdx + '][spare_part_id]" class="form-control">' + optionsHtml + '</select></div>' +
        '<div class="col-md-3"><input type="number" name="parts[' + partRowIdx + '][quantity]" class="form-control" min="1" placeholder="{{ __('custom::fom.wo_qty_placeholder') }}"></div>' +
        '<div class="col-md-3"><button type="button" class="btn btn-sm btn-danger" onclick="this.closest(\'.parts-row\').remove()"><i class="fas fa-trash"></i></button></div>';
    document.getElementById('parts-container').appendChild(row);
    partRowIdx++;
}
</script>
@endpush
@stop
