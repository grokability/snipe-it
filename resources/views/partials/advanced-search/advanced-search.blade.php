<div class="container">
    <div class="panel panel-default">
        <!-- The button for controlling the collapse -->
        <div class="panel-heading" data-toggle="collapse" data-target="#collapsePanel" aria-expanded="false"
            aria-controls="collapsePanel">
            <span class="panel-title" style="margin: 0;">
                <i class="fas fa-search"></i>
                Advanced search
            </span>
        </div>

        <!-- The collapsible content section -->
        <div id="collapsePanel" class="panel-collapse collapse" role="region" aria-labelledby="panelHeading">
            <div class="panel-body">
                <span id="advancedSearchFilters">
                    @php
                        $layoutJson = \App\Presenters\AssetPresenter::dataTableLayout();
                        $layout = json_decode($layoutJson); // decode to object by default
                        dump($layout);
                    @endphp

                    @foreach ($layout as $tableField)
                        @if ((!empty($tableField->searchable) && $tableField->searchable === true))
                            <span id="advancedSearch_{{ $tableField->field }}" class="advancedSearchItemContainer">
                                <label for="advancedSearch_{{ $tableField->field }}">
                                    <b>{{ $tableField->title }}</b>
                                </label>
                                @if (!isset($tableField->formatter))
                                    {{-- Default select if formatter is not set --}}
                                    <input class="advancedSearch_defaultField form-control" type="text" autocomplete="on" id="advancedSearch_{{ $tableField->field }}_input" >
                                @else
                                    @switch($tableField->formatter)
                                        @case('dateDisplayFormatter')
                                            <!--<input type="date" id="advancedSearch_{{ $tableField->field }}"
                                                name="{{ $tableField->title }}" class="datePicker">-->
                                            <div class="input-daterange input-group " id="checkin-range-datepicker">
                                                <input type="date" id="advancedSearch_{{ $tableField->field }}_start"
                                                    class="form-control" name="checkin_date_start" aria-label="checkin_date_start"">
                                                <span class="input-group-addon">{{ strtolower(trans('general.to')) }}</span>
                                                 <input type="date" id="advancedSearch_{{ $tableField->field }}_end" 
                                                    class="form-control" name="checkin_date_end" aria-label="checkin_date_end"">
                                            </div>
                                        @break

                                        @case('companiesLinkObjFormatter')
                                            @include ('partials.select.dropdowns.company-select', [
                                                'translated_name' => trans('admin/hardware/company.model'),
                                                'fieldname' => $tableField->field,
                                                'select_id' => "advancedSearch_$tableField->field",
                                                'required' => 'false',
                                                'multiple' => 'true',
                                            ])
                                        @break

                                        @case('trueFalseFormatter')
                                            <p>True/false</p>
                                        @break

                                        @case('categoriesLinkObjFormatter')
                                            @include ('partials.select.dropdowns.category-select', [
                                                'translated_name' => trans('admin/hardware/category.model'),
                                                'fieldname' => $tableField->field,
                                                'category_type' => 'asset',
                                                'select_id' => "advancedSearch_$tableField->field",
                                                'required' => 'false',
                                                'multiple' => 'true',
                                            ])
                                        @break

                                        @case('companiesLinkObjFormatter')
                                            <p>companiesLinkObjFormatter</p>
                                        @break

                                        @case('deployedLocationFormatter')
                                            @include ('partials.select.dropdowns.location-select', [
                                                'translated_name' => trans('admin/hardware/location.model'),
                                                'category_type' => 'asset',
                                                'select_id' => "advancedSearch_$tableField->field",
                                                'fieldname' => $tableField->field,
                                                'required' => 'false',
                                                'multiple' => 'true',
                                            ])
                                        @break

                                        @case('employeeNumFormatter')
                                            <input class="advancedSearch_employeeNumFormatter form-control" type="text" id="advancedSearch_{{ $tableField->field }}_input" autocomplete="on">
                                        @break

                                        @case('hardwareLinkFormatter')
                                            <input class="advancedSearch_hardwarelinkFormatter form-control" type="text" id="advancedSearch_{{ $tableField->field }}_input" autocomplete="on">
                                        @break

                                        <!-- Makes no sense for the advanced search
                                            @case('imageFormatter')
                                            <p>imageFormatter</p>
                                            @break */
                                        -->

                                        @case('manufacturersLinkObjFormatter')
                                            @include ('partials.select.dropdowns.manufacturer-select', [
                                                'translated_name' => trans('admin/hardware/manufacturer.model'),
                                                'select_id' => "advancedSearch_$tableField->field",
                                                'fieldname' => $tableField->field,
                                                'required' => 'false',
                                                'multiple' => 'true',
                                            ])
                                        @break

                                        @case('modelsLinkObjFormatter')
                                            @include ('partials.select.dropdowns.model-select', [
                                                'translated_name' => trans('admin/hardware/form.model'),
                                                'select_id' => "advancedSearch_$tableField->field",
                                                'fieldname' => $tableField->field,
                                                'required' => 'false',
                                                'multiple' => 'true',
                                            ])
                                        @break

                                        @case('orderNumberObjFilterFormatter')
                                            <input class="advancedSearch_orderNumberObjFilterFormatter form-control" type="text" id="advancedSearch_{{ $tableField->field }}_input" autocomplete="on">
                                        @break

                                        @case('polymorphicItemFormatter')
                                            @include ('partials.select.dropdowns.assignedTo-select', [
                                                'translated_name' => trans('admin/hardware/assignedTo.model'),
                                                'fieldname' => $tableField->field,
                                                'select_id' => "advancedSearch_$tableField->field",
                                                'required' => 'false',
                                                'multiple' => 'true',
                                            ])
                                        @break

                                        @case('statuslabelsLinkObjFormatter')
                                            @include ('partials.select.dropdowns.status-select', [
                                                'translated_name' => trans('admin/hardware/status.model'),
                                                'select_id' => "advancedSearch_$tableField->field",
                                                'fieldname' => $tableField->field,
                                                'required' => 'false',
                                                'multiple' => 'true',
                                            ])
                                        @break

                                        @case('suppliersLinkObjFormatter')
                                            @include ('partials.select.dropdowns.supplier-select', [
                                                'translated_name' => trans('admin/hardware/supplier.model'),
                                                'select_id' => "advancedSearch_$tableField->field",
                                                'fieldname' => $tableField->field,
                                                'required' => 'false',
                                                'multiple' => 'true',
                                            ])
                                        @break

                                        @case('trueFalseFormatter')
                                            <p>trueFalseFormatter</p>
                                        @break

                                        @case('customFieldsFormatter')
                                            <input class="advancedSearch_customField form-control" type="text" autocomplete="on" id="advancedSearch_{{ $tableField->field }}_input" >
                                        @break

                                        @case('usersLinkObjFormatter')
                                            @include ('partials.select.dropdowns.user-select', [
                                                'translated_name' => trans('admin/hardware/user.model'),
                                                'select_id' => "advancedSearch_$tableField->field",
                                                'fieldname' => $tableField->field,
                                                'required' => 'false',
                                                'multiple' => 'true',
                                            ])
                                        @break

                                        @default
                                            <input class="advancedSearch_defaultField form-control" type="text" autocomplete="on" id="advancedSearch_{{ $tableField->field }}_input" >
                                    @endswitch
                                @endif
                            </span>
                        @endif
                    @endforeach
                    <div id="advancedSearchControlContainer" class="form-group">
                        <button type="submit" class="btn btn-default" id="filterButton">
                            <span aria-hidden="true"></span>
                            <span class="visually-hidden">🔎 {{ trans('button.search') }}</span>
                        </button>
                        <button type="button" class="btn btn-default" id="clearInputButton">
                            <span aria-hidden="true"></span>
                            <span class="visually-hidden">❌ {{ trans('button.delete_search_query') }}</span>
                        </button>
                        <button type="button" class="btn btn-default" id="setDemoDataButton">
                            <span aria-hidden="true"></span>
                            <span class="visually-hidden">Fill with testdata</span>
                        </button>
                    </div>
                </span>
            </div>
        </div>
    </div>
