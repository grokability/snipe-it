@extends('custom::layouts.fom')

@section('title0')
    {{ __('custom::fom.shift_title') }}
@stop

@section('title')
    @yield('title0') @parent
@stop

@section('header_right')
    <a href="{{ route('fom.shift.history') }}" class="btn btn-default pull-right">
        <i class="fas fa-history"></i> {{ __('custom::fom.shift_history_title') }}
    </a>
@stop

@push('css')
<style>
    .shift-badge {
        display: inline-block;
        padding: 8px 20px;
        border-radius: 4px;
        background: #2d6a2d;
        color: #fff;
        font-size: 18px;
        font-weight: bold;
    }
    .asset-row { padding: 12px; border-bottom: 1px solid #eee; }
    .asset-row:last-child { border-bottom: none; }
    .condition-radio label {
        padding: 8px 16px;
        font-size: 14px;
        cursor: pointer;
        margin-right: 5px;
    }
    .condition-notes { display: none; margin-top: 8px; }
    .condition-notes.visible { display: block; }
</style>
@endpush

@section('content')

    @if($errors->any())
        <div class="alert alert-danger">
            <ul style="margin-bottom: 0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Step 1: Location Selection --}}
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fas fa-exchange-alt"></i> {{ __('custom::fom.shift_create_title') }} — {{ __('custom::fom.shift_step1') }}</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ __('custom::fom.shift_current_shift') }}</label>
                        <div><span class="shift-badge"><i class="fas fa-clock"></i> {{ $currentShift }}</span></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <form method="GET" action="{{ route('fom.shift.handover') }}">
                        <div class="form-group">
                            <label for="location_id">{{ __('custom::fom.shift_select_location') }}</label>
                            <select name="location_id" id="location_id" class="form-control" onchange="this.form.submit()">
                                <option value="">{{ __('custom::fom.shift_location_ph') }}</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Step 2: Asset List + Condition --}}
    @if($assets->count() > 0)
        <form method="POST" action="{{ route('fom.shift.store') }}" id="handover-form">
            @csrf
            <input type="hidden" name="location_id" value="{{ request('location_id') }}">

            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-clipboard-check"></i> {{ __('custom::fom.shift_step2') }} ({{ $assets->count() }} {{ __('custom::fom.shift_col_equipment') }})</h3>
                </div>
                <div class="box-body">
                    @foreach($assets as $i => $asset)
                        <div class="asset-row">
                            <input type="hidden" name="items[{{ $i }}][asset_id]" value="{{ $asset->id }}">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>{{ $asset->name }}</strong>
                                    <br><small class="text-muted">{{ $asset->asset_tag }}</small>
                                </div>
                                <div class="col-md-5">
                                    <div class="condition-radio btn-group" data-toggle="buttons" data-index="{{ $i }}">
                                        <label class="btn btn-success active" style="padding: 8px 16px;">
                                            <input type="radio" name="items[{{ $i }}][condition]" value="iyi" checked> <i class="fas fa-check"></i> {{ __('custom::fom.condition_iyi') }}
                                        </label>
                                        <label class="btn btn-warning" style="padding: 8px 16px;">
                                            <input type="radio" name="items[{{ $i }}][condition]" value="hasarli"> <i class="fas fa-exclamation-triangle"></i> {{ __('custom::fom.condition_hasarli') }}
                                        </label>
                                        <label class="btn btn-danger" style="padding: 8px 16px;">
                                            <input type="radio" name="items[{{ $i }}][condition]" value="eksik"> <i class="fas fa-times-circle"></i> {{ __('custom::fom.condition_eksik') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="condition-notes" id="notes-{{ $i }}">
                                        <textarea name="items[{{ $i }}][notes]" class="form-control" rows="2"
                                                  placeholder="{{ __('custom::fom.shift_general_notes') }}"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Notes & Signature --}}
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-pen"></i> {{ __('custom::fom.shift_step3') }}</h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label for="outgoing_notes">{{ __('custom::fom.shift_general_notes') }}</label>
                        <textarea name="outgoing_notes" id="outgoing_notes" class="form-control" rows="3"
                                  placeholder="{{ __('custom::fom.shift_notes_ph') }}">{{ old('outgoing_notes') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="outgoing_signature">{{ __('custom::fom.shift_pin_label') }}</label>
                        <input type="password" name="outgoing_signature" id="outgoing_signature" class="form-control"
                               style="max-width: 200px; font-size: 18px; letter-spacing: 8px;"
                               maxlength="6" placeholder="••••••">
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary btn-lg" style="padding: 14px 50px; font-size: 16px;">
                        <i class="fas fa-paper-plane"></i> {{ __('custom::fom.shift_btn_submit') }}
                    </button>
                </div>
            </div>
        </form>
    @elseif(request('location_id'))
        <div class="box box-default">
            <div class="box-body text-center" style="padding: 40px;">
                <i class="fas fa-inbox fa-3x text-muted"></i>
                <p class="text-muted" style="margin-top: 15px;">{{ __('custom::fom.shift_no_assets') }}</p>
            </div>
        </div>
    @endif

@stop

@push('js')
<script nonce="{{ csrf_token() }}">
document.querySelectorAll('.condition-radio input[type="radio"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        var index = this.closest('.condition-radio').dataset.index;
        var notesDiv = document.getElementById('notes-' + index);
        if (this.value === 'hasarli' || this.value === 'eksik') {
            notesDiv.classList.add('visible');
            notesDiv.querySelector('textarea').setAttribute('required', 'required');
        } else {
            notesDiv.classList.remove('visible');
            notesDiv.querySelector('textarea').removeAttribute('required');
        }
    });
});
</script>
@endpush
