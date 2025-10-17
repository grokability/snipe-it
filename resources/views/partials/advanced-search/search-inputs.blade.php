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
                    <option value="AND_contains"> = </option>
                    <option value="AND_equals"> != </option>
                    <option value="NOT_equals"> [abc] </option>
                    <option value="NOT_contains"> ![abc] </option>
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
    max-width: width;
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