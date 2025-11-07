@once
    @push('css')
        <style>
            :root {
                --l2fd-background-color: rgb(246, 250, 255);
                --l2fd-border-color:     #d2d6de;
                --l2fd-font-color:       #555555;
                
                --list-padding:          2px;

                --listitem-font-color:       #555555;
                --listitem-padding:          8px;
                --listitem-spacing:          2px;
                --listitem-background-color: white;
                --listitem-border-color:     #ccc;
                --listitem-border-radius:    2px;
                
                --listitem-hover-font-color:       var(--listitem-font-color);
                --listitem-hover-background-color: var(--listitem-background-color);
                --listitem-hover-border-color:     rgb(102, 175, 233);
                
                --listitem-selected-font-color:       var(--listitem-font-color);
                --listitem-selected-background-color: rgb(236, 240, 245);
                --listitem-selected-border-color:     var(--listitem-hover-border-color);

                --buttonbar-button-font-color:       #555555;
                --buttonbar-button-background-color: white;
                --buttonbar-button-border-color:     #ccc;
                --buttonbar-button-border-radius:    2px;
                
                --buttonbar-button-hover-font-color:       var(--buttonbar-button-font-color);
                --buttonbar-button-hover-background-color: var(--buttonbar-button-background-color);
                --buttonbar-button-hover-border-color:     var(--listitem-hover-border-color);
                
                --buttonbar-button-disabled-font-color:       var(--buttonbar-button-font-color);
                --buttonbar-button-disabled-background-color: #eee;
                --buttonbar-button-disabled-border-color:     var(--buttonbar-button-border-color);
            }

            .l2fd-root,
            .l2fd-root * {
                box-sizing: border-box;
            }

            .l2fd-root {
                height: 300px;
                display: flex;
                flex-direction: column;
                overflow:auto!important;
                max-height:400px;
            }

            .l2fd-title {
                font-size: 1.4em;
                padding: 6px;
                margin: 0;
            }
            
            .l2fd-list {
                overflow-y: scroll;
                padding:    var(--list-padding);
            }

            .l2fd-main {
                flex: 1;
                display: grid;
                grid-template-areas:
                    'fields-title   options-title'
                    'fields-list    options-list'
                    'fields-buttons options-buttons';
                grid-template-columns: 50% 50%;
                grid-template-rows: max-content auto max-content;

                background-color: var(--l2fd-background-color);
                border:           1px solid var(--l2fd-border-color);
                color:            var(--l2fd-font-color);
            }

            .l2fd-listitem {
                color: var(--listitem-font-color);
                cursor: pointer;
                padding: var(--listitem-padding);
                margin-bottom: var(--listitem-spacing);
                background-color: var(--listitem-background-color);
                border: 1px solid var(--listitem-border-color);
                border-radius: var(--listitem-border-radius);
            }
            .l2fd-listitem:hover {
                color: var(--listitem-hover-font-color);
                background-color: var(--listitem-hover-background-color);
                border: 1px solid var(--listitem-hover-border-color);
            }
            .l2fd-listitem.selected {
                color: var(--listitem-selected-font-color);
                background-color: var(--listitem-selected-background-color);
                border: 1px solid var(--listitem-selected-border-color);
            }

            .l2fd-itemgrid {
                display: grid;
                grid-template-areas:
                    'label-title source-title'
                    'label-field source-field';
                grid-template-columns: 50% 50%;
                grid-template-rows: auto auto;
            }

            .l2fd-listitem label {
                cursor: pointer;
                font-size: 0.9em;
                padding: 0;
                margin: 0;
            }

            .l2fd-buttonbar {
                display: flex;
                flex-direction: row;
                height: 35px;
            }
            .l2fd-buttonbar > button {
                flex: 1 1 100%;
                background-color: var(--buttonbar-button-background-color);
                border:           1px solid var(--buttonbar-button-border-color);
                border-radius:    var(--buttonbar-button-border-radius);
                color:            var(--buttonbar-button-font-color);
            }
            .l2fd-buttonbar > button:hover {
                background-color: var(--buttonbar-button-hover-background-color);
                border:           1px solid var(--buttonbar-button-hover-border-color);
                color:            var(--buttonbar-button-hover-font-color);
            }
            .l2fd-buttonbar > button.disabled {
                background-color: var(--buttonbar-button-disabled-background-color);
                border:           1px solid var(--buttonbar-button-disabled-border-color);
                color:            var(--buttonbar-button-disabled-font-color);
                cursor:           not-allowed;
            }
            
        </style>
    @endpush
@endonce

