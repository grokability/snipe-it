<span class="advancedSearchWrapper">
<span class="advancedSearchGridContainer">
   @foreach ($layout as $tableField)
      @if ((!empty($tableField->searchable) && $tableField->searchable === true))
            <span id="advancedSearch_{{ $tableField->field }}" class="advancedSearchItemContainer filter-item" data-filter-name="{{ strtolower($tableField->title) }}">
               <label for="advancedSearch_{{ $tableField->field }}" class="filterFieldName">
                  <b>{{ $tableField->title }}</b>
               </label>

               <div class="filter-controls-row">
                <select class="form-control filter-option" data-field="{{ $tableField->field }}">
                    <option value="AND_contains"> && ~= </option>
                    <option value="AND_equals"> && == </option>
                    <option value="NOT_equals"> != </option>
                    <option value="NOT_contains"> ~! </option>
                </select>


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
                           'allow_tags' => 'true',
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
                           'allow_tags' => 'true',
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
                           'allow_tags' => 'true',
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
                        'allow_tags' => 'true',
                     ])
                     @break
                     @case('modelsLinkObjFormatter')
                        @include ('partials.select.dropdowns.model-select', [
                           'translated_name' => trans('admin/hardware/form.model'),
                           'select_id' => "advancedSearch_$tableField->field",
                           'fieldname' => $tableField->field,
                           'required' => 'false',
                           'multiple' => 'true',
                           'allow_tags' => 'true',
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
                           'allow_tags' => 'true',
                        ])
                        @break
                     @case('statuslabelsLinkObjFormatter')
                        @include ('partials.select.dropdowns.status-select', [
                           'translated_name' => trans('admin/hardware/status.model'),
                           'select_id' => "advancedSearch_$tableField->field",
                           'fieldname' => $tableField->field,
                           'required' => 'false',
                           'multiple' => 'true',
                           'allow_tags' => 'true',
                        ])
                        @break
                     @case('suppliersLinkObjFormatter')
                        @include ('partials.select.dropdowns.supplier-select', [
                           'translated_name' => trans('admin/hardware/supplier.model'),
                           'select_id' => "advancedSearch_$tableField->field",
                           'fieldname' => $tableField->field,
                           'required' => 'false',
                           'multiple' => 'true',
                           'allow_tags' => 'true',
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
                           'allow_tags' => 'true',
                        ])
                        @break
                     @default
                        <input class="advancedSearch_defaultField form-control" type="text" autocomplete="on" id="advancedSearch_{{ $tableField->field }}_input" >
                  @endswitch
               @endif
                    </div>
         </span>
      @endif
   @endforeach
</span>
</span>

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
        if (value === null || value === undefined || value === '') {
            return;
        }

        const field = this.key;
        const filterOptionSelect = document.querySelector(`.filter-option[data-field="${field}"]`)

        let operator = "contains";
        let logic = "AND";
        switch(filterOptionSelect.value) {
            case "AND_equals":
                operator = "equals";
                logic = "AND";
                break;
            case "AND_contains":
                operator = "contains";
                logic = "AND";
                break;
            case "NOT_equals":
                operator = "equals";
                logic = "NOT"
                break;
            case "NOT_contains":
                operator = "contains";
                logic = "NOT";
                break;
        }
        
        filters.push({
            field,
            value,
            operator,
            logic
        })
    }

    clear() {
        this.element.value = "";
    }
}

class SelectFilterInput extends FilterInput {
    getValue() {
        const selections = $(this.element).select2('data');

        const selectedValues = selections.map(item => {
            const parseId = parseInt(item.id);
           return isNaN(parseId) ? item.id : parseId;
        })

        if (selectedValues.length === 0) {
            return null;
        }

        return selectedValues;
    }

