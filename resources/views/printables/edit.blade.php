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
                                    <label for="variable-insert-select" class="sr-only">{{ trans('admin/printables/general.available_variables') }}</label>
                                    <select id="variable-insert-select" class="form-control" data-placeholder="{{ trans('general.select') }} {{ trans('admin/printables/general.available_variables') }}">
                                        <option value="">{{ trans('general.select') }} {{ trans('admin/printables/general.available_variables') }}</option>
                                        @foreach ($variables as $placeholder => $label)
                                            <option value="{{ $placeholder }}">{{ $placeholder }} - {{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <p class="help-block" style="margin-top: 8px; margin-bottom: 0;">
                                        Use <code>&#123;&#123; variable &#125;&#125;</code> for variables, <code>&#123;% if condition %&#125;</code> blocks for conditionals, and <code>??</code> for fallbacks.
                                    </p>
                                    <h5 id="printable_conditional_example" class="remember-toggle" style="margin-top: 8px; margin-bottom: 0;">
                                        <x-icon type="caret-down" class="fa-fw" id="toggle-arrow-printable_conditional_example" />
                                        Conditional example
                                    </h5>
                                    <div class="toggle-content-printable_conditional_example" style="margin-top: 6px; white-space: pre-wrap;">
<code>&#123;% if checked_out_user.first_name %&#125;
    Hello &#123;&#123; checked_out_user.first_name &#125;&#125;,
&#123;% elseif assigned_to %&#125;
    Hello &#123;&#123; assigned_to &#125;&#125;,
&#123;% else %&#125;
    Hello,
&#123;% endif %&#125;</code>
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
        var variableSelect = document.getElementById('variable-insert-select');

        function updatePreview() {
            if (!textarea.value) {
            preview.innerHTML = '<span class="text-muted">{{ trans("admin/printables/general.preview") }}</span>';
            } else {
                // Create borders around variables and conditionals for better visibility in the preview
                var rendered = textarea.value.replace(/@{{\s*(\w+(\.\w+)*)\s*}}/g, function(match, variable) {
                    return '<span style="background-color: #f0f0f0; border: 1px solid #ccc; padding: 2px 4px; font-size: 12px; font-family: monospace;">' + variable + '</span>';
                }).replace(/{%\s*(if|elseif|else|endif)([^%]*)%}/g, function(match, directive, condition) {
                    return '<span style="background-color: #e8f4ff; border: 1px solid #b3d7ff; padding: 2px 4px; font-size: 12px; font-family: monospace;">' + directive + condition + '</span>';
                });
                preview.innerHTML = rendered;
            }
        }

        textarea.addEventListener('input', updatePreview);
        updatePreview();

        function insertVariable(variable) {
            if (!variable) {
                return;
            }

                var start    = textarea.selectionStart;
                var end      = textarea.selectionEnd;
                var before   = textarea.value.substring(0, start);
                var after    = textarea.value.substring(end);
                textarea.value = before + variable + after;
                textarea.selectionStart = textarea.selectionEnd = start + variable.length;
                textarea.focus();
                updatePreview();
        }

        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
            var $select = window.jQuery(variableSelect);
            $select.select2({
                width: '100%',
                placeholder: variableSelect.getAttribute('data-placeholder'),
                allowClear: true
            });

            $select.on('select2:select', function () {
                insertVariable($select.val());
                $select.val('').trigger('change.select2');
            });
        } else {
            variableSelect.addEventListener('change', function () {
                insertVariable(variableSelect.value);
                variableSelect.value = '';
            });
        }
    });
</script>
@stop
