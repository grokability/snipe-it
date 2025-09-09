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
                        //dump($layout);
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
                            <span>🔎 {{ trans('button.search') }}</span>
                        </button>
                        <button type="button" class="btn btn-default" id="clearInputButton">
                            <span aria-hidden="true"></span>
                            <span>❌ {{ trans('button.delete_search_query') }}</span>
                        </button>
                        <button type="button" class="btn btn-default" id="setDemoDataButton">
                            <span aria-hidden="true"></span>
                            <span>Fill with testdata</span>
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
            .replace("_input", "");
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
                .replace("_input", "")
                .replace("_start", "")
                .replace("_end", "");
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

        return selectedIds;
    }

    setValue(newValues) {
        // More details are here https://select2.org/programmatic-control/add-select-clear-items#preselecting-options-in-an-remotely-sourced-ajax-select2

        let requestPromises = [];
        newValues.forEach((newValue) => {
            const promise = fetchItemFromBackendById(this.getType(), newValue);
            requestPromises.push(promise);
        })

        Promise.all(requestPromises)
        .then((responses) => {
            responses.forEach((response) => {
                var option = new Option(response.name, response.id, true, true);
                $(this.element).append(option).trigger('change');
            });
            
            $(this.element).trigger({
                type: 'select2:select',
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

        return selections.map(selection => {
            // Find the corresponding <option> element
            const option = $(this.element).find(`option[value="${selection.id}"]`)[0];

            // Default assignedType in case data-attribute isn't set
            let assignedType = null;

            if (option) {
                assignedType = option.getAttribute('data-assigned-type');
            }

            // If data-assigned-type is missing, fallback to 'type' from Select2 selection (if available)
            if (!assignedType && selection.type) {
                assignedType = "App\\Models\\" + selection.type.charAt(0).toUpperCase() + selection.type.slice(1);
            }

            return {
                assignedType,
                assigned_to: parseInt(selection.id)
            };
        });
    }


    setValue(newValues) {
        // More details are here https://select2.org/programmatic-control/add-select-clear-items#preselecting-options-in-an-remotely-sourced-ajax-select2

        const requestPromises = newValues.map(({ assignedType, assigned_to }) => {
            const type = {
                "App\\Models\\Asset": "asset",
                "App\\Models\\Location": "location",
                "App\\Models\\User": "user"
            }[assignedType];

            return fetchItemFromBackendById(type, assigned_to)
                .then(response => ({ ...response, assignedType }));
        });

        Promise.all(requestPromises).then((responses) => {
                responses.forEach(({ id, name, assignedType }) => {
                const displayName = name || `#${id}`;
                const option = new Option(displayName, id, true, true);

                option.setAttribute('data-assigned-type', assignedType);

                $(this.element).append(option).trigger('change');
            });

            $(this.element).trigger({ type: 'select2:select' });
        });
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
        this.clearAll();

        for (const key in response) {
            const value = response[key];

            // Find the matching input by the `name` attribute
            const field = this.inputs.find(input => input.key === key);
            if (!field) {
                console.warn(`No input found for key: ${key}`);
                continue;
            }
            
            const type = field.getType();

            try {
                field.setValue(value);
            } catch (err) {
                console.error(`Failed to set value for "${key}":`, err);
            }
        }
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
            const demoDataObject = {
                                        category :[1, 3, 5, 7, 9],
                                        manufacturer:[1,3,7, 9, 11, 13, 15],
                                        asset_eol_date_end :"2027-10-31",
                                        "custom_fields._snipeit_cpu_4": "Core i7",
                                        assigned_to: [
                                            {
                                              assignedType: "App\\Models\\Location",
                                              assigned_to: 6071
                                            },
                                            {
                                              assignedType: "App\\Models\\Asset",
                                              assigned_to: 42657
                                            }
                                        ]
                                    };
            setDemoDataButton.addEventListener('click', () => this.collector.setValuesFromResponse(demoDataObject));
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
        typeSingularToPluralMap.set("asset", "hardware");
        typeSingularToPluralMap.set("category", "categories");
        typeSingularToPluralMap.set("company", "companies");
        typeSingularToPluralMap.set("location", "locations");
        typeSingularToPluralMap.set("manufacturer", "manufacturers");
        typeSingularToPluralMap.set("model", "models");
        typeSingularToPluralMap.set("rtd_location", "locations");
        typeSingularToPluralMap.set("status_label", "statuslabels");
        typeSingularToPluralMap.set("supplier", "suppliers");
        typeSingularToPluralMap.set("user", "users");

        if(!typeSingularToPluralMap.has(type)) {
            console.error("Invalid type " + type);
            reject("Invalid type " + type);
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
