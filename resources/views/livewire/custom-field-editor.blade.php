<div>

    <form wire:submit.prevent="save" class="form-horizontal" autocomplete="off" role="form">

        <x-container columns="2">

            <x-page-column class="col-md-8">

                <x-box top_submit>

                    <x-form.row
                        :label="trans('admin/custom_fields/general.field_name')"
                        name="name"
                        required
                    >
                        <x-slot:input>
                            <input
                                type="text"
                                id="name"
                                class="form-control"
                                maxlength="191"
                                wire:model.live="name"
                                aria-label="{{ trans('admin/custom_fields/general.field_name') }}"
                                required
                            >
                        </x-slot:input>
                    </x-form.row>

                    <x-form.row
                        :label="trans('admin/custom_fields/general.field_format')"
                        name="format"
                        required
                    >
                        <x-slot:input>
                            @if ($this->isFormatLockedForEdit)
                                <input
                                    type="text"
                                    id="format"
                                    class="form-control"
                                    value="{{ $format }}"
                                    readonly
                                    aria-label="{{ trans('admin/custom_fields/general.field_format') }}"
                                >
                                <x-form.help name="format-locked">
                                    {{ trans('admin/custom_fields/general.format_locked_for_native_column', ['format' => $format]) }}
                                </x-form.help>
                            @else
                                <x-input.select
                                    forLivewire
                                    id="format"
                                    name="format"
                                    wire:model.live="format"
                                    class="format form-control"
                                    style="width:100%"
                                    aria-label="format"
                                >
                                    @foreach ($predefinedFormats as $key => $label)
                                        <option
                                            value="{{ $key }}"
                                            @selected($format === $key)
                                            @disabled($isEdit && in_array($key, ['DATE', 'DATETIME'], true))
                                        >{{ $label }}</option>
                                    @endforeach
                                </x-input.select>
                                <x-form.help name="format">
                                    {{ trans('admin/custom_fields/general.field_format_help') }}
                                </x-form.help>
                                @if ($isEdit)
                                    <x-form.help name="date_formats_disabled">
                                        {{ trans('admin/custom_fields/general.date_formats_disabled_for_edit') }}
                                    </x-form.help>
                                @endif
                                @if ($this->showFormatChangeWarning)
                                    <x-callout type="warning" icon="warning" style="margin-top: 10px;">
                                        {{ trans('admin/custom_fields/general.format_change_warning') }}
                                    </x-callout>
                                @endif
                                @if ($this->showFormatPickerNote)
                                    <x-form.help name="format_picker_note">
                                        {{ trans('admin/custom_fields/general.format_any_with_date_picker_help') }}
                                    </x-form.help>
                                @endif
                            @endif
                        </x-slot:input>
                    </x-form.row>

                    <x-form.row
                        :label="trans('admin/custom_fields/general.field_element')"
                        name="element"
                        required
                    >
                        <x-slot:input>
                            <x-input.select
                                forLivewire
                                id="element"
                                name="element"
                                wire:model.live="element"
                                class="field_element form-control"
                                style="width: 100%;"
                                aria-label="element"
                            >
                                @foreach ($elementOptions as $key => $label)
                                    <option
                                        value="{{ $key }}"
                                        @selected($element === $key)
                                        @disabled(! in_array($key, $this->allowedElementKeys))
                                    >{{ $label }}</option>
                                @endforeach
                            </x-input.select>
                            <x-form.help name="element">
                                {{ trans('admin/custom_fields/general.field_element_help') }}
                            </x-form.help>
                        </x-slot:input>
                    </x-form.row>

                    @if ($this->showFieldValues)
                        <x-form.row
                            :label="trans('admin/custom_fields/general.field_values')"
                            name="field_values"
                            required
                            :help_text="trans('admin/custom_fields/general.field_values_help')"
                        >
                            <x-slot:input>
                                <textarea
                                    id="field_values"
                                    class="form-control"
                                    wire:model.live="field_values"
                                    style="width: 100%"
                                    rows="4"
                                    aria-label="field_values"
                                >{{ $field_values }}</textarea>
                            </x-slot:input>
                        </x-form.row>
                    @endif

                    @if ($this->showCustomRegex)
                        <x-form.row
                            :label="trans('admin/custom_fields/general.field_custom_format')"
                            name="custom_format"
                            required
                            help_html="{!! trans('admin/custom_fields/general.field_custom_format_help') !!}"
                        >
                            <x-slot:input>
                                <input
                                    type="text"
                                    id="custom_format"
                                    class="form-control"
                                    maxlength="191"
                                    placeholder="regex:/^[0-9]{15}$/"
                                    wire:model.live="custom_format"
                                    aria-label="custom_format"
                                >
                            </x-slot:input>
                        </x-form.row>
                    @endif

                    <x-form.row
                        :label="trans('admin/custom_fields/general.help_text')"
                        name="help_text"
                        :help_text="trans('admin/custom_fields/general.help_text_description')"
                    >
                        <x-slot:input>
                            <input
                                type="text"
                                id="help_text"
                                class="form-control"
                                wire:model.live="help_text"
                                aria-label="help_text"
                            >
                        </x-slot:input>
                    </x-form.row>

                    @if ($this->showLinkTemplate)
                        <x-form.row
                            :label="trans('admin/custom_fields/general.link_template')"
                            name="link_template"
                            help_html="{!! trans('admin/custom_fields/general.link_template_help') !!}"
                        >
                            <x-slot:input>
                                <input
                                    type="text"
                                    id="link_template"
                                    class="form-control"
                                    maxlength="191"
                                    placeholder="anydesk:{VALUE}"
                                    wire:model.live="link_template"
                                    aria-label="link_template"
                                >
                            </x-slot:input>
                        </x-form.row>
                    @endif

                    @if (! $isEdit)
                        <x-form.checkbox-row
                            name="field_encrypted"
                            :label="trans('admin/custom_fields/general.encrypt_field')"
                            :checked="$field_encrypted"
                            :disabled="! $this->canEncrypt"
                            :help_text="$this->showEncryptDisabledNote ? trans('admin/custom_fields/general.encrypt_disabled_for_date_format') : null"
                            wire:model.live="field_encrypted"
                        />

                        @if ($field_encrypted)
                            <div class="form-group">
                                <div class="col-md-9 col-md-offset-3">
                                    <x-callout type="danger" icon="warning" live="assertive">
                                        {{ trans('admin/custom_fields/general.encrypt_field_help') }}
                                    </x-callout>
                                </div>
                            </div>
                        @endif
                    @endif

                    @if ($isEdit && $field_encrypted)
                        <div class="form-group">
                            <div class="col-md-9 col-md-offset-3">
                                <x-alert type="warning" icon="warning" :title="trans('general.notification_warning')">
                                    {{ trans('admin/custom_fields/general.encrypted_options') }}
                                </x-alert>
                            </div>
                        </div>
                    @endif

                    @if (! $field_encrypted)
                        <x-form.checkbox-row
                            name="is_unique"
                            :label="trans('admin/custom_fields/general.is_unique')"
                            :checked="$is_unique"
                            wire:model.live="is_unique"
                        />
                    @endif

                    <fieldset>
                        <x-form.legend>
                            {{ trans('admin/custom_fields/general.section_visibility') }}
                        </x-form.legend>

                        <x-form.checkbox-row
                            name="show_in_listview"
                            :label="trans('admin/custom_fields/general.show_in_listview')"
                            :checked="$show_in_listview"
                            wire:model.live="show_in_listview"
                        />

                        @if (! $field_encrypted)
                            <x-form.checkbox-row
                                name="show_in_requestable_list"
                                :label="trans('admin/custom_fields/general.show_in_requestable_list')"
                                :checked="$show_in_requestable_list"
                                wire:model.live="show_in_requestable_list"
                            />

                            <x-form.checkbox-row
                                name="show_in_email"
                                :label="trans('admin/custom_fields/general.show_in_email')"
                                :checked="$show_in_email"
                                wire:model.live="show_in_email"
                            />

                            <x-form.checkbox-row
                                name="display_in_user_view"
                                :label="trans('admin/custom_fields/general.display_in_user_view')"
                                :checked="$display_in_user_view"
                                wire:model.live="display_in_user_view"
                            />
                        @endif
                    </fieldset>

                    <fieldset>
                        <x-form.legend>
                            {{ trans('admin/custom_fields/general.section_display_on_forms') }}
                        </x-form.legend>

                        <x-form.checkbox-row
                            name="display_checkout"
                            :label="trans('admin/custom_fields/general.display_checkout')"
                            :checked="$display_checkout"
                            wire:model.live="display_checkout"
                        />

                        <x-form.checkbox-row
                            name="display_checkin"
                            :label="trans('admin/custom_fields/general.display_checkin')"
                            :checked="$display_checkin"
                            wire:model.live="display_checkin"
                        />

                        <x-form.checkbox-row
                            name="display_audit"
                            :label="trans('admin/custom_fields/general.display_audit')"
                            :checked="$display_audit"
                            wire:model.live="display_audit"
                        />
                    </fieldset>

                </x-box>

            </x-page-column>

            <x-page-column class="col-md-4">

                <x-box :header="trans('general.preview')">
                    <x-custom-field-preview
                        :name="$name"
                        :element="$element"
                        :format="$format"
                        :help-text="$help_text"
                        :field-values="$field_values"
                        :link-template="$link_template"
                    />
                </x-box>

                <x-box :header="trans('admin/custom_fields/general.fieldsets')">



                    @if ($fieldsets->count() > 0)

                        <label class="form-control">
                            <x-input.checkbox
                                id="lw-check-all-fieldsets"
                                aria-label="{{ trans('general.select_all') }}"
                            />
                            {{ trans('general.select_all') }}
                        </label>

                        @foreach ($fieldsets as $fieldset)
                            <label class="form-control">
                                <x-input.checkbox
                                    :name="'associate_fieldsets.'.$fieldset->id"
                                    :value="$fieldset->id"
                                    :checked="(bool) ($associate_fieldsets[$fieldset->id] ?? false)"
                                    class="lw-fieldset-check"
                                    :aria-label="$fieldset->name"
                                    wire:model.live="associate_fieldsets.{{ $fieldset->id }}"
                                />
                                {{ $fieldset->name }}
                            </label>
                        @endforeach
                    @endif

                        <label class="form-control">
                            <x-input.checkbox
                                name="auto_add_to_fieldsets"
                                :checked="$auto_add_to_fieldsets"
                                aria-label="auto_add_to_fieldsets"
                                wire:model.live="auto_add_to_fieldsets"
                            />
                            {{ trans('admin/custom_fields/general.auto_add_to_fieldsets') }}
                        </label>

                </x-box>

            </x-page-column>

        </x-container>

    </form>

    @script
    <script>
        (function () {
            const checkAll = document.getElementById('lw-check-all-fieldsets');
            if (checkAll) {
                checkAll.addEventListener('change', function () {
                    document.querySelectorAll('.lw-fieldset-check').forEach(function (cb) {
                        cb.checked = checkAll.checked;
                        cb.dispatchEvent(new Event('change'));
                    });
                });
            }

            // Preview widgets (select2 on listbox, eonasdan datetimepicker on
            // date/datetime pickers) need to be (re-)initialized every time
            // Livewire morphs the DOM — when the user changes the element type
            // or edits field_values, the preview markup is fresh and any
            // previous widget wrappers are gone. On initial page load the
            // global inits in snipeit.js catch these; the interceptor below
            // catches every subsequent morph.
            //
            // We destroy any existing widget instance before re-init because
            // Livewire may reuse the same DOM node between morphs (wire:key
            // on the preview blade forces replacement for element-type
            // changes; this covers the in-place case too — e.g., editing
            // field_values on a listbox, or if a wire:key ever gets missed).
            const initPreviewWidgets = function () {
                const $scope = $('.js-custom-field-preview');
                if (!$scope.length) return;

                $scope.find('.js-preview-select2').each(function () {
                    const $el = $(this);
                    if ($el.hasClass('select2-hidden-accessible')) {
                        $el.select2('destroy');
                    }
                    $el.select2();
                });

                $scope.find('.js-preview-datetimepicker').each(function () {
                    const $el = $(this);
                    const existing = $el.data('DateTimePicker');
                    if (existing) {
                        existing.destroy();
                    }
                });
                if (typeof window.snipeitInitDatetimepickers === 'function') {
                    window.snipeitInitDatetimepickers($scope);
                }
            };

            Livewire.interceptMessage(({ onFinish }) => {
                onFinish(() => queueMicrotask(initPreviewWidgets));
            });

            initPreviewWidgets();
        })();
    </script>
    @endscript

</div>
