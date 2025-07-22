<div class="form-group {{ $errors->has('email') ? ' has-error' : '' }}">
    <label for="email" class="col-md-3 col-xs-12 control-label">{{ trans('admin/suppliers/table.email') }}</label>
    <div class="col-md-8 col-xs-12">
        <input type="email" name="email" id="email" value="{{ old('email', $item->email ?? null) }}" class="form-control"  maxlength="191" style="width:100%; display:flex;">
        {!! $errors->first('email', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>
</div>
