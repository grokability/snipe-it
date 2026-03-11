@extends('custom::layouts.fom')

@section('title0')
    {{ __('custom::fom.wo_new') }}
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

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-plus-circle"></i> {{ __('custom::fom.wo_new_report') }}</h3>
                </div>
                <form method="POST" action="{{ route('fom.work-orders.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="box-body">

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul style="margin-bottom: 0;">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Asset Selection --}}
                        <div class="form-group {{ $errors->has('asset_id') ? 'has-error' : '' }}">
                            <label for="asset_id"><i class="fas fa-cog"></i> {{ __('custom::fom.wo_asset_label') }} <span class="text-red">*</span></label>
                            <select name="asset_id" id="asset_id" class="form-control select2" style="width: 100%;" required>
                                <option value="">{{ __('custom::fom.wo_asset_placeholder') }}</option>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                                        {{ $asset->name }} ({{ $asset->asset_tag }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Priority --}}
                        <div class="form-group {{ $errors->has('priority') ? 'has-error' : '' }}">
                            <label><i class="fas fa-flag"></i> {{ __('custom::fom.wo_col_priority') }} <span class="text-red">*</span></label>
                            <div class="row">
                                <div class="col-xs-4">
                                    <label class="btn btn-block {{ old('priority', 'normal') == 'kritik' ? 'btn-danger' : 'btn-default' }}" style="padding: 15px; font-size: 16px; cursor: pointer;">
                                        <input type="radio" name="priority" value="kritik" {{ old('priority') == 'kritik' ? 'checked' : '' }} style="display:none;" onchange="updatePriorityButtons(this)">
                                        <i class="fas fa-exclamation-circle"></i><br>{{ __('custom::fom.priority_kritik') }}
                                    </label>
                                </div>
                                <div class="col-xs-4">
                                    <label class="btn btn-block {{ old('priority') == 'yuksek' ? 'btn-warning' : 'btn-default' }}" style="padding: 15px; font-size: 16px; cursor: pointer;">
                                        <input type="radio" name="priority" value="yuksek" {{ old('priority') == 'yuksek' ? 'checked' : '' }} style="display:none;" onchange="updatePriorityButtons(this)">
                                        <i class="fas fa-arrow-up"></i><br>{{ __('custom::fom.priority_yuksek') }}
                                    </label>
                                </div>
                                <div class="col-xs-4">
                                    <label class="btn btn-block {{ old('priority', 'normal') == 'normal' && !old('priority') ? 'btn-success' : (old('priority') == 'normal' ? 'btn-success' : 'btn-default') }}" style="padding: 15px; font-size: 16px; cursor: pointer;">
                                        <input type="radio" name="priority" value="normal" {{ old('priority', 'normal') == 'normal' ? 'checked' : '' }} style="display:none;" onchange="updatePriorityButtons(this)">
                                        <i class="fas fa-minus-circle"></i><br>{{ __('custom::fom.priority_normal') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                            <label for="description"><i class="fas fa-align-left"></i> {{ __('custom::fom.wo_description') }} <span class="text-red">*</span></label>
                            <textarea name="description" id="description" class="form-control" rows="4"
                                      placeholder="{{ __('custom::fom.wo_desc_placeholder') }}"
                                      required minlength="10">{{ old('description') }}</textarea>
                        </div>

                        {{-- Location --}}
                        <div class="form-group">
                            <label for="location_id"><i class="fas fa-map-marker-alt"></i> {{ __('custom::fom.wo_col_location') }}</label>
                            <select name="location_id" id="location_id" class="form-control">
                                <option value="">{{ __('custom::fom.wo_location_auto') }}</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Photo --}}
                        <div class="form-group">
                            <label for="photo"><i class="fas fa-camera"></i> {{ __('custom::fom.wo_photo') }}</label>
                            <input type="file" name="photo" id="photo" class="form-control" accept="image/*" capture="environment">
                            <div id="photo-preview" style="margin-top: 10px; display: none;">
                                <img id="photo-preview-img" src="" alt="{{ __('custom::fom.wo_photo_preview') }}" style="max-width: 300px; max-height: 200px; border-radius: 4px;">
                            </div>
                        </div>

                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary btn-lg" style="padding: 12px 40px;">
                            <i class="fas fa-paper-plane"></i> {{ __('custom::fom.wo_btn_create') }}
                        </button>
                        <a href="{{ route('fom.work-orders.index') }}" class="btn btn-default btn-lg">{{ __('custom::fom.btn_cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

@stop

@push('js')
<script nonce="{{ csrf_token() }}">
function updatePriorityButtons(radio) {
    document.querySelectorAll('input[name="priority"]').forEach(function(r) {
        var label = r.closest('label');
        label.className = 'btn btn-block btn-default';
        label.style.padding = '15px';
        label.style.fontSize = '16px';
        label.style.cursor = 'pointer';
    });
    var classes = {kritik: 'btn-danger', yuksek: 'btn-warning', normal: 'btn-success'};
    var label = radio.closest('label');
    label.className = 'btn btn-block ' + (classes[radio.value] || 'btn-default');
    label.style.padding = '15px';
    label.style.fontSize = '16px';
    label.style.cursor = 'pointer';
}

document.getElementById('photo').addEventListener('change', function(e) {
    var preview = document.getElementById('photo-preview');
    var img = document.getElementById('photo-preview-img');
    if (e.target.files && e.target.files[0]) {
        var reader = new FileReader();
        reader.onload = function(ev) {
            img.src = ev.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(e.target.files[0]);
    } else {
        preview.style.display = 'none';
    }
});

$(function() {
    if ($.fn.select2) {
        $('#asset_id').select2({ placeholder: '{{ __('custom::fom.wo_asset_placeholder') }}' });
    }
});
</script>
@endpush
