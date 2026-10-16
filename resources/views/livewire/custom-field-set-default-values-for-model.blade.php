<span>
    <div class="form-group{{ $errors->has('custom_fieldset') ? ' has-error' : '' }}">
        <label for="custom_fieldset" class="col-md-3 control-label">
            {{ trans('admin/models/general.fieldset') }}
        </label>
        <div class="col-md-5">
             <x-input.select
                 name="fieldset_id"
                 id="fieldset_id"
                 :options="Helper::customFieldsetList()"
                 :selected="old('fieldset_id', $fieldset_id)"
                 :for-livewire="true"
                 class="js-fieldset-field"
                 style="width:100%; min-width:350px;"
                 aria-label="custom_fieldset"
             />
            <x-form.error name="custom_fieldset" />
        </div>
        <div class="col-md-3">
            @if ($fieldset_id)
                <label class="form-control">
                    <input
                        type="checkbox"
                        name="add_default_values"
                        value="1"
                        id="add_default_values"
                        wire:model.live="add_default_values"
                        data-livewire-component="{{ $this->getId() }}"
                        @disabled($this->fields->isEmpty())
                    />
                    {{ trans('admin/models/general.add_default_values') }}
                </label>
            @endif
        </div>
    </div>

    @if ($add_default_values)

        @if ($this->fields)

                @foreach ($this->fields as $field)
                    <div class="form-group" wire:key="field-{{ $field->id }}">

                        <label class="col-md-3 control-label{{ $errors->has($field->db_column_name()) ? ' has-error' : '' }}">{{ $field->name }}</label>

                        <div class="col-md-7">

                                @if ($field->format == "DATE" || $field->element == "date_picker")

                                    <div class="input-group col-md-4" style="padding-left: 0px;">
                                        {{-- wire:model + the input-event dispatch on change is the
                                             usual Livewire workaround so the picker's value change
                                             flows back to the component. See
                                             https://laracasts.com/discuss/channels/livewire/livewire-and-bootstrap-datepicker?page=1&replyId=623122
                                             format=DATE and element=date_picker both render a
                                             datepicker here; the difference (native DATE column
                                             vs plain TEXT column that allows encryption) is
                                             handled at the model level, not on the default-value
                                             widget. --}}
                                        <x-input.datepicker
                                            id="default-value{{ $field->id }}"
                                            name="default_values[{{ $field->id }}]"
                                            wire:model="selectedValues.{{ $field->db_column }}"
                                            onchange="this.dispatchEvent(new InputEvent('input'))"
                                        />
                                    </div>

                                @elseif ($field->format == "DATETIME" || $field->element == "datetime_picker")

                                    {{-- Same rationale as the datepicker branch above, applied
                                         to the datetimepicker widget. default_now=false so an
                                         empty "no default" stays empty rather than pre-filling
                                         with today's datetime. --}}
                                    <div class="input-group col-md-6" style="padding-left: 0px;">
                                        <x-input.datetimepicker
                                            id="default-value{{ $field->id }}"
                                            name="default_values[{{ $field->id }}]"
                                            wire:model="selectedValues.{{ $field->db_column }}"
                                            :default_now="false"
                                            onchange="this.dispatchEvent(new InputEvent('input'))"
                                        />
                                    </div>

                                @elseif ($field->element == "text")


                                    <input
                                        class="form-control"
                                        type="text"
                                        id="default-value{{ $field->id }}"
                                        name="default_values[{{ $field->id }}]"
                                        wire:model="selectedValues.{{ $field->db_column }}"
                                    />


                                @elseif($field->element == "textarea" || $field->element == "markdown-textarea")


                                        <textarea
                                            class="form-control"
                                            style="width: 100%;"
                                            id="default-value{{ $field->id }}"
                                            name="default_values[{{ $field->id }}]"
                                            wire:model="selectedValues.{{ $field->db_column }}"
                                        ></textarea>


                                @elseif($field->element == "listbox")

                                        {{-- Iterate CustomField::formatFieldValuesAsArray so line-ending
                                             handling and the `key|label` split flow through the same
                                             helper the asset-edit form uses. Skip the '' key the helper
                                             prepends for listbox ("Select <format>") because on the
                                             model default-values page we want a truly blank "no default
                                             set" option, not a "please pick one" prompt. See #19429. --}}
                                        <select class="form-control" name="default_values[{{ $field->id }}]" wire:model="selectedValues.{{ $field->db_column }}">
                                            <option value=""></option>
                                            @foreach($field->formatFieldValuesAsArray() as $field_value => $field_label)
                                                @continue($field_value === '')
                                                <option
                                                    value="{{ $field_value }}"
                                                    wire:key="listbox-{{ $field_value }}"
                                                >
                                                    {{ $field_label }}
                                                </option>
                                            @endforeach
                                        </select>

                                @elseif($field->element == "multi-listbox")
                                    <select class="form-control" name="default_values[{{ $field->id }}][]" multiple wire:model="selectedValues.{{ $field->db_column }}">
                                        @foreach($field->formatFieldValuesAsArray() as $field_value => $field_label)
                                            <option
                                                value="{{ $field_value }}"
                                                wire:key="multi-listbox-{{ $field_value }}"
                                            >
                                                {{ $field_label }}
                                            </option>
                                        @endforeach
                                    </select>

                                @elseif($field->element == "radio")

                                    {{-- Radio/checkbox mirror the asset-edit form's convention:
                                         use the display label as both the submitted value and the
                                         visible text, so stored defaults match what the asset form
                                         will render as pre-selected. --}}
                                    @foreach($field->formatFieldValuesAsArray() as $field_value)
                                        <label class="col-md-3 form-control" for="{{ $field->db_column }}_{{ str_slug($field_value) }}" wire:key="radio-{{ $field_value }}">
                                            <input
                                                id="{{ $field->db_column }}_{{ str_slug($field_value) }}"
                                                aria-label="{{ str_slug($field->name) }}"
                                                type="radio"
                                                name="default_values[{{ $field->id }}]"
                                                value="{{ $field_value }}"
                                                wire:model="selectedValues.{{ $field->db_column }}"
                                            />{{ $field_value }}
                                        </label>
                                    @endforeach

                                @elseif($field->element == "checkbox")

                                     @foreach($field->formatFieldValuesAsArray() as $field_value)
                                        <label class="col-md-3 form-control" for="{{ $field->db_column }}_{{ str_slug($field_value) }}" wire:key="checkbox-{{ $field_value }}">
                                            <input
                                                id="{{ $field->db_column }}_{{ str_slug($field_value) }}"
                                                type="checkbox"
                                                aria-label="{{ str_slug($field->name) }}"
                                                name="default_values[{{ $field->id }}][]"
                                                value="{{ $field_value }}"
                                                wire:model="selectedValues.{{ $field->db_column }}"
                                            > {{ $field_value }}
                                        </label>
                                    @endforeach


                                @else
                                    <span class="help-block form-error">
                                        Unknown field element: {{ $field->element }}
                                    </span>
                                @endif
                                        <?php
                                        $errormessage = $errors->first($field->db_column_name());
                                        if ($errormessage) {
                                            print('<span class="alert-msg" role="alert" aria-live="assertive">'.$errormessage.'</span>');
                                        }
                                        ?>
                        </div>
                    </div>

            @endforeach

            @endif

    @endif
</span>