</div>

<script>

class FilterInput {
    constructor(element) {
        this.element = element;
    }

    get key() {
        return this.element.id
            .replace("advancedSearch_", "")
            .replace("_input", "")
            .replace("_start", "")
            .replace("_end", "");
    }

    hasValue() {
        return Boolean(this.element.value);
    }

    getValue() {
        throw new Error("getValue() must be implemented by subclass");
    }

    setValue(newValue) {
        this.element.value = newValue;
    }

    getType() {
        return this.element.id
                .replace("advancedSearch_", "")
                .replace("_", "");
    }

    appendTo(filters) {
        const value = this.getValue();
        if (value !== null && value !== undefined && value !== '') {
            filters[this.key] = value;
        }
    }

    clear() {
        this.element.value = "";
    }
}

class SelectFilterInput extends FilterInput {
    getValue() {
        const selections = $(this.element).select2('data');
        const selectedIds = selections
            .map(item => parseInt(item.id))
            .filter(id => !isNaN(id));

        if (selectedIds.length === 0) {
            return null;
        }

        /*if (this.element.id === 'advancedSearch_assigned_to') {
            return selections.map(selection => ({
                assignedType: "App\\Models\\" + selection.type.charAt(0).toUpperCase() + selection.type.slice(1),
                assigned_to: parseInt(selection.id)
            }));
        }*/
        return selectedIds;
    }

