@extends('layouts/edit-form', [
    'createText' => trans('admin/statuslabels/table.create') ,
    'updateText' => trans('admin/statuslabels/table.update'),
    'helpTitle' => trans('admin/statuslabels/table.about'),
    'helpText' => trans('admin/statuslabels/table.info'),
    'formAction' => (isset($item->id)) ? route('statuslabels.update', ['statuslabel' => $item->id]) : route('statuslabels.store'),
])

{{-- Page content --}}
@section('content')
<style>
    .input-group-addon {
        width: 30px;
    }
</style>

@parent
@stop

@section('inputFields')

@include ('partials.forms.edit.name', ['translated_name' => trans('general.name')])

<!-- Label type -->
<div class="form-group{{ $errors->has('statuslabel_types') ? ' has-error' : '' }}">
    <label for="statuslabel_types" class="col-md-3 control-label">
        {{ trans('admin/statuslabels/table.status_type') }}
    </label>
    <div class="col-md-7 required">
        <x-input.select
            name="statuslabel_types"
            :options="$statuslabel_types"
            :selected="$item->getStatuslabelType()"
            style="width: 100%; min-width:400px"
            aria-label="statuslabel_types"
        />
        {!! $errors->first('statuslabel_types', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>
</div>

<!-- Chart color -->
<div class="form-group{{ $errors->has('color') ? ' has-error' : '' }}">
    <label for="color" class="col-md-3 control-label">{{ trans('admin/statuslabels/table.color') }}</label>
    <div class="col-md-9">
        <div class="input-group color">
            <input class="form-control col-md-10" maxlength="20" name="color" type="text" id="color" value="{{ old('color', $item->color) }}">
            <div class="input-group-addon"><i></i></div>
        </div><!-- /.input group -->
        {!! $errors->first('color', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
    </div>
</div>

@include ('partials.forms.edit.notes')

<!-- Show in Nav -->
<div class="form-group{{ $errors->has('notes') ? ' has-error' : '' }}">
    <div class="col-md-9 col-md-offset-3">
        <label class="form-control">
            <input type="checkbox" value="1" name="show_in_nav" id="show_in_nav" {{ old('show_in_nav', $item->show_in_nav) == '1' ? ' checked="checked"' : '' }}> {{ trans('admin/statuslabels/table.show_in_nav') }}
        </label>
    </div>
</div>

<!-- Set as Default -->
<div class="form-group{{ $errors->has('default_label') ? ' has-error' : '' }}">

    <div class="col-md-9 col-md-offset-3">
        <label class="form-control">
            <input type="checkbox" value="1" name="default_label" id="default_label" {{ old('default_label', $item->default_label) == '1' ? ' checked="checked"' : '' }}>
             {{ trans('admin/statuslabels/table.default_label') }}
        </label>
        <p class="help-block"> {{ trans('admin/statuslabels/table.default_label_help') }}</p>
    </div>
</div>

@stop

@section('moar_scripts')
    <!-- bootstrap color picker -->
    <script nonce="{{ csrf_token() }}">

        $(function() {
            $('.color').colorpicker({
                color: `{{ old('color', $item->color) ?: '#AA3399' }}`,
                format: 'hex'
            });
        });

    </script>

@stop
