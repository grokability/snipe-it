@php
//set array up before loop so it doesn't get wiped at every iteration
    $fields = [];
    $anyModelHasCustomFields = 0;
@endphp

@foreach($models as $model)
    @if (($model) && ($model->fieldset ? $model->fieldset->count() > 0 : false))
        @php
            $anyModelHasCustomFields++;
        @endphp
    @endif
@endforeach

@if ($anyModelHasCustomFields > 0)
    <fieldset name="custom-fields" class="bottom-padded">
        <legend class="highlight">
            {{ trans('admin/custom_fields/general.custom_fields') }}
        </legend>
@endif

@foreach($models as $model)
@if (($model) && ($model->fieldset))
    @foreach($model->fieldset->fields AS $field)
        @php
        //prevents some duplicate queries - open to a better way of skipping dupes in output
        //its ugly, but if we'd rather deal with duplicate queries we can get rid of this. 
            if (in_array($field->db_column_name(), $fields)) {
                $duplicate = true;
                continue; 
            } else {
                $duplicate = false;
            }
            $fields[] = $field->db_column_name(); 
        @endphp

    <div class="form-group{{ $errors->has($field->db_column_name()) ? ' has-error' : '' }}">
      <label for="{{ $field->db_column_name() }}" class="col-md-3 control-label">{{ $field->name }} </label>
      <div class="col-md-7 col-sm-12{{ ($field->pivot->required=='1') ? ' required' : '' }}">

          @if ($field->element!='text')
              <!-- Listbox -->
              @if ($field->element=='listbox')
                  <x-input.select
                      :name="$field->db_column_name()"
                      :options="$field->formatFieldValuesAsArray()"
                      :selected="old($field->db_column_name(),(isset($item) ? Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}) : $field->defaultValue($model->id)))"
                      class="format form-control"
                  />

              @elseif ($field->element=='textarea')
                @if($field->is_unique)
                    <input type="text" class="form-control" disabled value="{{ trans('/admin/hardware/form.bulk_update_custom_field_unique') }}">
                @endif
                @if(!$field->is_unique)
                    <textarea class="col-md-6 form-control" id="{{ $field->db_column_name() }}" name="{{ $field->db_column_name() }}">{{ old($field->db_column_name(),(isset($item) ? Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}) : $field->defaultValue($model->id))) }}</textarea>
                @endif
              @elseif ($field->element=='checkbox')
                    <!-- Checkboxes -->
              @php
                  $fieldName = $field->db_column_name();
                  $oldValues = old($fieldName);
                  $defaultValues = array_map('trim', explode(',', $field->defaultValue($model->id)));
                  $currentValues = isset($item) ? array_map('trim', explode(',', $item->{$fieldName})) : $defaultValues;

                  $selectedValues = is_array($oldValues) ? $oldValues : $currentValues;
              @endphp

              @foreach ($field->formatFieldValuesAsArray() as $key => $value)
                  <label class="form-control">
                      <input type="checkbox"
                             name="{{ $fieldName }}[]"
                             value="{{ $key }}"
                              {{ in_array($key, $selectedValues) ? 'checked' : '' }}>
                      {{ $value }}
                  </label>
              @endforeach
            @elseif ($field->element=='radio')
                  @php
                      $fieldName = $field->db_column_name();
                      $oldValue = old($fieldName);
                      $default = trim($field->defaultValue($model->id));
                      $current = isset($item) ? trim($item->{$fieldName}) : $default;

                      $selectedValue = $oldValue !== null ? $oldValue : $current;
                  @endphp
                  @foreach ($field->formatFieldValuesAsArray() as $key => $value)
                      <label class="form-control">
                          <input type="radio"
                                 name="{{ $fieldName }}"
                                 value="{{ $key }}"
                                  {{ $selectedValue == $key ? 'checked' : '' }}>
                          {{ $value }}
                      </label>
                  @endforeach
                <button type="button"
                        class="btn btn-default btn-xs clear-radio"
                        data-target-name="{{ $field->db_column_name() }}">
                    {{ trans('/admin/hardware/general.clear') }}
                </button>
            @endif

            @else
            <!-- Date field -->

            @if ($field->format=='DATE')

            <div class="input-group col-md-5" style="padding-left: 0px;">
                <div class="input-group date" data-provide="datepicker" data-date-format="yyyy-mm-dd" data-autoclose="true" data-date-clear-btn="true">
                    <input type="text" class="form-control" placeholder="{{ trans('general.select_date') }}" name="{{ $field->db_column_name() }}" id="{{ $field->db_column_name() }}" readonly value="{{ old($field->db_column_name(),(isset($item) ? Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}) : $field->defaultValue($model->id))) }}"  style="background-color:inherit">
                    <span class="input-group-addon"><x-icon type="calendar" /></span>
                </div>
            </div>


                @else
                    
                    @if (($field->field_encrypted=='0') || (Gate::allows('admin')))
                        @if ($field->is_unique) 
                                <input type="text" class="form-control" disabled value="{{trans('/admin/hardware/form.bulk_update_custom_field_unique')}}">
                            @endif  
                        @if(!$field->is_unique) 
                                <input type="text" value="{{ old($field->db_column_name(),(isset($item) ? Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}) : $field->defaultValue($model->id))) }}" id="{{ $field->db_column_name() }}" class="form-control" name="{{ $field->db_column_name() }}" placeholder="Enter {{ strtolower($field->format) }} text">
                        @endif 
                            @else
                                <input type="text" value="{{ strtoupper(trans('admin/custom_fields/general.encrypted')) }}" class="form-control disabled" disabled>
                    @endif
                   
                @endif

          @endif

          <p class="help-block">
              <x-icon type="warning" class="text-info" /> {{ trans('admin/hardware/form.bulk_update_model_prefix') }}:
              @foreach ($field->assetModels()->pluck('name')->intersect($modelNames) as $modelName)
                  <span class="label label-default">
                {{ $modelName }}
            </span>&nbsp;
              @endforeach
          </p>

        @if ($field->help_text!='')
            <p class="help-block">{{ $field->help_text }}</p>
        @endif


              
              

          <?php
          $errormessage=$errors->first($field->db_column_name());
          if ($errormessage) {
              $errormessage=preg_replace('/ snipeit /', '', $errormessage);
              print('<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> '.$errormessage.'</span>');
          }
            ?>
      </div>

        @if ($field->field_encrypted)
        <div class="col-md-1 col-sm-1 text-left">
            <i class="fas fa-lock" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/custom_fields/general.value_encrypted') }}"></i>
        </div>
        @endif

        <div class="col-md-8 col-md-offset-3" style="padding-bottom: 10px;">
            <label class="form-control">
                <input type="checkbox" name="{{ 'null'.$field->db_column_name() }}" value="1">
                {{ trans_choice('general.set_to_null', count($assets),['selection_count' => count($assets)]) }}
            </label>
        </div>
    </div>
    @endforeach
@endif
 @endforeach
@if ($anyModelHasCustomFields > 0)
    </fieldset>
@endif
