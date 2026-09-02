@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.labels_title') }}
    @parent
@stop

@section('header_right')
    <button
            type="submit"
            form="label-customizer-form"
            class="btn btn-success"
    >
        <i class="fa fa-check"></i> Save
    </button>
    <a href="{{ route('settings.labels.index') }}" class="btn btn-primary"> {{ trans('general.back') }}</a>
@stop


{{-- Page content --}}
@section('content')
    <style>
        .label-customizer-shell {
            height: calc(100vh - 120px);
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        .label-preview-sticky {
            position: sticky;
            top: 0;
            z-index: 20;
            background: #222d32;
            padding-bottom: 12px;
            flex: 0 0 auto;
        }

        .label-form-scroll {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            padding-top: 10px;
        }

        .label-config-grid {
            display: block;
        }

        .label-form-header,
        .label-supports-row,
        .label-content-row,
        .label-dimensions-row {
            max-width: 100%;
            margin-bottom: 16px;
        }

        .label-content-row {
            width: 100%;
            margin-bottom: 16px;
        }

        .label-content-groups {
            display: grid;
            grid-template-columns: repeat(3, minmax(260px, 1fr));
            gap: 16px;
            align-items: start;
        }

        .label-dimensions-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(260px, 1fr));
            gap: 16px;
        }

        .label-layout-row {
            width: 100%;
            margin-bottom: 16px;
        }

        .label-config-panel {
            margin-bottom: 0;
            box-shadow: none;
        }

        .label-config-panel .form-control {
            width: 160px;
            max-width: 160px;
        }

        .label-supports-row .col-md-2 {
            text-align: center;
        }

        .label-supports-row .number-field label {
            flex-direction: row;
            gap: 8px;
        }

        .label-supports-row .col-md-2 label {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 0;
        }
    </style>

    <div class="col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1">
        <div class="label-customizer-shell">

            <fieldset name="label-preview" class="label-preview-sticky">
                <div class="col-md-12" style="margin-bottom: 10px;">
                    @include('partials.custom-label-preview')
                </div>
            </fieldset>
            <div class="label-form-scroll">
                <form id="label-customizer-form" method="POST" action="{{ $formAction }}" class="form-horizontal">
                    @csrf

                    @if (($formMethod ?? 'POST') !== 'POST')
                        @method($formMethod)
                    @endif
                    @if (! empty($importedConfig))
                        <input type="hidden" name="imported_config_snapshot"
                               value="{{ e(json_encode($importedConfig)) }}">
                    @endif

                    @php
                        $printable = $config['label_printable_area'] ?? $config['printable_area'] ?? null;
                    @endphp
                    <input type="hidden"
                           name="template"
                           value="{{ $selectedLabel ?: (($selectedType ?? null) === 'tape' ? 'StandardTape' : 'DefaultLabel') }}">
                    <input
                            type="hidden"
                            name="type"
                            value="{{ $selectedType ?? data_get($config, 'type', 'sheet') }}"
                    >
                    <div class="label-config-grid">
                        <div class="label-form-header">
                            @php
                                $defaultName = $selectedLabel ? 'Copy of '.$selectedLabel : 'Custom Label';
                            @endphp

                            <div class="form-group" style="margin-left: 0; margin-right: 0;">
                                <label for="name" style="display:block; margin-bottom:6px;">
                                    Label Name
                                </label>

                                <input
                                        id="name"
                                        type="text"
                                        name="name"
                                        class="form-control"
                                        value="{{ old('name', data_get($config ?? [], 'name', $defaultName)) }}"
                                        placeholder="Enter label name"
                                        style="max-width: 320px;"
                                >
                            </div>

                            <p class="text-muted">
                                Unit: {{ $config['unit'] }} (applies to all dimensions)
                            </p>
                        </div>

                        @if(isset($sections['supports']))
                            <div class="label-supports-row">
                                @include('partials.label-editor-section', [
                                    'sectionKey' => 'supports',
                                    'section' => $sections['supports'],
                                    'config' => $config,
                                    'inlineFields' => true,
                                    'showLegend' => true,
                                ])
                            </div>
                        @endif

                        @if(isset($sections['content']))
                            <div class="label-content-row">
                                @include('partials.label-editor-section', [
                                    'sectionKey' => 'content',
                                    'section' => $sections['content'],
                                    'config' => $config,
                                    'inlineFields' => false,
                                    'showLegend' => true,
                                ])
                            </div>
                        @endif

                        @if(isset($sections['layout']))
                            <div class="label-layout-row">
                                @include('partials.label-editor-section', [
                                    'sectionKey' => 'layout',
                                    'section' => $sections['layout'],
                                    'config' => $config,
                                    'inlineFields' => false,
                                ])
                            </div>
                        @endif
                    </div>
                </form>

            </div>
        </div>
    </div>
    <div class="label-form-footer">
        <div class="box-footer clearfix" style="padding-left: 0; padding-right: 0;">

        </div>
    </div>
@stop
@push('js')
    <script>
        $(document).on('change', '.align-sync', function () {
            const key = $(this).data('key');
            const value = $(this).val();

            if (key === 'logo_h_align') {
                $('select[name="content[barcode2D_h_align]"]')
                    .val(value === 'L' ? 'R' : 'L');
            }

            if (key === 'barcode2D_h_align') {
                $('select[name="content[logo_h_align]"]')
                    .val(value === 'L' ? 'R' : 'L');
            }
        });

        function syncContentSupportGroups() {
            $('.label-editor-group[data-support-toggle]').each(function () {
                const $group = $(this);
                const toggle = $group.data('support-toggle');

                const parts = toggle.split('.');

                if (parts.length !== 2) {
                    return;
                }

                const inputName = `${parts[0]}[${parts[1]}]`;
                const $input = $(`[name="${inputName}"]`);

                let enabled = false;

                if ($input.is(':checkbox')) {
                    enabled = $input.is(':checked');
                } else {
                    enabled = parseInt($input.val(), 10) > 0;
                }

                $group.toggle(enabled);
            });
        }

        $(document).on(
            'change',
            'input[name^="supports["][type="checkbox"]',
            syncContentSupportGroups
        );

        $(syncContentSupportGroups);
    </script>
    @livewireScripts
@endpush