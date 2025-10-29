<span class="advancedSearchWrapper">
   @push('css')
      <link rel="stylesheet" href="{{ mix('css/dist/filterInputs.min.css') }}">
   @endpush
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

<script>
document.addEventListener('DOMContentLoaded', () => {
   // Store original heights for each Select2 instance
   const originalHeights = new Map();

   // When a Select2 dropdown opens
   $(document).on('select2:open', (e) => {
      if(e.target.classList.value.includes("expandOnFocus") === false) {
         return;
      }

      const selectEl = e.target;
      const $select = $(selectEl);
      const $container = $select.next('.select2-container');

      if ($container.length) {
         // Store the original height before we change it
         if (!originalHeights.has(selectEl.id)) {
            const originalHeight = $container.find('.select2-selection').outerHeight();
            originalHeights.set(selectEl.id, originalHeight);
         }

         // Expand the dropdown container
         $container.css({
            'height': 'auto',
            'min-height': '150px'
         });
         
         $container.find('.select2-selection--multiple').css({
            'height': 'auto',
            'min-height': '100px',
            'max-height': '200px',
            'overflow-y': 'auto'
         });
         
         $container.find('.select2-selection--single').css({
            'height': 'auto',
            'min-height': '38px'
         });
      }
   });

   // When a Select2 dropdown closes (loses focus)
   $(document).on('select2:close', (e) => {

      if(e.target.classList.value.includes("expandOnFocus") === false) {
         return;
      }

      const selectEl = e.target;
      const $select = $(selectEl);
      const $container = $select.next('.select2-container');

      if ($container.length) {
         // Reset to original height
         const originalHeight = originalHeights.get(selectEl.id) || '38px';
         
         $container.css({
            'height': originalHeight,
            'min-height': ''
         });
         
         $container.find('.select2-selection--multiple').css({
            'height': originalHeight,
            'min-height': '',
            'max-height': '',
            'overflow-y': ''
         });
         
         $container.find('.select2-selection--single').css({
            'height': originalHeight,
            'min-height': ''
         });
      }
   });
});
</script>

<style>

</style>