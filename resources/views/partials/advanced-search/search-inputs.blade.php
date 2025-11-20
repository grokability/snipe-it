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
                    <option value="AND_equals"> = </option>
                    <option value="NOT_equals"> != </option>
                    <option value="AND_contains"> [abc] </option>
                    <option value="NOT_contains"> ![abc] </option>
                </select>


               @if (!isset($tableField->formatter))
                  {{-- Default select if formatter is not set --}}
                  <input class="advancedSearch_defaultField form-control" type="text" autocomplete="on" id="advancedSearch_{{ $tableField->field }}_input" placeholder="{{ $tableField->title }}" >
               @else
                  @switch($tableField->formatter)
                     @case('dateDisplayFormatter')
                        <div class="input-daterange input-group date-range-input" id="advancedSearch_{{ $tableField->field }}">
                           <input type="text" id="advancedSearch_{{ $tableField->field }}_start" autocomplete="off"
                              class="form-control input-daterange-field" name="checkin_date_start" aria-label="checkin_date_start"">
                           <div class="input-group-addon">{{ strtolower(trans('general.to')) }}</div>
                           <input type="text" id="advancedSearch_{{ $tableField->field }}_end" autocomplete="off"
                              class="form-control input-daterange-field" name="checkin_date_end" aria-label="checkin_date_end"">
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
                        <input class="advancedSearch_employeeNumFormatter form-control" type="text" id="advancedSearch_{{ $tableField->field }}_input" placeholder="{{ $tableField->title }}" autocomplete="on">
                        @break
                     @case('hardwareLinkFormatter')
                        <input class="advancedSearch_hardwarelinkFormatter form-control" type="text" id="advancedSearch_{{ $tableField->field }}_input" placeholder="{{ $tableField->title }}" autocomplete="on">
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
                        <input class="advancedSearch_orderNumberObjFilterFormatter form-control" type="text" id="advancedSearch_{{ $tableField->field }}_input" placeholder="{{ trans('general.order_number') }}" autocomplete="on">
                        @break
                     @case('polymorphicItemFormatter')
                     
                           <select id="advancedSearch_{{ $tableField->field }}_input_type" class="form-control filter-option advancedSearch_polymorphicItemFormatter_type no-select2">
                              <option value="{{ \App\Models\Asset::class }}"> &#128421; ({{ trans('general.asset') }})</option>
                              <option value="{{ \App\Models\Location::class }}"> &#127968;  ({{ trans('general.location') }})</option>
                              <option value="{{ \App\Models\User::class }}"> &#129485;  ({{ trans('general.user') }})</option>
                           </select>
                        
                        <input class="advancedSearch_polymorphicItemFormatter form-control" type="text" autocomplete="on" id="advancedSearch_{{ $tableField->field }}_input" placeholder="{{ trans('admin/hardware/form.checkedout_to') }}">
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
                        <input class="advancedSearch_customField form-control" type="text" autocomplete="on" id="advancedSearch_{{ $tableField->field }}_input" placeholder="{{ $tableField->title }}">
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
                        <input class="advancedSearch_defaultField form-control" type="text" autocomplete="on" id="advancedSearch_{{ $tableField->field }}_input" placeholder="{{ $tableField->title }}">
                  @endswitch
               @endif
                    </div>
         </span>
      @endif
   @endforeach
</span>
</span>

<script src="{{ url(mix('js/dist/search-inputs.min.js')) }}"></script>