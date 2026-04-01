@extends('layouts/default')

{{-- Page title --}}
@section('title')
    @if ($item->id)
        {{ trans('admin/printables/general.edit') }}
    @else
        {{ trans('admin/printables/general.create') }}
    @endif
    @parent
@stop

{{-- Page content --}}
@section('content')
<div class="row">
    <div class="col-lg-10 col-lg-offset-1">
        <form id="create-form" class="form-horizontal" method="POST"
              action="{{ $item->id ? route('printables.update', $item->id) : route('printables.store') }}"
              autocomplete="off">
            @csrf
            @if ($item->id)
                @method('PUT')
            @endif

            <div class="box box-default">
                <div class="box-header with-border">
                    <div class="col-md-12 box-title text-right" style="padding: 0; margin: 0;">
                        <div class="col-md-9 text-left">
                            @if ($item->id)
                                <h2 class="box-title" style="padding-top: 8px; padding-bottom: 7px;">
                                    {{ $item->name }}
                                </h2>
                            @endif
                        </div>
                        <div class="col-md-3 text-right" style="padding-right: 10px;">
                            <button type="submit" class="btn btn-success pull-right">
                                <x-icon type="checkmark"/>
                                {{ trans('general.save') }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="box-body">
                    <div style="padding-top: 30px;">

                        {{-- Name --}}
                        <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                            <label for="name" class="col-md-3 control-label">{{ trans('admin/printables/general.name') }} <span class="required">*</span></label>
                            <div class="col-md-7">
                                <input type="text" name="name" id="name" class="form-control"
                                       value="{{ old('name', $item->name) }}"
                                       placeholder="{{ trans('admin/printables/general.name') }}"
                                       maxlength="255" required>
                                {!! $errors->first('name', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                        {{-- Assigned Categories --}}
                        <div class="form-group {{ $errors->has('category_ids') ? 'has-error' : '' }}">
                            <label class="col-md-3 control-label">{{ trans('admin/printables/general.categories') }}</label>
                            <div class="col-md-7">
                                @foreach ($categories as $category)
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox"
                                                   name="category_ids[]"
                                                   value="{{ $category->id }}"
                                                   {{ in_array($category->id, old('category_ids', $item->id ? $item->categories->pluck('id')->toArray() : [])) ? 'checked' : '' }}
                                            >
                                            {{ $category->name }}
                                        </label>
                                    </div>
                                @endforeach
                                @if ($categories->isEmpty())
                                    <p class="text-muted">{{ trans('general.no_results') }}</p>
                                @endif
                                <p class="help-block">{{ trans('admin/printables/general.categories_help') }}</p>
                                {!! $errors->first('category_ids', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                        {{-- Template Content --}}
                        <div class="form-group {{ $errors->has('content') ? 'has-error' : '' }}">
                            <label for="content" class="col-md-3 control-label">{{ trans('admin/printables/general.content') }} <span class="required">*</span></label>
                            <div class="col-md-9">

                                {{-- Variable Helper --}}
                                <div class="well well-sm" style="margin-bottom: 10px;">
                                    <strong>{{ trans('admin/printables/general.available_variables') }}</strong>
                                    <p class="help-block" style="margin-bottom: 8px;">{{ trans('admin/printables/general.variables_help') }}</p>
                                    <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                        @foreach ($variables as $placeholder => $label)
                                            <button type="button"
                                                    class="btn btn-xs btn-default variable-btn"
                                                    data-variable="{{ $placeholder }}"
                                                    title="{{ $label }}">
                                                <code>{{ $placeholder }}</code>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                <textarea name="content" id="content" class="form-control" rows="20"
                                          style="font-family: monospace; font-size: 13px;"
                                          placeholder="<p>Asset Tag: {asset_tag}</p>" required>{{ old('content', $item->content) }}</textarea>
                                <p class="help-block">{{ trans('admin/printables/general.content') }}: {{ trans('admin/printables/general.variables_help') }}</p>
                                {!! $errors->first('content', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}

                                {{-- Live Preview --}}
                                <div style="margin-top: 15px;">
                                    <strong>{{ trans('admin/printables/general.preview') }}</strong>
                                    <div id="printable-preview"
                                         style="border: 1px solid #ccc; padding: 16px; margin-top: 8px; min-height: 120px; background: #fff; border-radius: 4px;">
                                        <span class="text-muted">{{ trans('admin/printables/general.preview') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="text-right" style="margin-top: 10px;">
                <a href="{{ route('printables.index') }}" class="btn btn-default">{{ trans('button.cancel') }}</a>
                <button type="submit" class="btn btn-success">
                    <x-icon type="checkmark"/>
                    {{ trans('general.save') }}
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@section('moar_scripts')
<script nonce="{{ csrf_token() }}">
    document.addEventListener('DOMContentLoaded', function () {
        var textarea = document.getElementById('content');
        var preview  = document.getElementById('printable-preview');

        function updatePreview() {
            preview.innerHTML = textarea.value || '<span class="text-muted">{{ trans("admin/printables/general.preview") }}</span>';
        }

        textarea.addEventListener('input', updatePreview);
        updatePreview();

        // Variable insertion buttons
        document.querySelectorAll('.variable-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var variable = btn.getAttribute('data-variable');
                var start    = textarea.selectionStart;
                var end      = textarea.selectionEnd;
                var before   = textarea.value.substring(0, start);
                var after    = textarea.value.substring(end);
                textarea.value = before + variable + after;
                textarea.selectionStart = textarea.selectionEnd = start + variable.length;
                textarea.focus();
                updatePreview();
            });
        });
    });
</script>
@stop
