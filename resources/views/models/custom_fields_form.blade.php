@if (($model) && ($model->fieldset) && $model->fieldset->displayAnyFieldsInForm($show_custom_fields_type ?? ''))
    <div class="col-md-12 col-sm-12">

    <fieldset name="custom-fields">
        <x-form.legend
                help_text="{!! trans('admin/custom_fields/general.general_help_text') !!}">

            {{ trans('admin/custom_fields/general.custom_fields') }}
        </x-form.legend>

  @foreach($model->fieldset->fields as $field)
    @if (!isset($show_custom_fields_type) || ($field->displayFieldInCurrentForm($show_custom_fields_type)))


    <div class="form-group{{ $errors->has($field->db_column_name()) ? ' has-error' : '' }}">


      <label for="{{ $field->db_column_name() }}" class="col-md-3 control-label">

          @if ($field->field_encrypted)
              <i class="fas fa-lock" data-tooltip="true" data-placement="top" title="{{ trans('admin/custom_fields/general.value_encrypted') }}"></i>
          @endif

          {{ $field->name }}

      </label>

        <div class="col-md-8 col-sm-12">

          @if ($field->element!='text')

              @if ($field->element=='listbox')
                  <!-- Listbox -->
                  <x-input.select
                      :name="$field->db_column_name()"
                      :options="$field->formatFieldValuesAsArray()"
                      :selected="old($field->db_column_name(), Helper::customFieldFormValue($field, $item ?? null, $model))"
                      :required="$field->pivot->required == '1'"
                      class="format form-control"
                  />

              @elseif ($field->element=='textarea')
                  <!-- Textarea -->
                    <textarea rows="6" class="col-md-6 form-control" id="{{ $field->db_column_name() }}" name="{{ $field->db_column_name() }}"{{ ($field->pivot->required=='1') ? ' required' : '' }}>{{ old($field->db_column_name(), Helper::customFieldFormValue($field, $item ?? null, $model)) }}</textarea>

                @elseif ($field->element=='markdown-textarea')
                    <!-- Markdown Textarea -->
                    <textarea rows="6" class="col-md-6 form-control" id="{{ $field->db_column_name() }}" name="{{ $field->db_column_name() }}"{{ ($field->pivot->required=='1') ? ' required' : '' }}>{{ old($field->db_column_name(), Helper::customFieldFormValue($field, $item ?? null, $model)) }}</textarea>
                    <x-form.help :name="$field->db_column_name()" icon="markdown">
                        {{ trans('general.markdown') }}
                    </x-form.help>

              @elseif ($field->element=='checkbox')
                  <!-- Checkbox -->
                  @php
                      $checkboxDefault = isset($item)
                          ? array_map('trim', explode(',', $item->{$field->db_column_name()}))
                          : array_map('trim', explode(',', $field->defaultValue($model->id)));
                  @endphp
                  <x-input.select
                      :name="$field->db_column_name().'[]'"
                      :options="$field->formatFieldValuesAsArray()"
                      :selected="old($field->db_column_name(), $checkboxDefault)"
                      :required="$field->pivot->required == '1'"
                      multiple
                      class="format form-control"
                  />

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

              @elseif ($field->element=='date_picker')
                  {{-- Datepicker widget on a plain TEXT column (allows the
                       field to be encrypted). Format DATE uses a separate
                       native-column path below. --}}
                  <div class="input-group col-md-5" style="padding-left: 0px;">
                      <x-input.datepicker
                          id="{{ $field->db_column_name() }}"
                          name="{{ $field->db_column_name() }}"
                          :value="old($field->db_column_name(), Helper::customFieldFormValue($field, $item ?? null, $model))"
                          required="{{ ($field->pivot->required=='1') ? '1' : '' }}"
                      />
                  </div>

              @elseif ($field->element=='datetime_picker')
                  {{-- Datetimepicker widget on a plain TEXT column (allows
                       the field to be encrypted). Format DATETIME uses a
                       separate native-column path below. --}}
                  <div class="input-group col-md-6" style="padding-left: 0px;">
                      <x-input.datetimepicker
                          id="{{ $field->db_column_name() }}"
                          name="{{ $field->db_column_name() }}"
                          :value="old($field->db_column_name(), Helper::customFieldFormValue($field, $item ?? null, $model))"
                          required="{{ ($field->pivot->required=='1') ? '1' : '' }}"
                          :default_now="false"
                      />
                  </div>
              @endif


          @else
            <!-- Date field -->
                @if ($field->format=='DATE')

                        <div class="input-group col-md-5" style="padding-left: 0px;">
                            <x-input.datepicker
                                id="{{ $field->db_column_name() }}"
                                name="{{ $field->db_column_name() }}"
                                :value="old($field->db_column_name(), Helper::customFieldFormValue($field, $item ?? null, $model))"
                                required="{{ ($field->pivot->required=='1') ? '1' : '' }}"
                            />
                        </div>


                @elseif ($field->format=='DATETIME')

                        {{-- Outer wrapper with inline padding-left: 0 to
                             match the DATE case above. Later duplicate
                             .input-group[class*="col-"] CSS rules re-apply
                             15px padding that would otherwise indent the
                             widget; the inline style forces it to zero. --}}
                    <div class="input-group col-md-6" style="padding-left: 0px;">
                            <x-input.datetimepicker
                                id="{{ $field->db_column_name() }}"
                                name="{{ $field->db_column_name() }}"
                                :value="old($field->db_column_name(), Helper::customFieldFormValue($field, $item ?? null, $model))"
                                required="{{ ($field->pivot->required=='1') ? '1' : '' }}"
                                :default_now="false"
                            />
                        </div>


                @else
                    @if (($field->field_encrypted=='0') || (Gate::allows('assets.view.encrypted_custom_fields')))
                        @php
                            $format_icon = \App\Models\CustomField::iconForFormat($field->format);
                            // MAC fields get a client-side input mask (see
                            // window.snipeitInitMacAddressMask in snipeit.js)
                            // that normalizes any hex-shaped paste to the
                            // colon-separated AA:BB:CC:DD:EE:FF form the
                            // backend rule enforces. Both branches below
                            // (with and without $format_icon) need the class
                            // + shared placeholder / maxlength / inputmode /
                            // autocomplete attrs, so they're built once here
                            // and interpolated.
                            $isMac = strtoupper($field->format) === 'MAC';
                            $macClass = $isMac ? ' mac-address-input' : '';
                            // No maxlength: browser-side length caps run
                            // BEFORE the input event fires, so a slightly-
                            // over-length paste (e.g. a stray leading quote
                            // from a copied cell) would get truncated by
                            // the browser and lose a trailing hex char
                            // before the mask's substring(0, 12) had a
                            // chance to normalize. The mask itself is the
                            // authoritative cap.
                            $macAttrs = $isMac ? ' inputmode="text" autocomplete="off"' : '';
                            $placeholder = $isMac ? 'AA:BB:CC:DD:EE:FF' : 'Enter '.strtolower($field->format).' text';
                        @endphp
                        @if ($format_icon)
                            <div class="input-group">
                                <input type="text" value="{{ old($field->db_column_name(),(isset($item) ? Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}) : $field->defaultValue($model->id))) }}" id="{{ $field->db_column_name() }}" class="form-control{{ $macClass }}" name="{{ $field->db_column_name() }}" placeholder="{{ $placeholder }}"{!! $macAttrs !!}{{ ($field->pivot->required=='1') ? ' required' : '' }}>
                                <span class="input-group-addon"><x-icon :type="$format_icon" /></span>
                            </div>
                        @else
                            <input type="text" value="{{ old($field->db_column_name(),(isset($item) ? Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}) : $field->defaultValue($model->id))) }}" id="{{ $field->db_column_name() }}" class="form-control{{ $macClass }}" name="{{ $field->db_column_name() }}" placeholder="{{ $placeholder }}"{!! $macAttrs !!}{{ ($field->pivot->required=='1') ? ' required' : '' }}>
                        @endif
                    @else
                        <input type="text" value="{{ strtoupper(trans('admin/custom_fields/general.encrypted')) }}" class="form-control disabled" disabled>
                    @endif
                @endif

          @endif

              @if (count(\App\Presenters\CustomFieldPresenter::visibilityIconsArray($field)) > 0)
                  @if ($field->help_text != '')
                      <p class="help-block">
                          {{ $field->help_text }}
                          <br>{!! \App\Presenters\CustomFieldPresenter::visibilityIcons($field) !!}
                      </p>
                  @else
                      <div class="help-block">
                          {!! \App\Presenters\CustomFieldPresenter::visibilityIcons($field) !!}
                      </div>
                  @endif
              @elseif ($field->help_text != '')
                  <p class="help-block">{{ $field->help_text }}</p>
              @endif

                  <?php
                  $errormessage = $errors->first($field->db_column_name());
                  if ($errormessage) {
                      $errormessage = preg_replace('/ snipeit /', '', $errormessage);
                      echo '<span class="alert-msg" role="alert" aria-live="assertive">'.$errormessage.'</span>';
                  }
                  ?>
      </div>


    </div>
            @endif
  @endforeach
    </fieldset>
    </div>
@endif
