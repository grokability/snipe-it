@extends('custom::layouts.fom')

@section('title0')
    {{ __('custom::fom.sp_define_new') }}
@stop

@section('title')
    @yield('title0') @parent
@stop

@section('header_right')
    <a href="{{ route('fom.spare-parts.index') }}" class="btn btn-default pull-right">
        <i class="fas fa-arrow-left"></i> {{ __('custom::fom.sp_back') }}
    </a>
@stop

@section('content')

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-plus-circle"></i> {{ __('custom::fom.sp_define_new') }}</h3>
                </div>
                <form method="POST" action="{{ route('fom.spare-parts.store') }}">
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

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                                    <label>{{ __('custom::fom.sp_field_name') }} <span class="text-red">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('part_number') ? 'has-error' : '' }}">
                                    <label>{{ __('custom::fom.sp_field_number') }} <span class="text-red">*</span></label>
                                    <input type="text" name="part_number" class="form-control" value="{{ old('part_number') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>{{ __('custom::fom.sp_field_description') }}</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('custom::fom.sp_field_category') }}</label>
                                    <select name="category_id" class="form-control">
                                        <option value="">{{ __('custom::fom.sp_select') }}</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('custom::fom.sp_field_manufacturer') }}</label>
                                    <select name="manufacturer_id" class="form-control">
                                        <option value="">{{ __('custom::fom.sp_select') }}</option>
                                        @foreach($manufacturers as $mfr)
                                            <option value="{{ $mfr->id }}" {{ old('manufacturer_id') == $mfr->id ? 'selected' : '' }}>{{ $mfr->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('minimum_quantity') ? 'has-error' : '' }}">
                                    <label>{{ __('custom::fom.sp_field_min_stock') }} <span class="text-red">*</span></label>
                                    <input type="number" name="minimum_quantity" class="form-control" value="{{ old('minimum_quantity', 1) }}" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ __('custom::fom.sp_field_unit_cost') }}</label>
                                    <input type="number" name="unit_cost" class="form-control" value="{{ old('unit_cost') }}" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ __('custom::fom.sp_field_lead_time') }}</label>
                                    <input type="number" name="lead_time_days" class="form-control" value="{{ old('lead_time_days') }}" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('custom::fom.sp_field_supplier') }}</label>
                                    <select name="supplier_id" class="form-control">
                                        <option value="">{{ __('custom::fom.sp_select') }}</option>
                                        @foreach($suppliers as $sup)
                                            <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('custom::fom.sp_field_warehouse') }}</label>
                                    <select name="location_id" class="form-control">
                                        <option value="">{{ __('custom::fom.sp_select') }}</option>
                                        @foreach($locations as $loc)
                                            <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>{{ __('custom::fom.sp_field_models') }}</label>
                            <select name="asset_model_ids[]" class="form-control select2" multiple style="width: 100%;">
                                @foreach($assetModels as $model)
                                    <option value="{{ $model->id }}">{{ $model->name }} ({{ $model->model_number }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ __('custom::fom.sp_field_models_hint') }}</small>
                        </div>

                        <div class="form-group">
                            <label>{{ __('custom::fom.sp_field_initial_stock') }}</label>
                            <input type="number" name="quantity_on_hand" class="form-control" value="{{ old('quantity_on_hand', 0) }}" min="0" style="max-width: 200px;">
                        </div>

                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ __('custom::fom.btn_save') }}</button>
                        <a href="{{ route('fom.spare-parts.index') }}" class="btn btn-default">{{ __('custom::fom.btn_cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

@stop

@push('js')
<script nonce="{{ csrf_token() }}">
$(function() {
    if ($.fn.select2) {
        $('select.select2').select2();
    }
});
</script>
@endpush
