
@extends('layouts/edit-form', [
    'createText' => trans('admin/hardware/form.create'),
    'updateText' => trans('admin/hardware/form.update'),
    'topSubmit' => true,
    'helpText' => trans('help.assets'),
    'helpPosition' => 'right',
    'formAction' => ($item->id) ? route('hardware.update', $item) : route('hardware.store'),
    'index_route' => 'hardware.index',
    'options' => [
                'back' => trans('admin/hardware/form.redirect_to_type',['type' => trans('general.previous_page')]),
                'index' => trans('admin/hardware/form.redirect_to_all', ['type' => 'assets']),
                'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.asset')]),
                'other_redirect' => trans('admin/hardware/form.redirect_to_type', [ 'type' => trans('general.asset').' '.trans('general.asset_model')]),
               ]
])


{{-- Page content --}}
@section('inputFields')
    
    @include ('partials.forms.edit.company-select', ['translated_name' => trans('general.company'), 'fieldname' => 'company_id'])


  <!-- Asset Tag -->
  <div class="form-group {{ $errors->has('asset_tag') ? ' has-error' : '' }}">
    <label for="asset_tag" class="col-md-3 control-label">{{ trans('admin/hardware/form.tag') }}</label>



      @if  ($item->id)
          <!-- we are editing an existing asset,  there will be only one asset tag -->
          <div class="col-md-7 col-sm-12">

          <input class="form-control" type="text" name="asset_tags[1]" id="asset_tag" value="{{ old('asset_tag', $item->asset_tag) }}" required>
              {!! $errors->first('asset_tags', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
              {!! $errors->first('asset_tag', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
          </div>
      @else
          <!-- we are creating a new asset - let people use more than one asset tag -->
          <div class="col-md-7 col-sm-12">
              <input class="form-control" type="text" name="asset_tags[1]" id="asset_tag" value="{{ old('asset_tags.1', \App\Models\Asset::autoincrement_asset()) }}" required>
              {!! $errors->first('asset_tags', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
              {!! $errors->first('asset_tag', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
          </div>
          <div class="col-md-2 col-sm-12">
              <button class="add_field_button btn btn-default btn-sm" name="add_field_button">
                  <x-icon type="plus" />
                  <span class="sr-only">
                      {{ trans('general.new') }}
                  </span>
              </button>
          </div>
      @endif
  </div>

    @include ('partials.forms.edit.serial', ['fieldname'=> 'serials[1]', 'old_val_name' => 'serials.1', 'translated_serial' => trans('admin/hardware/form.serial')])

    <div class="input_fields_wrap">
    </div>

    @include ('partials.forms.edit.model-select', ['translated_name' => trans('admin/hardware/form.model'), 'fieldname' => 'model_id', 'field_req' => true])


    @include ('partials.forms.edit.status', [ 'required' => 'true'])
    @if (!$item->id)
        @include ('partials.forms.checkout-selector', ['user_select' => 'true','asset_select' => 'true', 'location_select' => 'true', 'style' => 'display:none;'])
        @include ('partials.forms.edit.user-select', ['translated_name' => trans('admin/hardware/form.checkout_to'), 'fieldname' => 'assigned_user', 'style' => 'display:none;', 'required' => 'false'])
        @include ('partials.forms.edit.asset-select', ['translated_name' => trans('admin/hardware/form.checkout_to'), 'fieldname' => 'assigned_asset', 'style' => 'display:none;', 'required' => 'false'])
        @include ('partials.forms.edit.location-select', ['translated_name' => trans('admin/hardware/form.checkout_to'), 'fieldname' => 'assigned_location', 'style' => 'display:none;', 'required' => 'false'])
    @endif

    @include ('partials.forms.edit.notes')
    @include ('partials.forms.edit.location-select', ['translated_name' => trans('admin/hardware/form.default_location'), 'fieldname' => 'rtd_location_id', 'help_text' => trans('general.rtd_location_help')])
    @include ('partials.forms.edit.requestable', ['requestable_text' => trans('admin/hardware/general.requestable')])



    @include ('partials.forms.edit.image-upload', ['image_path' => app('assets_upload_path')])


    <div id='custom_fields_content'>
        <!-- Custom Fields -->
        @if ($item->model && $item->model->fieldset)
        <?php $model = $item->model; ?>
        @endif
        @if (old('model_id'))
            @php
                $model = \App\Models\AssetModel::find(old('model_id'));
            @endphp
        @elseif (isset($selected_model))
            @php
                $model = $selected_model;
            @endphp
        @endif
        @if (isset($model) && $model)
        @include("models/custom_fields_form",["model" => $model])
        @endif
    </div>


        <div class="col-md-12 col-sm-12">

        <fieldset name="optional-details">

            <x-form-legend>
                <a id="optional_info">
                    <x-icon type="caret-right" id="optional_info_icon" />
                    {{ trans('admin/hardware/form.optional_infos') }}
                </a>
            </x-form-legend>

            <div id="optional_details" class="col-md-12" style="display:none">
                @include ('partials.forms.edit.name', ['translated_name' => trans('admin/hardware/form.name')])
                @include ('partials.forms.edit.warranty')
                @include ('partials.forms.edit.datepicker', ['translated_name' => trans('admin/hardware/form.expected_checkin'),'fieldname' => 'expected_checkin'])
                @include ('partials.forms.edit.datepicker', ['translated_name' => trans('general.next_audit_date'),'fieldname' => 'next_audit_date', 'help_text' => trans('general.next_audit_date_help')])
                <!-- byod checkbox -->
                <div class="form-group byod">
                    <div class="col-md-7 col-md-offset-3">
                        <label class="form-control">
                            <input type="checkbox" value="1" name="byod" {{ (old('remote', $item->byod)) == '1' ? ' checked="checked"' : '' }} aria-label="byod">
                            {{ trans('general.byod') }}
                        </label>
                        <p class="help-block">
                            {{ trans('general.byod_help') }}
                        </p>
                    </div>
                </div>

            </div> <!-- end optional details -->
        </fieldset>

        </div><!-- end col-md-12 col-sm-12-->



        <div class="col-md-12 col-sm-12">
            <fieldset name="order-info">
                <x-form-legend>
                    <a id="order_info">
                        <x-icon type="caret-right" id="order_info_icon" />
                        {{ trans('admin/hardware/form.order_details') }}
                    </a>
                </x-form-legend>

                <div id='order_details' class="col-md-12" style="display:none">
                    @include ('partials.forms.edit.order_number')
                    @include ('partials.forms.edit.datepicker', ['translated_name' => trans('general.purchase_date'),'fieldname' => 'purchase_date'])
                    @include ('partials.forms.edit.datepicker', ['translated_name' => trans('admin/hardware/form.eol_date'),'fieldname' => 'asset_eol_date'])
                    @include ('partials.forms.edit.supplier-select', ['translated_name' => trans('general.supplier'), 'fieldname' => 'supplier_id'])

                    @php
                        $currency_type = null;
                        if ($item->id && $item->location) {
                            $currency_type = $item->location->currency;
                        }
                    @endphp

                    @include ('partials.forms.edit.purchase_cost', ['currency_type' => $currency_type])

                </div> <!-- end order details -->
            </fieldset>
        </div><!-- end col-md-12 col-sm-12-->
    </div><!-- end col-md-12 col-sm-12-->
    </div><!-- end col-md-12 col-sm-12-->
   
@stop

@section('moar_scripts')



<script nonce="{{ csrf_token() }}">

    @if(Request::has('model_id'))
        //TODO: Refactor custom fields to use Livewire, populate from server on page load when requested with model_id
    $(document).ready(function() {
        fetchCustomFields()
    });
    @endif

    var transformed_oldvals={};

    function fetchCustomFields() {
        //save custom field choices
        var oldvals = $('#custom_fields_content').find('input,select,textarea').serializeArray();
        for(var i in oldvals) {
            transformed_oldvals[oldvals[i].name]=oldvals[i].value;
        }

        var modelid = $('#model_select_id').val();
        if (modelid == '') {
            $('#custom_fields_content').html("");
        } else {

            $.ajax({
                type: 'GET',
                url: "{{ config('app.url') }}/models/" + modelid + "/custom_fields",
                headers: {
                    "X-Requested-With": 'XMLHttpRequest',
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                },
                _token: "{{ csrf_token() }}",
                dataType: 'html',
                success: function (data) {
                    $('#custom_fields_content').html(data);
                    $('#custom_fields_content select').select2(); //enable select2 on any custom fields that are select-boxes
                    //now re-populate the custom fields based on the previously saved values
                    $('#custom_fields_content').find('input,select,textarea').each(function (index,elem) {
                        if(transformed_oldvals[elem.name]) {
                            if (elem.type === 'checkbox' || elem.type === 'radio'){
                                let shouldBeChecked = oldvals.find(oldValElement => {
                                    return oldValElement.name === elem.name && oldValElement.value === $(elem).val();
                                });

                                if (shouldBeChecked){
                                    $(elem).prop('checked', true);
                                }

                                return;
                            }
                             {{-- If there already *is* is a previously-input 'transformed_oldvals' handy,
                                  overwrite with that previously-input value *IF* this is an edit of an existing item *OR*
                                  if there is no new default custom field value coming from the model --}}
                            if({{ $item->id ? 'true' : 'false' }} || $(elem).val() == '') {
                                $(elem).val(transformed_oldvals[elem.name]).trigger('change'); //the trigger is for select2-based objects, if we have any
                            }
                        }

                    });
                }
            });
        }
    }

    function user_add(status_id) {

        if (status_id != '') {
            $(".status_spinner").css("display", "inline");
            $.ajax({
                url: "{{config('app.url') }}/api/v1/statuslabels/" + status_id + "/deployable",
                headers: {
                    "X-Requested-With": 'XMLHttpRequest',
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                },
                success: function (data) {
                    $(".status_spinner").css("display", "none");
                    $("#selected_status_status").fadeIn();

                    if (data == true) {
                        $("#assignto_selector").show();
                        $("#assigned_user").show();

                        $("#selected_status_status").removeClass('text-danger');
                        $("#selected_status_status").addClass('text-success');
                        $("#selected_status_status").html('<x-icon type="checkmark" /> {{ trans('admin/hardware/form.asset_deployable')}}');


                    } else {
                        $("#assignto_selector").hide();
                        $("#selected_status_status").removeClass('text-success');
                        $("#selected_status_status").addClass('text-danger');
                        $("#selected_status_status").html('<x-icon type="warning" /> {{ (($item->assigned_to!='') && ($item->assigned_type!='') && ($item->deleted_at == '')) ? trans('admin/hardware/form.asset_not_deployable_checkin') : trans('admin/hardware/form.asset_not_deployable')  }} ');
                    }
                }
            });
        }
    }


    $(function () {
        //grab custom fields for this model whenever model changes.
        $('#model_select_id').on("change", fetchCustomFields);

        //initialize assigned user/loc/asset based on statuslabel's statustype
        user_add($(".status_id option:selected").val());

        //whenever statuslabel changes, update assigned user/loc/asset
        $(".status_id").on("change", function () {
            user_add($(".status_id").val());
        });

    });


    // Add another asset tag + serial combination if the plus sign is clicked
    $(document).ready(function() {

        var max_fields      = 100; //maximum input boxes allowed
        var wrapper         = $(".input_fields_wrap"); //Fields wrapper
        var add_button      = $(".add_field_button"); //Add button ID
        var x               = 1; //initial text box count




        $(add_button).click(function(e){ //on add input button click

            e.preventDefault();

            var auto_tag = $("#asset_tag").val().replace(/^{{ preg_quote(App\Models\Setting::getSettings()->auto_increment_prefix, '/') }}/g, '');
            var box_html        = '';
			const zeroPad 		= (num, places) => String(num).padStart(places, '0');

            // Check that we haven't exceeded the max number of asset fields
            if (x < max_fields) {

                if (auto_tag!='') {
                     auto_tag = zeroPad(parseInt(auto_tag) + parseInt(x),auto_tag.length);
                } else {
                     auto_tag = '';
                }

                x++; //text box increment

                box_html += '<span class="fields_wrapper">';
                box_html += '<div class="form-group"><label for="asset_tag" class="col-md-3 control-label">{{ trans('admin/hardware/form.tag') }} ' + x + '</label>';
                box_html += '<div class="col-md-7 col-sm-12 required">';
                box_html += '<input type="text"  class="form-control" name="asset_tags[' + x + ']" value="{{ (($snipeSettings->auto_increment_prefix!='') && ($snipeSettings->auto_increment_assets=='1')) ? $snipeSettings->auto_increment_prefix : '' }}'+ auto_tag +'" required>';
                box_html += '</div>';
                box_html += '<div class="col-md-2 col-sm-12">';
                box_html += '<a href="#" class="remove_field btn btn-default btn-sm"><x-icon type="minus" /></a>';
                box_html += '</div>';
                box_html += '</div>';
                box_html += '</div>';
                box_html += '<div class="form-group"><label for="serial" class="col-md-3 control-label">{{ trans('admin/hardware/form.serial') }} ' + x + '</label>';
                box_html += '<div class="col-md-7 col-sm-12">';
                box_html += '<input type="text"  class="form-control" name="serials[' + x + ']">';
                box_html += '</div>';
                box_html += '</div>';
                box_html += '</span>';
                $(wrapper).append(box_html);

            // We have reached the maximum number of extra asset fields, so disable the button
            } else {
                $(".add_field_button").attr('disabled');
                $(".add_field_button").addClass('disabled');
            }
        });

        $(wrapper).on("click",".remove_field", function(e){ //user clicks on remove text
            $(".add_field_button").removeAttr('disabled');
            $(".add_field_button").removeClass('disabled');
            e.preventDefault();
            //console.log(x);

            $(this).parent('div').parent('div').parent('span').remove();
            x--;
        });


        $('.expand').click(function(){
            id = $(this).attr('id');
            fields = $(this).text();
            if (txt == '+'){
                $(this).text('-');
            }
            else{
                $(this).text('+');
            }
            $("#"+id).toggle();

        });

        {{-- TODO: Clean up some of the duplication in here. Not too high of a priority since we only copied it once. --}}
        $("#optional_info").on("click",function(){
            $('#optional_details').fadeToggle(100);
            $('#optional_info_icon').toggleClass('fa-caret-right fa-caret-down');
            var optional_info_open = $('#optional_info_icon').hasClass('fa-caret-down');
            document.cookie = "optional_info_open="+optional_info_open+'; path=/';
        });

        $("#order_info").on("click",function(){
            $('#order_details').fadeToggle(100);
            $("#order_info_icon").toggleClass('fa-caret-right fa-caret-down');
            var order_info_open = $('#order_info_icon').hasClass('fa-caret-down');
            document.cookie = "order_info_open="+order_info_open+'; path=/';
        });

        var all_cookies = document.cookie.split(';')
        for(var i in all_cookies) {
            var trimmed_cookie = all_cookies[i].trim(' ')
            if (trimmed_cookie.startsWith('optional_info_open=')) {
                elems = all_cookies[i].split('=', 2)
                if (elems[1] == 'true') {
                    $('#optional_info').trigger('click')
                }
            }
            if (trimmed_cookie.startsWith('order_info_open=')) {
                elems = all_cookies[i].split('=', 2)
                if (elems[1] == 'true') {
                    $('#order_info').trigger('click')
                }
            }
        }

    });




</script>
@stop
