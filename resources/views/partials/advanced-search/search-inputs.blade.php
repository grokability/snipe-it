<span>
   @foreach ($layout as $tableField)
      @if ((!empty($tableField->searchable) && $tableField->searchable === true))
            <span id="advancedSearch_{{ $tableField->field }}" class="advancedSearchItemContainer filter-item" data-filter-name="{{ strtolower($tableField->title) }}">
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
                response.json()
                .then((responseJson) => {
                    var option = new Option(responseJson.name, responseJson.id, true, true);
                    $(this.element).append(option).trigger('change');
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


    setValue(newValues) {
       const requestPromises = newValues.map(({ assignedType, assigned_to }) => {
            const type = {
                "App\\Models\\Asset": "asset",
                "App\\Models\\Location": "location",
                "App\\Models\\User": "user"
            }[assignedType];

            return fetchItemFromBackendById(type, assigned_to)
                .then(response => 
                    response.json().then(responseJson => ({
                        ...responseJson,
                        assignedType
                    }))
                );
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
        setAdvancedSearchPanelFilterEnabledState(false);
    }
}
</script>

<style>
/* 
Spacing between filter fields/items.
*/
.filter-item {
    margin-bottom: 15px;
    padding-bottom: 10px;
}

.filter-item:last-child {
    border-bottom: none;
}

/* 
Flexbox layout for each filter field for nice stacking.
*/
.advancedSearchItemContainer {
    display: flex;
    flex-direction: column;
    align-items: stretch;
}

/* 
Spacing for label text in advanced search.
*/
.advancedSearchItemContainer b {
    margin-bottom: 5px;
}

/* 
Make selects stretch to fill the available width.
*/
.advancedSearchItemContainer select.form-control {
    width: 100%;
    box-sizing: border-box;
}

</style>