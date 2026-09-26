@extends('layouts/default')

@section('title')
    @if ($item->id)
        {{ trans('documents.general.edit_template') }}
    @else
        {{ trans('documents.general.create_template') }}
    @endif
    @parent
@stop

@push('css')
    <style>
        .document-template-form .checkbox-inline { padding-left: 32px; margin-right: 8px; }
        .document-template-form select, .document-template-form select option {
            background-color: var(--box-bg, #fff) !important;
            color: var(--color-fg, #373636) !important;
            color-scheme: inherit;
        }
        .terms-toolbar { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 8px; }
        .terms-preview { border: 1px solid #d2d6de; padding: 16px; min-height: 220px; overflow-wrap: anywhere; }
        .terms-preview table { width: 100%; border-collapse: collapse; }
        .terms-preview td, .terms-preview th { border: 1px solid #ddd; padding: 6px; }
        .document-template-form details { margin: 20px 0; }
        .document-template-form summary { cursor: pointer; font-weight: bold; margin-bottom: 12px; padding: 10px; background: var(--table-stripe-bg, #f5f5f5); color: var(--color-fg, #373636); }
        .document-template-form summary::before { content: "+ "; }
        .document-template-form details[open] > summary::before { content: "− "; }
        .placeholder-list { max-height: 440px; overflow-y: auto; }
        .placeholder-list button { white-space: normal; text-align: left; width: 100%; margin: 3px 0; }
        .placeholder-list code { display: block; overflow-wrap: anywhere; }
        .document-setting input[type="checkbox"] { margin-top: 10px; }
        .document-template-form label.document-checkbox { display: flex; align-items: center; gap: 8px; margin-top: 8px; }
    </style>
@endpush

@section('content')
    @php $terms = app(\App\Services\Documents\TemplateService::class)->terms($item); @endphp

    <div class="row">
        <div class="col-md-8 col-md-push-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        @if ($item->id) {{ $item->name }} @else {{ trans('documents.general.create_template') }} @endif
                    </h3>
                </div>
                <div class="box-body">

                    <p>{{ trans('documents.general.layout_help') }}</p>
                    <form method="POST"
                          action="{{ $item->id ? route('documents.templates.update', $item) : route('documents.templates.store') }}"
                          enctype="multipart/form-data" accept-charset="UTF-8" class="form-horizontal document-template-form">
                        {{ csrf_field() }}
                        <input type="hidden" name="layout_submitted" value="1">

                        <x-form.row :label="trans('general.name')" :$item name="name" required="required" />

                        <x-form.row :label="trans('documents.general.type')" name="type" input_div_class="col-md-7 required">
                            <x-slot:input>
                                <x-input.select name="type" style="width: 100%;" :options="['checkout' => 'Checkout', 'handover' => 'Handover', 'return' => 'Return']"
                                                :selected="old('type', $item->type)" required="required" aria-label="type" />
                                <x-form.error name="type" />
                            </x-slot:input>
                        </x-form.row>

                        <h3 id="agreement-editor">{{ trans('documents.general.eula') }}</h3>
                        <p>{{ trans('documents.general.terms_help') }}</p>
                        <div class="form-group">
                            <div class="col-md-12">
                                <input type="hidden" name="eula_enabled" value="0">
                                <label class="document-checkbox"><input id="eula_enabled" type="checkbox" name="eula_enabled" value="1" @checked(old('eula_enabled', $item->exists ? $terms['eula_enabled'] : true))> {{ trans('documents.general.include_terms') }}</label>
                            </div>
                        </div>
                        <div id="eula-fields">
                            <x-form.row :label="trans('documents.general.eula_title')" name="eula_title">
                                <x-slot:input>
                                    <input id="eula_title" name="eula_title" class="form-control placeholder-target" maxlength="191" value="{{ old('eula_title', $item->exists ? $terms['eula_title'] : trans('documents.general.terms_title_placeholder')) }}" placeholder="{{ trans('documents.general.terms_title_placeholder') }}">
                                    <x-form.error name="eula_title" />
                                </x-slot:input>
                            </x-form.row>
                            <x-form.row :label="trans('documents.general.eula_version')" name="eula_version">
                                <x-slot:input>
                                    <input id="eula_version" name="eula_version" class="form-control" maxlength="40" value="{{ old('eula_version', $item->exists ? $terms['eula_version'] : '1.0') }}" aria-describedby="revision-help">
                                    <p id="revision-help" class="help-block">{{ trans('documents.general.terms_revision_help') }}</p>
                                    <x-form.error name="eula_version" />
                                </x-slot:input>
                            </x-form.row>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <label for="eula_format">{{ trans('documents.general.format') }}</label>
                                    <select id="eula_format" name="eula_format" class="form-control" style="max-width: 220px; margin-bottom: 12px;">
                                        <option value="markdown" @selected(old('eula_format', $terms['eula_format']) === 'markdown')>{{ trans('documents.general.markdown') }}</option>
                                        <option value="plain" @selected(old('eula_format', $terms['eula_format']) === 'plain')>{{ trans('documents.general.plain_text') }}</option>
                                    </select>
                                    <label for="eula_body">{{ trans('documents.general.eula_body') }}</label>
                                    <div class="terms-toolbar" id="markdown-toolbar" role="group" aria-label="{{ trans('documents.general.markdown') }}">
                                        @foreach (['heading', 'bold', 'italic', 'bullets', 'numbers', 'quote', 'table'] as $format)
                                            <button type="button" class="btn btn-sm btn-default" data-markdown="{{ $format }}">{{ trans('documents.general.markdown_'.$format) }}</button>
                                        @endforeach
                                    </div>
                                    <div class="terms-toolbar">
                                        <label for="eula-placeholder">{{ trans('documents.general.placeholder_palette') }}</label>
                                        <select id="eula-placeholder" class="form-control" style="max-width: 100%;">
                                            @foreach (\App\Services\Documents\PlaceholderRegistry::available() as $key => $description)
                                                @if ($key !== 'company.logo')<option value="{{ $key }}">{{ $description }}</option>@endif
                                            @endforeach
                                        </select>
                                        <button id="insert-eula-placeholder" type="button" class="btn btn-default btn-sm">{{ trans('documents.custom.insert_placeholder') }}</button>
                                    </div>
                                    <div class="terms-toolbar">
                                        <button id="terms-write" type="button" class="btn btn-sm btn-primary" aria-pressed="true">{{ trans('documents.general.write') }}</button>
                                        <button id="terms-preview-button" type="button" class="btn btn-sm btn-default" aria-pressed="false" aria-controls="terms-preview">{{ trans('documents.general.preview_terms') }}</button>
                                    </div>
                                    <textarea class="form-control placeholder-target" name="eula_body" id="eula_body" rows="14" maxlength="60000" aria-describedby="markdown-help" placeholder="{{ trans('documents.general.terms_placeholder') }}">{{ old('eula_body', $terms['eula_body']) }}</textarea>
                                    <div id="terms-preview" class="terms-preview" role="region" aria-label="{{ trans('documents.general.preview_terms') }}" aria-live="polite" hidden></div>
                                    <p id="markdown-help" class="help-block">{{ trans('documents.general.markdown_help') }}</p>
                                    <x-form.error name="eula_body" />
                                </div>
                            </div>
                        </div>
                        <p class="help-block">{{ trans('documents.general.terms_snapshot_help') }}</p>
                        <details @if ($errors->hasAny(['language', 'page_size', 'orientation', 'signature_roles', 'asset_columns', 'header_config.title', 'footer_config.text'])) open @endif>
                            <summary>{{ trans('documents.general.layout_options') }}</summary>
                        <x-form.row :label="trans('general.language')" :$item name="language" placeholder="en / fa" input_div_class="col-md-3" />

                        <x-form.row :label="trans('documents.general.page_size')" name="page_size" input_div_class="col-md-3">
                            <x-slot:input>
                                <x-input.select name="page_size" style="width: 100%;" :options="['A4' => 'A4', 'LETTER' => 'Letter', 'LEGAL' => 'Legal']"
                                                :selected="old('page_size', $item->page_size)" aria-label="page_size" />
                            </x-slot:input>
                        </x-form.row>

                        <x-form.row :label="trans('documents.general.orientation')" name="orientation" input_div_class="col-md-3">
                            <x-slot:input>
                                <x-input.select name="orientation" style="width: 100%;" :options="['P' => 'Portrait', 'L' => 'Landscape']"
                                                :selected="old('orientation', $item->orientation)" aria-label="orientation" />
                            </x-slot:input>
                        </x-form.row>

                        <x-form.row :label="trans('documents.general.active')" name="active">
                            <x-slot:input>
                                <input type="hidden" name="active" value="0">
                                <label class="document-checkbox"><input id="active" type="checkbox" name="active" value="1" @checked(old('active', $item->exists ? $item->active : true))> {{ trans('documents.general.active') }}</label>
                            </x-slot:input>
                        </x-form.row>
                        </details>
                        <details id="settings-typography" @if ($errors->any()) open @endif>
                            <summary>{{ trans('documents.custom.typography') }}</summary>
                            <x-document-setting :item="$item" section="header_config" field="font_family" type="select" default="dejavusans" :options="\App\Services\Documents\DocumentFonts::FAMILIES" />
                            <div class="form-group" id="custom-font-field"><label class="col-md-4 control-label" for="custom_font">{{ trans('documents.custom.custom_font') }}</label><div class="col-md-8">
                                <input type="file" id="custom_font" name="custom_font" accept=".ttf,font/ttf">
                                @if ($item->header_config['custom_font']['name'] ?? null)<p>{{ $item->header_config['custom_font']['name'] }}</p>@endif
                                <p class="help-block">{{ trans('documents.custom.font_help') }}</p><x-form.error name="custom_font" />
                            </div></div>
                            <x-document-setting :item="$item" section="header_config" field="font_size" type="number" :default="9" min="6" max="30" step="0.5" />
                            <x-document-setting :item="$item" section="header_config" field="heading_size" type="number" :default="14" min="6" max="30" step="0.5" />
                            <x-document-setting :item="$item" section="header_config" field="header_size" type="number" :default="9" min="6" max="30" step="0.5" />
                            <x-document-setting :item="$item" section="header_config" field="line_height" type="number" :default="1.25" min="1" max="2" step="0.05" />
                            <x-document-setting :item="$item" section="header_config" field="alignment" type="select" default="start" :options="['start' => trans('documents.custom.start'), 'left' => trans('documents.custom.left'), 'right' => trans('documents.custom.right'), 'center' => trans('documents.custom.center'), 'justify' => trans('documents.custom.justify')]" />
                            <x-document-setting :item="$item" section="header_config" field="margin_top" type="number" :default="34" min="10" max="60" />
                            <x-document-setting :item="$item" section="header_config" field="margin_bottom" type="number" :default="20" min="10" max="60" />
                            <x-document-setting :item="$item" section="header_config" field="margin_left" type="number" :default="15" min="10" max="60" />
                            <x-document-setting :item="$item" section="header_config" field="margin_right" type="number" :default="15" min="10" max="60" />
                            <p class="help-block">{{ trans('documents.custom.margin_help') }}</p>
                        </details>
                        <details id="settings-header_dates" @if ($errors->any()) open @endif>
                            <summary>{{ trans('documents.custom.header_dates') }}</summary>
                            <x-document-setting :item="$item" section="header_config" field="title" type="text" max="191" />
                            <x-document-setting :item="$item" section="header_config" field="show_header" type="checkbox" :default="true" />
                            <x-document-setting :item="$item" section="header_config" field="show_logo" type="checkbox" :default="true" />
                            <x-document-setting :item="$item" section="header_config" field="show_company" type="checkbox" :default="true" />
                            <x-document-setting :item="$item" section="header_config" field="show_number" type="checkbox" :default="true" />
                            <x-document-setting :item="$item" section="header_config" field="show_date" type="checkbox" :default="true" />
                            <x-document-setting :item="$item" section="header_config" field="show_rule" type="checkbox" :default="true" />
                            <x-document-setting :item="$item" section="header_config" field="date_calendar" type="select" default="both" :options="['gregorian' => trans('documents.custom.gregorian'), 'jalali' => trans('documents.custom.jalali'), 'both' => trans('documents.custom.both')]" />
                            <x-document-setting :item="$item" section="header_config" field="date_format" type="select" default="Y-m-d" :options="['Y-m-d' => 'YYYY-MM-DD', 'Y/m/d' => 'YYYY/MM/DD', 'd/m/Y' => 'DD/MM/YYYY', 'm/d/Y' => 'MM/DD/YYYY', 'd.m.Y' => 'DD.MM.YYYY']" />
                            <x-document-setting :item="$item" section="header_config" field="left_text" type="textarea" />
                            <x-document-setting :item="$item" section="header_config" field="right_text" type="textarea" />
                        </details>
                        <details id="settings-sections" @if ($errors->any()) open @endif>
                            <summary>{{ trans('documents.custom.sections') }}</summary>
                        <x-form.row :label="trans('documents.general.asset_columns')" name="asset_columns">
                            <x-slot:input>
                                @php $columns = old('asset_columns', $item->asset_columns ?? app(\App\Services\Documents\TemplateService::class)->defaultAssetColumns()); @endphp
                                @foreach (['asset_tag', 'name', 'model', 'manufacturer', 'serial', 'status', 'condition'] as $column)
                                    <label class="checkbox-inline"><input type="checkbox" name="asset_columns[]" value="{{ $column }}" @checked(in_array($column, $columns))> {{ trans('documents.columns.'.$column) }}</label>
                                @endforeach
                                <x-form.error name="asset_columns" />
                            </x-slot:input>
                        </x-form.row>

                            <x-document-setting :item="$item" section="header_config" field="show_title" type="checkbox" :default="true" />
                            <x-document-setting :item="$item" section="header_config" field="show_assignee" type="checkbox" :default="true" />
                            <x-document-setting :item="$item" section="header_config" field="show_assets" type="checkbox" :default="true" />
                            <x-document-setting :item="$item" section="header_config" field="show_notes" type="checkbox" :default="true" />
                            <x-document-setting :item="$item" section="header_config" field="show_provenance" type="checkbox" :default="true" />
                            <x-document-setting :item="$item" section="header_config" field="show_revision" type="checkbox" :default="true" />
                            <x-document-setting :item="$item" section="header_config" field="terms_position" type="select" default="after_assets" :options="['before_assets' => trans('documents.custom.before_assets'), 'after_assets' => trans('documents.custom.after_assets')]" />
                        </details>
                        <details id="settings-footer" @if ($errors->any()) open @endif>
                            <summary>{{ trans('documents.custom.footer') }}</summary>
                            <x-document-setting :item="$item" section="footer_config" field="show_footer" type="checkbox" :default="true" />
                            <x-document-setting :item="$item" section="footer_config" field="show_pages" type="checkbox" :default="true" />
                            <x-document-setting :item="$item" section="footer_config" field="text" type="textarea" max="2000" />
                            <x-document-setting :item="$item" section="footer_config" field="font_size" type="number" :default="7" min="6" max="20" step="0.5" />
                            <x-document-setting :item="$item" section="footer_config" field="alignment" type="select" default="left" :options="['left' => trans('documents.custom.left'), 'center' => trans('documents.custom.center'), 'right' => trans('documents.custom.right')]" />
                        </details>
                        <details id="settings-signature_design" @if ($errors->any()) open @endif>
                            <summary>{{ trans('documents.custom.signature_design') }}</summary>
                        <x-form.row :label="trans('documents.general.signature_config')" name="signature_config" input_div_class="col-md-7">
                            <x-slot:input>
                                @php
                                    $roles = old('signature_roles', $item->signature_config['roles'] ?? ['employee', 'it_representative']);
                                @endphp
                                <x-form.error name="signature_roles" />
                                @foreach (['employee', 'it_representative', 'manager', 'custom'] as $role)
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="signature_roles[]" value="{{ $role }}" @if (in_array($role, (array) $roles)) checked @endif>
                                        {{ ucfirst(str_replace('_', ' ', $role)) }}
                                    </label>
                                @endforeach
                            </x-slot:input>
                        </x-form.row>

                            <p class="help-block">{{ trans('documents.custom.signature_help') }}</p>
                            @foreach (['employee', 'it_representative', 'manager', 'custom'] as $role)
                            <div class="form-group"><label class="col-md-4 control-label" for="signature-label-{{ $role }}">{{ trans('documents.custom.role_'.$role) }}</label><div class="col-md-8">
                                <input class="form-control" id="signature-label-{{ $role }}" name="signature_config[labels][{{ $role }}]" maxlength="100" required value="{{ old('signature_config.labels.'.$role, $item->signature_config['labels'][$role] ?? trans('documents.custom.role_'.$role)) }}">
                                <x-form.error :name="'signature_config.labels.'.$role" />
                            </div></div>
                            @endforeach
                            <x-document-setting :item="$item" section="signature_config" field="heading" type="text" />
                            <x-document-setting :item="$item" section="signature_config" field="instructions" type="textarea" max="2000" />
                            <x-document-setting :item="$item" section="signature_config" field="columns" type="select" :default="2" :options="[1 => '1', 2 => '2', 3 => '3', 4 => '4']" />
                            <x-document-setting :item="$item" section="signature_config" field="space" type="number" :default="12" min="5" max="40" />
                            <x-document-setting :item="$item" section="signature_config" field="show_name" type="checkbox" :default="true" />
                            <x-document-setting :item="$item" section="signature_config" field="show_date" type="checkbox" :default="true" />
                            <x-document-setting :item="$item" section="signature_config" field="show_line" type="checkbox" :default="true" />
                        </details>
                        <details id="optional-introduction" @if (old('body', $item->body) || $errors->has('body')) open @endif>
                            <summary>{{ trans('documents.general.body') }}</summary>
                            <p class="help-block">{{ trans('documents.general.introduction_help') }}</p>
                        <x-form.row :label="trans('documents.general.body')" name="body" input_div_class="col-md-7">
                            <x-slot:input>
                                <textarea class="form-control placeholder-target" name="body" id="body" rows="4">{{ old('body', $item->body) }}</textarea>
                                <p class="help-block">{{ trans('documents.general.body_help') }}</p>
                                <p class="help-block">{{ trans('documents.general.body_format_help') }}</p>
                                <x-form.error name="body" />
                            </x-slot:input>
                        </x-form.row>

                        </details>

                        @can($item->exists ? 'update' : 'create', $item->exists ? $item : \App\Models\DocumentTemplate::class)
                            @include('partials.forms.edit.submit')
                        @endcan

                    </form>

                    @if ($item->id)
                        <p class="text-muted"><em>{{ trans('documents.general.working_copy_note') }}</em></p>
                    @endif

                </div>
            </div>
        </div>

        <div class="col-md-4 col-md-pull-8">
            <div class="box box-default"><div class="box-header with-border"><h3 class="box-title">{{ trans('documents.custom.customize') }}</h3></div><div class="box-body">
                <a class="btn btn-default btn-sm" href="#agreement-editor">{{ trans('documents.general.eula') }}</a>
                @foreach (['typography', 'header_dates', 'sections', 'footer', 'signature_design'] as $section)
                    <a class="btn btn-default btn-sm section-shortcut" href="#settings-{{ $section }}" style="margin: 3px 0;">{{ trans('documents.custom.'.$section) }}</a>
                @endforeach
            </div></div>

            <div class="box box-default">
                <div class="box-header with-border"><h3 class="box-title">{{ trans('documents.custom.placeholder_guide') }}</h3></div>
                <div class="box-body">
                    <p>{{ trans('documents.custom.placeholder_help') }}</p>
                    <p class="small">{{ trans('documents.custom.date_placeholder_help') }}</p>
                    <label for="placeholder-search">{{ trans('documents.custom.find_placeholder') }}</label>
                    <input id="placeholder-search" class="form-control" type="search">
                    <p id="placeholder-target-label" class="help-block" aria-live="polite"></p>
                    <div class="placeholder-list">
                        @foreach (\App\Services\Documents\PlaceholderRegistry::available() as $key => $description)
                            @if ($key !== 'company.logo')
                            <button type="button" class="btn btn-default btn-sm ph-insert" data-key="{{ $key }}">{{ $description }}<code>{{ str_starts_with($key, '@loop.') ? '{'.'{'.substr($key, 6).'}'.'}' : '{'.'{'.$key.'}'.'}' }}</code></button>
                            @endif
                        @endforeach
                    </div>
                    <p class="small">{{ trans('documents.custom.loop_help') }}</p>
                    <button type="button" class="btn btn-default btn-sm" id="ph-insert-loop">{{ trans('documents.general.asset_loop') }}</button>
                </div>
            </div>

            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title">{{ trans('documents.general.terms_topics') }}</h3></div>
                <div class="box-body">
                    <p>{{ trans('documents.general.terms_care') }}</p>
                    <p>{{ trans('documents.general.terms_security') }}</p>
                    <p>{{ trans('documents.general.terms_incidents') }}</p>
                    <p>{{ trans('documents.general.terms_return') }}</p>
                </div>
            </div>
        </div>
    </div>
@stop

@section('moar_scripts')
    <script nonce="{{ csrf_token() }}">
        $(function () {
            var terms = document.getElementById('eula_body');
            var previewRequest;
            function showWrite() {
                $('#terms-preview').prop('hidden', true);
                $('#eula_body').show();
                $('#terms-write').attr('aria-pressed', 'true').addClass('btn-primary').removeClass('btn-default');
                $('#terms-preview-button').attr('aria-pressed', 'false').removeClass('btn-primary').addClass('btn-default');
            }
            function updateTerms() {
                var enabled = $('#eula_enabled').is(':checked');
                $('#eula-fields').toggle(enabled);
                $('#eula_title, #eula_version, #eula_body').prop('required', enabled);
                $('#markdown-toolbar, #markdown-help').toggle($('#eula_format').val() === 'markdown');
                showWrite();
            }
            $('#eula_enabled, #eula_format').on('change', updateTerms);
            updateTerms();
            terms.addEventListener('invalid', showWrite);
            $('#terms-write').on('click', showWrite);
            $('#terms-preview-button').on('click', function () {
                if (previewRequest) { previewRequest.abort(); }
                $('#terms-preview').prop('hidden', false).text(@json(trans('general.loading')));
                $('#eula_body').hide();
                $('#terms-write').attr('aria-pressed', 'false').removeClass('btn-primary').addClass('btn-default');
                $(this).attr('aria-pressed', 'true').addClass('btn-primary').removeClass('btn-default');
                previewRequest = $.ajax({
                    url: @json(route('documents.templates.markdown-preview')),
                    method: 'POST', contentType: 'application/json',
                    headers: {'X-CSRF-TOKEN': @json(csrf_token())},
                    data: JSON.stringify({text: terms.value, format: $('#eula_format').val(), header_config: {date_calendar: $('#header_config_date_calendar').val(), date_format: $('#header_config_date_format').val()}})
                }).done(function (data) {
                    if (typeof data.html === 'string') {
                        $('#terms-preview').html(data.html);
                    } else {
                        $('#terms-preview').text(@json(trans('documents.general.preview_failed')));
                    }
                }).fail(function (xhr, status) {
                    if (status !== 'abort') { $('#terms-preview').text(@json(trans('documents.general.preview_failed'))); }
                });
            });
            $('[data-markdown]').on('click', function () {
                showWrite();
                var start = terms.selectionStart, end = terms.selectionEnd;
                var selected = terms.value.substring(start, end);
                var action = $(this).data('markdown');
                var label = $(this).text().trim();
                var text = selected || label;
                var value;
                if (action === 'bold' || action === 'italic') {
                    var marker = action === 'bold' ? '**' : '*';
                    value = marker + text + marker;
                } else if (action === 'table') {
                    value = '\n| ' + @json(trans('general.name')) + ' | ' + @json(trans('documents.general.details')) + ' |\n| --- | --- |\n|  |  |\n';
                } else {
                    var prefixes = {heading: '## ', bullets: '- ', numbers: '1. ', quote: '> '};
                    value = (start > 0 && terms.value[start - 1] !== '\n' ? '\n' : '') + text.split('\n').map(function (line, index) {
                        return (action === 'numbers' ? (index + 1) + '. ' : prefixes[action]) + line;
                    }).join('\n') + '\n';
                }
                insertAtCursor(terms, value);
            });
            $('.document-template-form').on('submit', showWrite);
            function insertAtCursor(input, value) {
                var start = input.selectionStart;
                var end = input.selectionEnd;
                input.value = input.value.substring(0, start) + value + input.value.substring(end);
                input.focus();
                input.setSelectionRange(start + value.length, start + value.length);
            }
            $('.section-shortcut').on('click', function () { $(this.getAttribute('href')).prop('open', true); });
            var placeholderTarget = terms;
            function targetLabel() {
                var label = $('label[for="' + placeholderTarget.id + '"]').first().text().trim();
                $('#placeholder-target-label').text(@json(trans('documents.custom.inserting_into')) + ' ' + label);
            }
            $('.placeholder-target').on('focus', function () { placeholderTarget = this; targetLabel(); });
            targetLabel();
            function token(key) {
                return key.indexOf('@loop.') === 0
                    ? '@{{#assets}}' + '\n' + '{' + '{' + key.substring(6) + '}' + '}\n' + '@{{/assets}}'
                    : '{' + '{' + key + '}' + '}';
            }
            function insertPlaceholder(value) {
                if (placeholderTarget === terms) { showWrite(); }
                $(placeholderTarget).parents('details').prop('open', true);
                insertAtCursor(placeholderTarget, value);
            }
            $('.ph-insert').on('click', function () { insertPlaceholder(token(String($(this).data('key')))); });
            $('#insert-eula-placeholder').on('click', function () {
                placeholderTarget = terms; targetLabel(); insertPlaceholder(token($('#eula-placeholder').val()));
            });
            $('#ph-insert-loop').on('click', function () {
                insertPlaceholder('@{{#assets}}\n@{{asset.asset_tag}} — @{{asset.name}} (@{{asset.serial}})\n@{{/assets}}');
            });
            $('#placeholder-search').on('input', function () {
                var query = this.value.toLowerCase();
                $('.ph-insert').each(function () { $(this).toggle($(this).text().toLowerCase().indexOf(query) !== -1); });
            });
            function updateFont() {
                var family = $('#header_config_font_family').val();
                $('#custom-font-field').toggle(family === 'custom');
                var families = {dejavusans: 'DejaVu Sans, sans-serif', dejavuserif: 'DejaVu Serif, serif', dejavusansmono: 'DejaVu Sans Mono, monospace'};
                $('#terms-preview').css({'font-family': families[family] || 'inherit', 'font-size': $('#header_config_font_size').val() + 'pt', 'line-height': $('#header_config_line_height').val()});
            }
            $('#header_config_font_family, #header_config_font_size, #header_config_line_height').on('change input', updateFont); updateFont();
            document.querySelector('.document-template-form').addEventListener('invalid', function (event) {
                $(event.target).parents('details').prop('open', true);
            }, true);
        });
    </script>
@stop