    setValue(newValue) {
        // More details are here https://select2.org/programmatic-control/add-select-clear-items#preselecting-options-in-an-remotely-sourced-ajax-select2

        // Fetch data
        const optionData = fetchItemFromBackendById(this.getType(), newValue)
        .then((response) => {
            var option = new Option(response.name, response.id, true, true);
            $(this.element).append(option).trigger('change');
            
            // manually trigger the `select2:select` event
            $(this.element).trigger({
                type: 'select2:select',
                /*params: {
                    data: data
                }*/
            });
        });
    }

    clear() {
        $(this.element).val(null).trigger('change');
    }
}

class AssignedToSelectFilterInput extends SelectFilterInput {
    getValue() {
        const selections = $(this.element).select2('data');

        if (!selections.length) return null;

        return selections.map(selection => ({
            assignedType: "App\\Models\\" + selection.type.charAt(0).toUpperCase() + selection.type.slice(1),
            assigned_to: parseInt(selection.id)
        }));
    }

    setValue(newValue) {
        console.warn("Not implemented.");
        console.log("Get the data depending if it's assigend to a asset, location or user and set it depending on that. Before implement the import from the data");
    }

}


class DateFilterInput extends FilterInput {
    getValue() {
           return this.hasValue() ? this.element.value : null;
    }
}

class TextFilterInput extends FilterInput {
    getValue() {
        return this.hasValue() ? this.element.value : null;
    }
}

class AdvancedSearchFilterCollector {
    constructor() {
        this.filters = {};
        this.inputs = [];
    }

    collect() {
        this.filters = {};
        this.inputs = [];

        // Select2
        document.querySelectorAll('select[id^="advancedSearch_"]').forEach(el => {
            if (el.id === 'advancedSearch_assigned_to') {
                this.inputs.push(new AssignedToSelectFilterInput(el));
            } else {
                this.inputs.push(new SelectFilterInput(el));
            }
        });

        // Dates
        document.querySelectorAll('input[id^="advancedSearch_"][id$="_start"][type="date"], input[id^="advancedSearch_"][id$="_end"][type="date"]').forEach(el => {
            this.inputs.push(new DateFilterInput(el));
        });

        // Text
        document.querySelectorAll('input[id^="advancedSearch_"][type="text"]').forEach(el => {
            this.inputs.push(new TextFilterInput(el));
        });

        // Process all inputs polymorphically
        this.inputs.forEach(input => {
            input.appendTo(this.filters);
        });

        return this.filters;
    }

