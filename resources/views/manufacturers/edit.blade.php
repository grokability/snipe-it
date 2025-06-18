@extends('layouts/edit-form', [
    'createText' => trans('admin/manufacturers/table.create') ,
    'updateText' => trans('admin/manufacturers/table.update'),
    'helpTitle' => trans('admin/manufacturers/table.about_manufacturers_title'),
    'helpText' => trans('admin/manufacturers/table.about_manufacturers_text'),
    'formAction' => (isset($item->id)) ? route('manufacturers.update', ['manufacturer' => $item->id]) : route('manufacturers.store'),
])


{{-- Page content --}}
@section('inputFields')
@include ('partials.forms.edit.name', ['translated_name' => trans('admin/manufacturers/table.name')])
    <!-- URL -->
    <div class="form-group {{ $errors->has('url') ? ' has-error' : '' }}">
        <label for="url" class="col-md-3 control-label">{{ trans('general.url') }}
        </label>
        <div class="col-md-6">
            <input class="form-control" type="text" name="url" id="url" value="{{ old('url', $item->url) }}" />
            {!! $errors->first('url', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- Support URL -->
    <div class="form-group {{ $errors->has('support_url') ? ' has-error' : '' }}">
        <label for="support_url" class="col-md-3 control-label">{{ trans('admin/manufacturers/table.support_url') }}
        </label>
        <div class="col-md-6">
            <input class="form-control" type="url" name="support_url" id="support_url" value="{{ old('support_url', $item->support_url) }}" />
            {!! $errors->first('support_url', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- Warranty Lookup URL -->
    <div class="form-group {{ $errors->has('warranty_lookup_url') ? ' has-error' : '' }}">
        <label for="support_url" class="col-md-3 control-label">{{ trans('admin/manufacturers/table.warranty_lookup_url') }}
        </label>
        <div class="col-md-6">
            <input class="form-control" type="url" name="warranty_lookup_url" id="warranty_lookup_url" value="{{ old('warranty_lookup_url', $item->warranty_lookup_url) }}" />
            <p class="help-block">{!! trans('admin/manufacturers/message.support_url_help') !!}</p>
            {!! $errors->first('warranty_lookup_url', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- Support Phone -->
    <div class="form-group {{ $errors->has('support_phone') ? ' has-error' : '' }}">
        <label for="support_phone" class="col-md-3 control-label">{{ trans('admin/manufacturers/table.support_phone') }}
        </label>
        <div class="col-md-6">
            <input class="form-control" type="text" name="support_phone" id="support_phone" value="{{ old('support_phone', $item->support_phone) }}" />
            {!! $errors->first('support_phone', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- Support Email -->
    <div class="form-group {{ $errors->has('support_email') ? ' has-error' : '' }}">
        <label for="support_email" class="col-md-3 control-label">{{ trans('admin/manufacturers/table.support_email') }}
        </label>
        <div class="col-md-6">
            <input class="form-control" type="email" name="support_email" id="support_email" value="{{ old('support_email', $item->support_email) }}" />
            {!! $errors->first('support_email', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

@include ('partials.forms.edit.image-upload', ['image_path' => app('manufacturers_upload_path')])

<div class="form-group{!! $errors->has('notes') ? ' has-error' : '' !!}">
    <label for="notes" class="col-md-3 control-label">{{ trans('general.notes') }}</label>
    <div class="col-md-8">
        <x-input.textarea
                name="notes"
                id="notes"
                :value="old('notes', $item->notes)"
                placeholder="{{ trans('general.placeholders.notes') }}"
                aria-label="notes"
                rows="5"
        />
        {!! $errors->first('notes', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>
</div>



@stop
