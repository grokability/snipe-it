@extends('custom::layouts.fom')

@section('title0')
    {{ __('custom::fom.shift_accept_title') }}
@stop

@section('title')
    @yield('title0') @parent
@stop

@section('content')

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            {{-- Handover Info --}}
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fas fa-exchange-alt"></i>
                        {{ __('custom::fom.shift_handover_id') }} #{{ $handover->id }}
                    </h3>
                    <span class="label label-warning pull-right">{{ __('custom::fom.shift_awaiting') }}</span>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4"><strong>{{ __('custom::fom.shift_field_shift') }}:</strong> {{ $handover->shift_type }}</div>
                        <div class="col-md-4"><strong>{{ __('custom::fom.shift_field_date') }}:</strong> {{ $handover->shift_date->format('d.m.Y') }}</div>
                        <div class="col-md-4"><strong>{{ __('custom::fom.shift_field_location') }}:</strong> {{ $handover->location?->name }}</div>
                    </div>
                    <div class="row" style="margin-top: 10px;">
                        <div class="col-md-6"><strong>{{ __('custom::fom.shift_field_outgoing') }}:</strong> {{ $handover->outgoingUser?->full_name }}</div>
                    </div>
                    @if($handover->outgoing_notes)
                        <div class="well" style="margin-top: 10px;">
                            <strong>{{ __('custom::fom.shift_note_label') }}:</strong>
                            <p style="margin-top: 5px;">{{ $handover->outgoing_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Accept Form --}}
            <form method="POST" action="{{ route('fom.shift.update', $handover->id) }}">
                @csrf
                @method('PATCH')

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fas fa-clipboard-check"></i> {{ __('custom::fom.shift_equipment_status') }}</h3>
                    </div>
                    <div class="box-body">
                        @foreach($handover->items as $item)
                            @php
                                $conditionClass = match($item->condition) {
                                    'hasarli' => 'warning',
                                    'eksik'   => 'danger',
                                    default   => 'success',
                                };
                                $conditionLabel = match($item->condition) {
                                    'hasarli' => __('custom::fom.condition_hasarli'),
                                    'eksik'   => __('custom::fom.condition_eksik'),
                                    default   => __('custom::fom.condition_iyi'),
                                };
                            @endphp
                            <div style="padding: 10px; border-bottom: 1px solid #eee; {{ $item->condition !== 'iyi' ? 'background-color: #fcf8e3;' : '' }}">
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>{{ $item->asset?->name }}</strong>
                                        <br><small class="text-muted">{{ $item->asset?->asset_tag }}</small>
                                    </div>
                                    <div class="col-md-3">
                                        <span class="label label-{{ $conditionClass }}">{{ $conditionLabel }}</span>
                                        @if($item->notes)
                                            <br><small>{{ $item->notes }}</small>
                                        @endif
                                    </div>
                                    <div class="col-md-5">
                                        <select name="items[{{ $item->id }}][condition]" class="form-control input-sm">
                                            <option value="iyi" {{ $item->condition == 'iyi' ? 'selected' : '' }}>{{ __('custom::fom.condition_iyi') }}</option>
                                            <option value="hasarli" {{ $item->condition == 'hasarli' ? 'selected' : '' }}>{{ __('custom::fom.condition_hasarli') }}</option>
                                            <option value="eksik" {{ $item->condition == 'eksik' ? 'selected' : '' }}>{{ __('custom::fom.condition_eksik') }}</option>
                                        </select>
                                        <input type="text" name="items[{{ $item->id }}][notes]" class="form-control input-sm"
                                               style="margin-top: 4px;" placeholder="{{ __('custom::fom.shift_accept_notes_ph') }}" value="{{ $item->notes }}">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="box box-default">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="incoming_notes">{{ __('custom::fom.shift_accept_notes') }}</label>
                            <textarea name="incoming_notes" id="incoming_notes" class="form-control" rows="2"
                                      placeholder="{{ __('custom::fom.shift_accept_notes_ph') }}"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="incoming_signature">{{ __('custom::fom.shift_pin_label') }}</label>
                            <input type="password" name="incoming_signature" id="incoming_signature" class="form-control"
                                   style="max-width: 200px; font-size: 18px; letter-spacing: 8px;"
                                   maxlength="6" placeholder="••••••">
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-success btn-lg" style="padding: 14px 50px; font-size: 16px;">
                            <i class="fas fa-check-circle"></i> {{ __('custom::fom.shift_btn_accept') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@stop