@push('js')
    <script>
        document.addEventListener('alpine:init', () => {

            Alpine.data('{{ $name }}', () => ({

                _name: '{{ $name }}',
                _defaultValue: '{{ $value }}',
                  _init: function() {
                    this.fields = this.fromString(this._defaultValue);
                    this.$watch('valueString', () => {
                        this.$refs.input.form.dispatchEvent(new Event('change'));
                    });
                },

                /* Fields */
                fields: [],
                get _templateField() { return ({ options: [ this._templateOption ] }); },
                _selectedField: null,
                get selectedField() { return this._selectedField; },
                set selectedField(field) {
                    this._selectedField = field;
                    this.selectedOption = null;
                },
                get selectedFieldIndex() {
                    return this.selectedField ? this.fields.indexOf(this.selectedField) : -1;
                },
                shiftSelectedField: function(offset) {
                    this.shiftArrayValue(this.fields, this.selectedField, offset);
                },
                trashSelectedField: function() {
                    this.fields.splice(this.fields.indexOf(this.selectedField), 1);
                    this.selectedField = null;
                },
                addField: function() {
                    let newField = JSON.parse(JSON.stringify(this._templateField));
                    this.fields.push(newField);
                    this.selectedField = newField;
                },

                /* Options */
                get _templateOption() { return ({ label: '', datasource: '' }); },
                selectedOption: null,
                get selectedOptionIndex() {
                    return this.selectedOption ? this.selectedField.options.indexOf(this.selectedOption) : -1;
                },
                shiftSelectedOption: function(offset) {
                    this.shiftArrayValue(this.selectedField.options, this.selectedOption, offset);
                },
                trashSelectedOption: function() {
                    this.selectedField.options.splice(this.selectedField.options.indexOf(this.selectedOption), 1);
                    this.selectedOption = null;
                },
                addOption: function() {
                    let newOption = JSON.parse(JSON.stringify(this._templateOption));
                    this.selectedField.options.push(newOption);
                    this.selectedOption = newOption;
                },


                /* Helpers */

                shiftArrayValue: function(array, value, offset) {
                    let oldIndex = array.indexOf(value);
                    let newIndex = oldIndex + offset;
                    newIndex = Math.max(newIndex, 0);
                    newIndex = Math.min(newIndex, array.length);

                    array.splice(newIndex, 0, array.splice(oldIndex, 1)[0]);
                },

                get valueString() {
                    return this.getCombinedString(this.fields);
                },
                onTest: function(a) {
                    console.log('test', a);
                },

                getFieldLabel: function(field) {
                    return field.options.map(option => option.label).join(' | ');
                },
                fromString: function(string) {
                    return string
                        .split(';').filter(fieldString => fieldString !== '')
                        .map(fieldString => ({
                            options: fieldString
                                .split('|').filter(optionString => optionString !== '')
                                .map(optionString => {
                                    let [l,d] = optionString.split('=');
                                    return { label: l, datasource: d };
                                })
                        }));
                },
                getCombinedString: function (fields) {
                    return fields
                        .map(field => field.options
                            .map(option => option.label + '=' + option.datasource)
                            .join('|')
                        )
                        .join(';');
                },

            }));

        });
    </script>
@endpush
@php
    $selector = '[x-data="'.$name.'"]';
@endphp
@push('css')
    <style>


    </style>
@endpush

