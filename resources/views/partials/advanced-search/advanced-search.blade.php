<div class="container">
    @include('partials.confetti-js', ['autostart' => false])
    <div id="advancedSearchPanel" class="panel panel-default">
            @include ('partials.select.dropdowns.predefined-select', [
                'translated_name' => trans('admin/hardware/company.model'),
                'fieldname' => 'predefinedFilters',
                'select_id' => "predefinedfilters-select",
                'required' => 'false',
            ])
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
                        <hr/>
                        <button type="button" class="btn btn-default" id="storeFilterButton">
                            <span aria-hidden="true"></span>
                            <span>Store current filter in backend</span>
                        </button>
                        <button type="button" class="btn btn-default" id="updateFilterButton">
                            <span aria-hidden="true"></span>
                            <span>Update current filter in backend</span>
                        </button>
                        <button type="button" class="btn btn-default" id="deleteFilterButton">
                            <span aria-hidden="true"></span>
                            <span>Delete current filter from backend</span>
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
        let requestPromises = newValues.map((newValue) => {
            return fetchItemFromBackendById(this.getType(), newValue);
        });

        return Promise.all(requestPromises).then((responses) => {
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

class AssignedEntityFilterInput extends SelectFilterInput {
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
        const requestPromises = newValues.map(({ assignedType, assigned_to }) => {
            const type = {
                "App\\Models\\Asset": "asset",
                "App\\Models\\Location": "location",
                "App\\Models\\User": "user"
            }[assignedType];

            return fetchItemFromBackendById(type, assigned_to)
                .then(response => ({ ...response, assignedType }));
        });

        return Promise.all(requestPromises).then((responses) => {
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

class FilterFormManager {
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
                this.inputs.push(new AssignedEntityFilterInput(el));
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

    async setValuesFromResponse(response) {
        this.clearAll();

        const promises = [];

        for (const key in response) {
            const value = response[key];

            const field = this.inputs.find(input => input.key === key);
            if (!field) {
                console.warn(`No input found for key: ${key}`);
                continue;
            }

            try {
                const result = field.setValue(value);
                // If the method returns a promise, store it
                if (result instanceof Promise) {
                    promises.push(result);
                }
            } catch (err) {
                console.error(`Failed to set value for "${key}":`, err);
            }
        }

        // Wait for all async setValue calls to complete
        await Promise.all(promises);
        setAdvancedSearchPanelState(false);
    }
}

class FilterUIController {
    constructor(tableElement) {
        this.$table = tableElement;
        this.collector = new FilterFormManager();
    }

    refresh() {
        const filters = this.collector.collect();
        this.$table.bootstrapTable('refresh', {
            query: {
                filter: JSON.stringify(filters)
            }
        });
    }

    async updateFilterWithPredefined(event) {
        const selectedId = event?.target?.value;
        if (!selectedId) return;

        setAdvancedSearchPanelState(true);

        try {
            const filterData = await this.fetchPredefinedFilterData(selectedId);
            if (!filterData) return;

            await this.collector.setValuesFromResponse(filterData.filter_data); // Await here
            this.refresh();
        } catch (err) {
            console.error("Failed to apply predefined filter:", err);
            alert("Failed to apply predefined filter");
            setAdvancedSearchPanelState(false);
        }
    }


    storePredefinedFilterInBackend() {
        const filters = this.collector.collect();
        const name = prompt("Enter the name of the filter");
        const payload = {
            name: name,
            filter_data: filters,
        };
        fetchFromBackend('POST', `/api/v1/predefinedFilters`, JSON.stringify(payload))
        .then((response) => {
            if(response.message === "Template saved successfully") {
                alert("Filter stored successfully");
                if(window.triggerConfetti) window.triggerConfetti();
            } else {
                console.error(response);
                alert("An error has occured. Look in the browser console for more details.");  
            }
        })
        .catch((error) => {
            console.error(error);
            alert("An error has occured: " + error);
        })
    }

    updatePredefinedFilterInBackend() {
        const selectedFilter = $("#predefinedfilters-select").select2('data')[0]; // Always zero because only one element can be selected at the time
        if (!selectedFilter) return;
        const filters = this.collector.collect();
        const name = prompt("Enter the name of the filter", selectedFilter.text);

        const payload = {
            name: name,
            filter_data: filters,
        };

        fetchFromBackend('PUT', `/api/v1/predefinedFilters/${selectedFilter.id}`, JSON.stringify(payload))
        .then((response) => {
            if(response.message === "Template updated successfully") {
                alert("Filter stored successfully");
                if(window.triggerConfetti) window.triggerConfetti();
            } else {
                console.error(response);
                alert("An error has occured. Look in the browser console for more details.");  
            }
        })
        .catch((error) => {
            console.error(error);
            alert("An error has occured: " + error);
        })
    }

    deletePredefinedFilterFromBackend() {
        const selectedId = $("#predefinedfilters-select").select2('data')[0].id; // Always zero because only one element can be selected at the time
        if (!selectedId) return;

        fetchFromBackend('DELETE', `/api/v1/predefinedFilters/${selectedId}`)
        .then((response) => {
            if(response.message === "Template deleted") {
                alert("Filter deleted successfully");
                if(window.triggerConfetti) window.triggerConfetti();
            } else {
                console.error(response);
                alert("An error has occured. Look in the browser console for more details.");  
            }
        })
        .catch((error) => {
            console.error(error);
            alert("An error has occured: " + error);
        })
    }

    fetchPredefinedFilterData(filterId) {
        return fetchFromBackend('GET', `/api/v1/predefinedFilters/${filterId}`);
    }

    bindEvents() {
        document.querySelectorAll('[id^="advancedSearch_"]').forEach(el => {
            el.addEventListener('change', this.refresh.bind(this));
        });

        $('#predefinedfilters-select').on('change', (e) => {
            this.updateFilterWithPredefined(e);
        });

        const filterButton = document.getElementById("filterButton");
        if (filterButton) {
            filterButton.addEventListener('click', this.refresh.bind(this));
        }

        const clearButton = document.getElementById("clearInputButton");
        if (clearButton) {
            clearButton.addEventListener('click', () => this.collector.clearAll());
        }

        const saveFilterButton = document.getElementById("storeFilterButton");
        if (saveFilterButton) {
            saveFilterButton.addEventListener('click', () => this.storePredefinedFilterInBackend());
        }

        const updateFilterButton = document.getElementById("updateFilterButton");
        if (updateFilterButton) {
            updateFilterButton.addEventListener('click', () => this.updatePredefinedFilterInBackend());
        }

        const deleteFilterButton = document.getElementById("deleteFilterButton");
        if (deleteFilterButton) {
            deleteFilterButton.addEventListener('click', () => this.deletePredefinedFilterFromBackend());
        }

    }
}

document.addEventListener('DOMContentLoaded', function () {
    const tableId = "{{ request()->has('status') ? e(request()->input('status')) : '' }}assetsListingTable";
    const $table = $('#' + tableId);

    // Initialize everything
    const controller = new FilterUIController($table);
    controller.bindEvents();
});

function getCsrfToken() {
    return $('meta[name="csrf-token"]').attr('content');
}

function fetchFromBackend(method, path, body = null) {
    const options = {
        method: method,
        headers: {
            accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Content-Type': 'application/json'
        },
        ...(body && { body })
    };

    return fetch(path, options)
        .then(res => res.json());
}

function fetchItemFromBackendById(type, id) {
    const typeMap = {
        asset: "hardware",
        category: "categories",
        company: "companies",
        location: "locations",
        manufacturer: "manufacturers",
        model: "models",
        rtd_location: "locations",
        status_label: "statuslabels",
        supplier: "suppliers",
        user: "users"
    };

    if (!typeMap[type]) {
        return Promise.reject(`Invalid type ${type}`);
    }

    const path = `/api/v1/${typeMap[type]}/${id}`;
    return fetchFromBackend('GET', path);
}

function predefinedFilterRequest(method, filterId = null, filterData = null) {
    let  path = "/api/v1/predefinedFilters";

    if(filterId !== null) {
        path += "/" + filterId;
    }

    return fetchFromBackend(method, path, filterData);
}

function setAdvancedSearchPanelState(state) {
    const fields = document.getElementById("advancedSearchPanel").getElementsByTagName('*');
    for(var i = 0; i < fields.length; i++)
    {
        fields[i].disabled = state;
    }
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
