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
                            <div id="advancedSearch_{{ $tableField->field }}" class="advancedSearchItemContainer">
                                <label for="advancedSearch_{{ $tableField->field }}">
                                    <b>{{ $tableField->title }}</b>
                                </label>
                                @if (!isset($tableField->formatter))
                                    {{-- Default select if formatter is not set --}}
                                    <input class="advancedSearch_defaultField" type="text" autocomplete="on" id="advancedSearch_{{ $tableField->field }}_input" >
                                @else
                                    @switch($tableField->formatter)
                                        @case('dateDisplayFormatter')
                                            <!--<input type="date" id="advancedSearch_{{ $tableField->field }}"
                                                name="{{ $tableField->title }}" class="datePicker">-->
                                            <div class="input-daterange input-group" id="checkin-range-datepicker">
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
                                            <input class="advancedSearch_employeeNumFormatter" type="text" id="advancedSearch_{{ $tableField->field }}_input" autocomplete="on">
                                        @break

                                        @case('hardwareLinkFormatter')
                                            <input class="advancedSearch_hardwarelinkFormatter" type="text" id="advancedSearch_{{ $tableField->field }}_input" autocomplete="on">
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
                                            <input class="advancedSearch_orderNumberObjFilterFormatter" type="text" id="advancedSearch_{{ $tableField->field }}_input" autocomplete="on">
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
                                            <input class="advancedSearch_customField" type="text" autocomplete="on" id="advancedSearch_{{ $tableField->field }}_input" >
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
                                            <input class="advancedSearch_defaultField" type="text" autocomplete="on" id="advancedSearch_{{ $tableField->field }}_input" >
                                    @endswitch
                                @endif
                            </div>
                        @endif
                    @endforeach
                    <button class="button" id="filterButton">Search</button>
                </span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Insert the table ID using PHP
    const tableId = "{{ request()->has('status') ? e(request()->input('status')) : '' }}assetsListingTable";
    const $table = $('#' + tableId);

    /**
     * Extracts the filter key from the element ID
     */
    function getFilterKey(id, prefix = "advancedSearch_") {
        return id.replace(prefix, "").replace("_input", "");
    }

    /**
     * Collects selected values from Select2 dropdowns
     */
    function collectSelectFilters(filters) {
        document.querySelectorAll('select[id^="advancedSearch_"]').forEach(el => {
            const selections = $(el).select2('data');
            const selectedIds = selections
                .map(item => parseInt(item.id))
                .filter(id => !isNaN(id));

            if (selectedIds.length > 0) {

                const key = getFilterKey(el.id);
                if(el.getAttribute('id') ==='advancedSearch_assigned_to') {
                    let assignedToFilters = []
                    selections.forEach(selection => {
                        const type = "App\\Models\\" + selection.type.charAt(0).toUpperCase() + selection.type.slice(1);

                        assignedToFilters.push({
                            assignedType: type,
                            assigned_to: parseInt(selection.id),
                        });
                    });
                    filters[key] = assignedToFilters;
                } else {
                    filters[key] = selectedIds;
                }
            }
        });
    }

    /**
     * Collects values from date inputs (start and end)
     */
    function collectDateFilters(filters) {
        const selector = 'input[id^="advancedSearch_"][id$="_start"][type="date"], input[id^="advancedSearch_"][id$="_end"][type="date"]';
        document.querySelectorAll(selector).forEach(el => {
            if (el.value) {
                const key = getFilterKey(el.id);
                filters[key] = el.value;
            }
        });
    }

    /**
     * Collects values from text inputs
     */
    function collectTextFilters(filters) {
        const selector = 'input[id^="advancedSearch_"][type="text"]';
        document.querySelectorAll(selector).forEach(el => {
            if (el.value) {
                const key = getFilterKey(el.id);
                filters[key] = el.value;
            }
        });
    }

    /**
     * Main filter collector
     */
    function collectAdvancedSearchFilters() {
        const filters = {};
        collectSelectFilters(filters);
        collectDateFilters(filters);
        collectTextFilters(filters);
        return filters;
    }

    /**
     * Refreshes the table with collected filters
     */
    function refreshTableWithAdvancedFilters() {
        const filters = collectAdvancedSearchFilters();
        console.log("Applying Filters:", filters);

        $table.bootstrapTable('refresh', {
            query: {
                filter: JSON.stringify(filters)
            }
        });
    }

    /**
     * Set up listeners
     */
    function bindFilterEvents() {
        // Trigger refresh on any advancedSearch field change
        document.querySelectorAll('[id^="advancedSearch_"]').forEach(el => {
            el.addEventListener('change', refreshTableWithAdvancedFilters);
        });

        // Trigger refresh on filter button click
        const filterButton = document.getElementById("filterButton");
        if (filterButton) {
            filterButton.addEventListener('click', refreshTableWithAdvancedFilters);
        }
    }

    // Init
    bindFilterEvents();
});

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