    clearAll() {
        this.collect();
        this.inputs.forEach(field => {
            field.clear();
        });
    }

    setValuesFromResponse(response) {
        this.clearAll(); // Runs also this.collect();
        this.inputs.forEach(field => {
            field.setValue("1");
        });
    }
}

class TableFilterController {
    constructor(tableElement) {
        this.$table = tableElement;
        this.collector = new AdvancedSearchFilterCollector();
    }

    refresh() {
        const filters = this.collector.collect();
        this.$table.bootstrapTable('refresh', {
            query: {
                filter: JSON.stringify(filters)
            }
        });
    }

    bindEvents() {
        document.querySelectorAll('[id^="advancedSearch_"]').forEach(el => {
            el.addEventListener('change', this.refresh.bind(this));
        });

        const filterButton = document.getElementById("filterButton");
        if (filterButton) {
            filterButton.addEventListener('click', this.refresh.bind(this));
        }

        const clearButton = document.getElementById("clearInputButton");
        if (clearButton) {
            clearButton.addEventListener('click', () => this.collector.clearAll());
        }

        const setDemoDataButton = document.getElementById("setDemoDataButton");
        if (setDemoDataButton) {
            setDemoDataButton.addEventListener('click', () => this.collector.setValuesFromResponse("1"));
        }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const tableId = "{{ request()->has('status') ? e(request()->input('status')) : '' }}assetsListingTable";
    const $table = $('#' + tableId);

    // Initialize everything
    const controller = new TableFilterController($table);
    controller.bindEvents();
});

function fetchItemFromBackendById(type, id) {
    return new Promise((resolve, reject) => {

        const typeSingularToPluralMap = new Map();
        typeSingularToPluralMap.set("category", "categories");
        typeSingularToPluralMap.set("company", "companies");
        typeSingularToPluralMap.set("location", "locations");
        typeSingularToPluralMap.set("manufacturer", "manufacturers");
        typeSingularToPluralMap.set("model", "models");
        typeSingularToPluralMap.set("rtdlocation", "locations");
        typeSingularToPluralMap.set("statuslabel", "statuslabels");
        typeSingularToPluralMap.set("supplier", "suppliers");

        if(!typeSingularToPluralMap.has(type)) {
            console.error("Invalid type");
            reject("Invalid type");
            return;
        }

        const path = "/api/v1/" + typeSingularToPluralMap.get(type) + "/" + id;
        const options = {
            method: 'GET',
            headers: {
                accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        };
        
        fetch(path, options)
        .then(res => res.json())
        .then(res => resolve(res))
        .catch(err => reject(err));
    });
}

</script>

<style>
    /* Ensure the container is properly constrained */
    .container {
        width: 100%;
        margin: 0 auto;
        padding: 10px;
        box-sizing: border-box;
    }

    /* Make the panel-heading fit within the container */
    .panel-heading {
        cursor: pointer;
        width: 100%;
        box-sizing: border-box;
        padding: 10px 15px;
        margin: 0;
    }

    /* Ensure the grid stays centered */
    #advancedSearchFilters {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px 20px;
        max-width: 1050px;
        margin: 0 auto;
        justify-content: center;
    }

    /* Flexbox styling remains the same */
    .advancedSearchItemContainer {
        display: flex;
        flex-direction: column;
        align-items: stretch;
    }

    .advancedSearchItemContainer b {
        margin-bottom: 5px;
    }

    .advancedSearchItemContainer select.form-control {
        width: 100%;
        box-sizing: border-box;
    }
</style>
