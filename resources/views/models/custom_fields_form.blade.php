@if (($model) && ($model->fieldset) && $model->fieldset->displayAnyFieldsInForm($show_custom_fields_type ?? ''))
    <div class="col-md-12 col-sm-12">

    <fieldset name="custom-fields">
        <x-form-legend
                help_text="{!! trans('admin/custom_fields/general.general_help_text') !!}">

            {{ trans('admin/custom_fields/general.custom_fields') }}
        </x-form-legend>

  @foreach($model->fieldset->fields as $field)
    @if (!isset($show_custom_fields_type) || ($field->displayFieldInCurrentForm($show_custom_fields_type)))


    <div class="form-group{{ $errors->has($field->db_column_name()) ? ' has-error' : '' }}">


      <label for="{{ $field->db_column_name() }}" class="col-md-3 control-label">
          {{ $field->name }}

      </label>

      <div class="col-md-7 col-sm-12">

          @if ($field->element!='text')

              @if ($field->element=='listbox')
                  <!-- Listbox -->
                  <x-input.select
                      :name="$field->db_column_name()"
                      :options="$field->formatFieldValuesAsArray()"
                      :selected="old($field->db_column_name(), (isset($item) ? Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}) : $field->defaultValue($model->id)))"
                      :required="$field->pivot->required == '1'"
                      class="format form-control"
                  />

              @elseif ($field->element=='textarea')
                  <!-- Textarea -->
                  <textarea class="col-md-6 form-control" id="{{ $field->db_column_name() }}" name="{{ $field->db_column_name() }}"{{ ($field->pivot->required=='1') ? ' required' : '' }}>{{ old($field->db_column_name(),(isset($item) ? Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}) : $field->defaultValue($model->id))) }}</textarea>

              @elseif ($field->element=='checkbox')
                  <!-- Checkbox -->
                  @foreach ($field->formatFieldValuesAsArray() as $key => $value)
                      <div>
                          <label class="form-control">
                              <input type="checkbox" value="{{ $value }}" name="{{ $field->db_column_name() }}[]" {{  isset($item) ? (in_array($value, array_map('trim', explode(',', $item->{$field->db_column_name()}))) ? ' checked="checked"' : '') : (old($field->db_column_name()) != '' ? ' checked="checked"' : (in_array($key, array_map('trim', explode(',', $field->defaultValue($model->id)))) ? ' checked="checked"' : '')) }}>
                              {{ $value }}
                          </label>
                      </div>
                  @endforeach

              @elseif ($field->element=='radio')
                  <!-- Radio -->
                  @foreach ($field->formatFieldValuesAsArray() as $value)
                      <div>
                          <label class="form-control">
                              <input type="radio" value="{{ $value }}" name="{{ $field->db_column_name() }}" {{ isset($item) ? ($item->{$field->db_column_name()} == $value ? ' checked="checked"' : '') : (old($field->db_column_name()) != '' ? ' checked="checked"' : (in_array($value, explode(', ', $field->defaultValue($model->id))) ? ' checked="checked"' : '')) }}>
                              {{ $value }}
                          </label>
                      </div>
                  @endforeach
              @endif


          @else
            <!-- Date field -->
                @if ($field->format=='DATE')

                        <div class="input-group col-md-5" style="padding-left: 0px;">
                            <div class="input-group date" data-provide="datepicker" data-date-format="yyyy-mm-dd" data-autoclose="true" data-date-clear-btn="true">
                                <input type="text" class="form-control" placeholder="{{ trans('general.select_date') }}" name="{{ $field->db_column_name() }}" id="{{ $field->db_column_name() }}" readonly value="{{ old($field->db_column_name(),(isset($item) ? Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}) : $field->defaultValue($model->id))) }}"  style="background-color:inherit"{{ ($field->pivot->required=='1') ? ' required' : '' }}>
                                <span class="input-group-addon"><x-icon type="calendar" /></span>
                            </div>
                        </div>


                @else
                    @if (($field->field_encrypted=='0') || (Gate::allows('assets.view.encrypted_custom_fields')))
                    <input type="text" value="{{ old($field->db_column_name(),(isset($item) ? Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}) : $field->defaultValue($model->id))) }}" id="{{ $field->db_column_name() }}" class="form-control" name="{{ $field->db_column_name() }}" placeholder="Enter {{ strtolower($field->format) }} text"{{ ($field->pivot->required=='1') ? ' required' : '' }}>
                        @else
                            <input type="text" value="{{ strtoupper(trans('admin/custom_fields/general.encrypted')) }}" class="form-control disabled" disabled>
                    @endif
                @endif

          @endif

              @if ($field->help_text!='')
              <p class="help-block">{{ $field->help_text }}</p>
              @endif

                  <?php
                  $errormessage = $errors->first($field->db_column_name());
                  if ($errormessage) {
                      $errormessage = preg_replace('/ snipeit /', '', $errormessage);
                      print('<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> '.$errormessage.'</span>');
                  }
                  ?>
      </div>

        @if ($field->field_encrypted)
        <div class="col-md-1 col-sm-1 text-left">
            <i class="fas fa-lock" data-tooltip="true" data-placement="top" title="{{ trans('admin/custom_fields/general.value_encrypted') }}"></i>
        </div>
        @endif

    </div>
            @endif
  @endforeach
    </fieldset>
    </div>
@endif



