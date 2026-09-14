<div
        wire:ignore.self
        class="modal fade"
        id="import-label-modal"
        tabindex="-1"
        role="dialog"
        aria-labelledby="importLabelModalLabel"
>
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>

                <h4 class="modal-title" id="importLabelModalLabel">
                    {{ trans('admin/labels/general.import_label_config') }}
                </h4>
            </div>

            <div class="modal-body">

                <div class="form-group">
                    <label>
                        {{ trans('admin/labels/general.import_method') }}
                    </label>

                    <div class="btn-group import-toggle" role="group">
                        <button
                                type="button"
                                class="btn {{ $importMethod === 'json' ? 'btn-primary active' : 'btn-default' }}"
                                wire:click="setImportMethod('json')"
                        >
                            JSON
                        </button>

                        <button
                                type="button"
                                class="btn {{ $importMethod === 'text' ? 'btn-primary active' : 'btn-default' }}"
                                wire:click="setImportMethod('text')"
                        >
                            {{ trans('general.text') }}
                        </button>
                    </div>
                </div>

                @if ($importMethod === 'text')
                    <div class="form-group">
                        <label for="config-snapshot">
{{--                            {{ trans('admin/labels/general.import_label_config') }}--}}
                        </label>

                        <textarea
                                id="config-snapshot"
                                wire:model.live="configSnapshot"
                                class="form-control"
                                rows="8"
                                placeholder=" {{ trans('admin/labels/general.import_paste_json') }}"
                        ></textarea>
                    </div>
                @else
                    <div class="form-group">

                        <input
                                id="config-file"
                                type="file"
                                wire:model="configFile"
                                class="form-control"
                                accept=".json,application/json"
                        >

                        <p class="help-block">
                            {{ trans('admin/labels/general.import_json_help') }}
                        </p>
                    </div>
                @endif

                @error('configSnapshot')
                <p class="help-block text-danger">
                    <i class="fa-solid fa-xmark"></i>
                    {{ $message }}
                </p>
                @enderror

                @error('configFile')
                <p class="help-block text-danger">
                    <i class="fa-solid fa-xmark"></i>
                    {{ $message }}
                </p>
                @enderror

                @if ($validationMessages)
                    <ul class="alert-msg list-unstyled">
                        @foreach ($validationMessages as $message)
                            <li>
                                <i class="fa-solid fa-xmark text-danger"></i>
                                {{ $message }}
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div wire:loading wire:target="configFile,import">
                    <p class="text-muted">
                        <i class="fas fa-spinner fa-spin"></i>
                    </p>
                </div>

            </div>

            <div class="modal-footer">
                <button
                        type="button"
                        class="btn btn-default"
                        data-dismiss="modal"
                >
                    {{ trans('general.cancel') }}
                </button>

                <button
                        type="button"
                        class="btn btn-primary"
                        wire:click="import"
                        wire:loading.attr="disabled"
                @if ($importMethod === 'text')
                    @disabled(trim($configSnapshot) === '')
                        @else
                    @disabled(!$configFile)
                        @endif
                >
                    {{ trans('general.import') }}
                </button>
            </div>

        </div>
    </div>
</div>