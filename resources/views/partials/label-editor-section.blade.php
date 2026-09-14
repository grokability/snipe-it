<style>
    .supports-control {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .supports-control-input {
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .supports-control .supports-number-input {
        width: 72px;
        max-width: 72px;
        min-width: 72px;
        margin: 0 auto;
        text-align: center;
    }

    .supports-control > label {
        margin-top: 6px;
        margin-bottom: 0;
        text-align: center;
    }
</style>
<div class="box box-default label-config-panel">
    <div class="box-header with-border">
        <x-form.legend>
            {{ $section['label'] }}
        </x-form.legend>
    </div>

    <div class="box-body">
        @if ($inlineFields)
            <div class="row">
                @foreach ($section['fields'] as $fieldKey => $field)
                    @php
                        $name = "{$sectionKey}[{$fieldKey}]";
                        $value = data_get($config, "{$sectionKey}.{$fieldKey}");
                    @endphp

                    <div class="col-md-2">
                        <div class="supports-control">

                            @if ($field['type'] === 'checkbox')
                                <div class="supports-control-input">
                                    <input type="hidden" name="{{ $name }}" value="0">

                                    <input
                                            id="{{ $name }}"
                                            type="checkbox"
                                            name="{{ $name }}"
                                            value="1"
                                            @checked((bool) $value)
                                    >
                                </div>

                                <label for="{{ $name }}">
                                    {{ $field['label'] }}
                                </label>

                            @elseif ($field['type'] === 'number')
                                <div class="supports-control-input">
                                    <input
                                            id="{{ $name }}"
                                            type="number"
                                            name="{{ $name }}"
                                            value="{{ $value }}"
                                            class="form-control supports-number-input"
                                    >
                                </div>

                                <label for="{{ $name }}">
                                    {{ $field['label'] }}
                                </label>

                            @else
                                <div class="supports-control-input">
                                    <input
                                            id="{{ $name }}"
                                            type="{{ $field['type'] }}"
                                            name="{{ $name }}"
                                            value="{{ $value }}"
                                            class="form-control"
                                    >
                                </div>

                                <label for="{{ $name }}">
                                    {{ $field['label'] }}
                                </label>
                            @endif

                        </div>
                    </div>
                @endforeach
            </div>

        @elseif (isset($section['groups']))
            <div class="label-content-groups">
                @foreach ($section['groups'] as $groupKey => $group)
                    @php
                        $toggle = $group['toggle'] ?? null;
                        $isSupported = $toggle
                            ? (bool) data_get($config, $toggle, false)
                            : true;
                    @endphp

                    <div
                            class="panel panel-default label-editor-group"
                            @if($toggle)
                                data-support-toggle="{{ $toggle }}"
                            @endif
                            @if(!$isSupported)
                                style="display:none;"
                            @endif
                    >
                        <div class="panel-heading">
                            <strong>{{ $group['label'] }}</strong>
                        </div>

                        <div class="panel-body">
                            @foreach ($group['fields'] as $fieldKey => $field)
                                @include('partials.label-editor-field', [
                                    'sectionKey' => $group['section_key'] ?? $sectionKey,
                                    'fieldKey' => $fieldKey,
                                    'field' => $field,
                                    'config' => $config,
                                ])
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

        @else
            @foreach ($section['fields'] as $fieldKey => $field)
                @include('partials.label-editor-field', [
                    'sectionKey' => $sectionKey,
                    'fieldKey' => $fieldKey,
                    'field' => $field,
                    'config' => $config,
                ])
            @endforeach
        @endif
    </div>
</div>