<div x-data="{{ $name }}" x-init="_init" class="l2fd-root">
    <input type="hidden" name="{{ $name }}" x-model="valueString" x-ref="input" />
    <div class="l2fd-main">
        <h1 class="l2fd-title" style="grid-area: fields-title">{{trans('general.fields')}}</h1>
        <div class="l2fd-list" style="grid-area: fields-list">
            <template x-for="(field, index) in fields">
                <div
                    x-data="{
                                template: '{{ $template }}'
                            }"
                    x-bind:key="'field-' + index"
                    x-bind:class="{
                        'l2fd-listitem': true,
                        'selected': selectedField === field
                    }"
                    x-bind:style="index < 4 && template === 'DefaultLabel' ? 'background-color:#EEEEEE;' : 'background-color:#FFF;'"
                    x-on:click="selectedField = field"
                    >
                    <label><span x-text="index+1"></span>: <span x-text="getFieldLabel(field)"></span></label>
                </div>
            </template>
        </div>
        <div class="l2fd-buttonbar" style="grid-area: fields-buttons">
            <button 
                x-on:click.prevent="if(!$event.target.classList.contains('disabled')) shiftSelectedField(-1)"
                x-bind:class="{ 'disabled': !selectedField || selectedFieldIndex == 0 }"
                ><i class="fa-solid fa-caret-up"></i></button>
            <button 
                x-on:click.prevent="if(!$event.target.classList.contains('disabled')) shiftSelectedField(+1)"
                x-bind:class="{ 'disabled': !selectedField || selectedFieldIndex == fields.length - 1 }"
                ><i class="fa-solid fa-caret-down"></i></button>
            <button 
                x-on:click.prevent="if(!$event.target.classList.contains('disabled')) addField()"
                x-bind:class="{}"
                ><i class="fa-solid fa-plus"></i></button>
            <button 
                x-on:click.prevent="if(!$event.target.classList.contains('disabled')) trashSelectedField()"
                x-bind:class="{ 'disabled': !selectedField }"
                ><i class="fa-solid fa-trash"></i></button>
        </div>

        <h1 class="l2fd-title" style="grid-area: options-title">{{trans('general.options')}}</h1>
        <div class="l2fd-list" style="grid-area: options-list">
            <template x-if="selectedField">
                <template x-for="(option, index) in selectedField.options">
                    <div 
                        x-bind:key="'option-' + index"
                        x-bind:class="{
                            'l2fd-listitem': true,
                            'l2fd-itemgrid': true,
                            'selected': selectedOption == option 
                        }"
                        x-on:click="selectedOption = option" >
                        <label style="grid-area: label-title">{{trans('general.label')}}</label>
                        <input style="grid-area: label-field" x-model="option.label" />
                        <label style="grid-area: source-title">{{trans('general.data_source')}}</label>
                        <select style="grid-area: source-field" x-model="option.datasource">
                            <optgroup label="{{trans('general.asset')}}">
                                <option value="" disabled>{{ trans('general.select_datasource') }}</option>
                                <option value="asset_tag">{{trans('admin/hardware/table.asset_tag')}}</option>
                                <option value="name">{{trans('admin/hardware/form.name')}}</option>
                                <option value="serial">{{trans('admin/hardware/table.serial')}}</option>
                                <option value="asset_eol_date">{{trans('admin/hardware/form.eol_date')}}</option>
                                <option value="order_number">{{trans('admin/hardware/form.order')}}</option>
                                <option value="purchase_date">{{trans('admin/hardware/table.purchase_date')}}</option>
                                <option value="assignedTo">{{trans('admin/hardware/table.assigned_to')}}</option>
                                <option value="last_audit_date">{{trans('general.last_audit')}}</option>
                                <option value="next_audit_date">{{trans('general.next_audit_date')}}</option>
                            </optgroup>
                            <optgroup label="{{trans('general.asset')." ".trans('general.asset_model')}}">
                                <option value="model.name">{{trans('admin/models/table.name')}}</option>
                                <option value="model.model_number">{{trans('admin/models/table.modelnumber')}}</option>
                            </optgroup>
                            <optgroup label="{{trans('general.manufacturer')}}">
                                <option value="model.manufacturer.name">{{trans('admin/hardware/form.manufacturer')}}</option>
                                <option value="model.manufacturer.support_email">{{trans('admin/manufacturers/table.support_email')}}</option>
                                <option value="model.manufacturer.support_phone">{{trans('admin/manufacturers/table.support_phone')}}</option>
                                <option value="model.manufacturer.support_url">{{trans('general.url')}}</option>
                            </optgroup>
                            <optgroup label="{{trans('general.category')}}">
                                <option value="model.category.name">{{trans('admin/categories/general.category_name')}}</option>
                            </optgroup>
                            <optgroup label="{{trans('general.status')}}">
                                <option value="assetstatus.name">{{trans('admin/statuslabels/table.name')}}</option>
                            </optgroup>
                            <optgroup label="{{trans('general.supplier')}}">
                                <option value="supplier.name">{{trans('admin/suppliers/table.name')}}</option>
                            </optgroup>
                            <optgroup label=" {{trans('admin/hardware/form.default_location')}}">
                                <option value="defaultLoc.name">{{trans('admin/hardware/form.default_location')}}</option>
                                <option value="defaultLoc.phone">{{trans('admin/hardware/form.default_location_phone')}}</option>
                            </optgroup>
                            <optgroup label="{{trans('general.location')}}">
                                <option value="location.name">{{trans('admin/locations/table.name')}}</option>
                                <option value="location.phone">{{trans('admin/locations/table.phone')}}</option>
                            </optgroup>
                            <optgroup label="{{trans('general.company')}}">
                                <option value="company.email">{{trans('admin/companies/table.email')}}</option>
                                <option value="company.name">{{trans('admin/companies/table.name')}}</option>
                                <option value="company.phone">{{trans('admin/companies/table.phone')}}</option>
                            </optgroup>
                            <optgroup label="Custom Fields">
                                @foreach($customFields as $customField)

                                    <option value="{{ $customField->db_column }}">{{ $customField->name }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>
                </template>
            </template>
            <template x-if="!selectedField">
                <div>Please select a field</div>
            </template>
        </div>
        <div class="l2fd-buttonbar" style="grid-area: options-buttons">
            <button 
                x-on:click.prevent="if(!$event.target.classList.contains('disabled')) shiftSelectedOption(-1)"
                x-bind:class="{ 'disabled': !selectedField || !selectedOption || selectedOptionIndex == 0 }"
                ><i class="fa-solid fa-caret-up"></i></button>
            <button 
                x-on:click.prevent="if(!$event.target.classList.contains('disabled')) shiftSelectedOption(+1)"
                x-bind:class="{ 'disabled': !selectedField || !selectedOption || selectedOptionIndex == selectedField.options.length - 1 }"
                ><i class="fa-solid fa-caret-down"></i></button>
            <button 
                x-on:click.prevent="if(!$event.target.classList.contains('disabled')) addOption()"
                x-bind:class="{ 'disabled': !selectedField }"
                ><i class="fa-solid fa-plus"></i></button>
            <button 
                x-on:click.prevent="if(!$event.target.classList.contains('disabled')) trashSelectedOption()"
                x-bind:class="{ 'disabled': !selectedField || !selectedOption }"
                ><i class="fa-solid fa-trash"></i></button>
        </div>
    </div>
</div>