    setValue(newValues, type = this.getType()) {
        let requestPromises = newValues.map((newValue) => {
            return fetchItemFromBackendById(type, newValue);
        });
    
        return Promise.all(requestPromises).then((responses) => {
            responses.forEach((response) => {
                response.json().then((responseJson) => {
                    // Check if option already exists
                    let $existingOption = $(this.element).find(`option[value='${responseJson.id}']`);
                
                    if ($existingOption.length === 0) {
                        // Option doesn't exist, create and append it
                        let option = new Option(responseJson.name, responseJson.id, true, true);
                        $(this.element).append(option);
                    } else {
                        // Option exists, just select it
                        $existingOption.prop('selected', true);
                    }
                
                    // Trigger change once per response (or once after all?)
                    $(this.element).trigger('change');
                });
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

    setValue(newValues, type = this.getType()) {
        // Map each new value to a fetch request
        let requestPromises = newValues.map((newValue) => {
            return fetchItemFromBackendById(type, newValue);
        });

        // Wait for all fetches to complete
        return Promise.all(requestPromises).then((responses) => {
            // For each response, parse JSON and prepare to update select options
            let appendPromises = responses.map(response =>
                response.json().then(responseJson => {
                    // Check if option with this ID already exists
                    let $existingOption = $(this.element).find(`option[value='${responseJson.id}']`);

                    if ($existingOption.length === 0) {
                        // Option does not exist, create and append new one (selected)
                        let option = new Option(responseJson.name, responseJson.id, true, true);
                        $(this.element).append(option);
                    } else {
                        // Option exists, mark it selected (in case it was not)
                        $existingOption.prop('selected', true);
                    }
                })
            );

            // Wait for all JSON processing and DOM updates to finish
            return Promise.all(appendPromises).then(() => {
                // Trigger change event once, so Select2 updates UI properly
                $(this.element).trigger('change');
            });
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
        this.filters = [];
        this.inputs = [];
    }

    collect() {
        this.filters = [];
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

        for (const filter of response) {
            const {field, value} = filter;

            const input = this.inputs.find(input => input.key === field);
            if (!input) {
                console.warn(`No input found for key: ${field}`);
                continue;
            }

            try {
                const result = input.setValue(value);
                // If the method returns a promise, store it
                if (result instanceof Promise) {
                    promises.push(result);
                }
            } catch (err) {
                console.error(`Failed to set value for "${field}":`, err);
            }
        }

        // Wait for all async setValue calls to complete
        await Promise.all(promises);
        setAdvancedSearchPanelFilterEnabledState(false);
    }
}
</script>

<style>
/* 
Layout container for the whole page.
Uses flexbox so the filter and table sections can sit side by side on desktop, and stack on mobile.
*/
.responsive-layout {
    display: flex;
    flex-wrap: wrap;
    width: 100%;
}

/* 
The filter (sidebar) section.
Transition allows smooth showing/hiding.
*/
.filter-section {
    transition: all 0.3s ease;
}

/* 
When .hide is applied, the filter section is hidden.
!important ensures it's forced, even if overridden by other classes.
*/
.filter-section.hide {
    display: none !important;
}

/* New CSS for the advanced search layout */
.advancedSearchWrapper {
    width: 100%;
    padding: 0 15px;
}

.advancedSearchGridContainer {
    display: flex;
    flex-direction: column;
    gap: 20px; /* Space between each filter item */
}

.advancedSearchItemContainer {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

/* Heading takes full width on its own row */
.filterFieldName {
    font-weight: 600;
    font-size: 14px;
    margin: 0;
    color: #333;
}

/* Container for operator dropdown + input */
.filter-controls-row {
    display: flex;
    gap: 0;
    width: 100%;
}

/* Operator dropdown - smaller width */
.filter-option {
    flex: 0 0 60px; /* Fixed width for consistency */
    min-width: 30px;
    max-width: 60px;
    height: 38px;
    padding: 6px 8px;
    font-size: 13px;
    border: 1px solid #ced4da;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    background-color: #fff;
    border-right: none;
}

/* Input field - takes remaining space */
.advancedSearch_defaultField,
.advancedSearchGridContainer .form-control:not(.filter-option),
.select2-container {
    flex: 1;
    height: 38px;
    font-size: 13px;
    border: 1px solid #ced4da;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    background-color: #fff;
}

.advancedSearch_defaultField,
.advancedSearchGridContainer .form-control:not(.filter-option) {
    padding: 6px 10px;
}

/* Select2 specific styling */
.select2-container {
    width: auto;
    box-sizing: border-box;
}

.select2-container--default .select2-selection--single,
.select2-container--default .select2-selection--multiple {
    height: 38px;
    border: 1px solid #ced4da;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    border-left: none;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px;
    padding-left: 10px;
}

/* Focus states */
.filter-option:focus,
.advancedSearch_defaultField:focus,
.form-control:focus {
    border-color: #6c63ff;
    box-shadow: 0 0 0 2px rgba(108, 99, 255, 0.08);
    outline: none;
}

.select2-container--default .select2-selection:focus {
    border-color: #6c63ff;
    box-shadow: 0 0 0 2px rgba(108, 99, 255, 0.08);
    outline: none;
}

/* Date range styling */
.input-daterange {
    display: flex;
    align-items: center;
    flex: 1;
}

.input-daterange .form-control {
    border-radius: 0;
    border-left: none;
}

.input-daterange .form-control:first-child {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

.input-daterange .form-control:last-child {
    border-top-right-radius: 4px;
    border-bottom-right-radius: 4px;
}

.input-daterange .input-group-addon {
    padding: 0 8px;
    font-size: 12px;
    color: #666;
    white-space: nowrap;
}

/* ---------- DESKTOP Styles (screen ≥ 768px) ---------- */
@media (min-width: 768px) {
    /* 
    Filter sidebar gets 25% width, and some space on the right.
    */
    .filter-section {
        flex: 0 0 25%;
        max-width: 25%;
        padding-right: 15px;
    }

    /* 
    Main table takes the remaining 75%.
    */
    .table-section {
        flex: 0 0 75%;
        max-width: 75%;
    }

    /* 
    If filter is hidden, the table takes full width.
    */
    .filter-section.hide + .table-section {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

/* ---------- MOBILE Styles (screen < 768px) ---------- */
@media (max-width: 767px) {
    /* 
    Filter takes full width, and sits above the table section.
    */
    .filter-section {
        width: 100%;
        margin-bottom: 15px;
    }

    .table-section {
        width: 100%;
    }
    
    /* Adjust operator dropdown width on mobile */
    .filter-option {
        flex: 0 0 120px;
        min-width: 120px;
    }
}
</style>