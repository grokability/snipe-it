@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ $item->id ? trans('admin/hardware/form.update') : trans('admin/hardware/form.create') }}
    @parent
@stop

{{-- Page content --}}
@section('content')

<x-container class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-sm-12 col-sm-offset-0">
    <x-form :item="$item" :route="$item->id ? route('hardware.update', $item) : route('hardware.store')">
        <x-box top_submit>
            @if ($item->id)
                <x-slot:header>{{ $item->display_name }}</x-slot:header>
            @endif

    <x-input.company-select
        :label="trans('general.company')"
        name="company_id"
        :selected="old('company_id', $item?->company_id)"
    />


  <!-- Asset Tag -->
    <div class="form-group {{ ($errors->has('asset_tag') || $errors->has('asset_tags.1')) ? ' has-error' : '' }}">
      <label for="asset_tag" class="col-md-3 control-label">{{ trans('admin/hardware/form.tag') }}</label>



      @if ($item->id)
          {{-- Editing an existing asset — only one tag input.
               UpdateAssetRequest + the Asset model rules key on
               asset_tag (singular), so that's the only error to render. --}}
          <div class="col-md-7 col-sm-12">
              <input class="form-control" type="text" name="asset_tags[1]" id="asset_tag" value="{{ old('asset_tag', $item->asset_tag) }}" required>
              <x-form.error name="asset_tag" />
          </div>
      @else
          {{-- Creating a new asset — operator can add extra tag rows via
               the plus button, which dispatches an addTagRow event to
               the Livewire component below. Passes the primary tag
               input's current DOM value so the component auto-increments
               from whatever the operator has actually typed, not the
               server-rendered default. CreateMultipleAssetRequest remaps
               the asset_tag rule onto each row's asset_tags[N], so this
               row's error keys on asset_tags.1. --}}
          <div class="col-md-7 col-sm-12">
              <input class="form-control"
                     type="text" name="asset_tags[1]" id="asset_tag"
                     value="{{ old('asset_tags.1', \App\Models\Asset::autoincrement_asset()) }}" required>
              <x-form.error name="asset_tags.1" />
          </div>
          <div class="col-md-2 col-sm-12">
              <button type="button" class="add_field_button btn btn-theme" name="add_field_button" onclick="Livewire.dispatch('addTagRow', { firstTag: document.getElementById('asset_tag').value })">
                  <x-icon type="plus" class="fa-fw"/>
                  <span class="sr-only">
                      {{ trans('general.new') }}
                  </span>
              </button>
          </div>
      @endif
  </div>

    {{-- Serial (row 1). Required if the model has require_serial set or
         if the asset's own rules mark it required. --}}
    <x-form.row
        :label="trans('admin/hardware/form.serial')"
        name="serials.1"
        input_div_class="col-md-7 col-sm-12"
    >
        <x-slot:input>
            <x-input.text
                name="serials[1]"
                id="serial_1"
                :value="old('serials.1', $item->serial)"
                :required="Helper::checkIfRequired($item, 'serial') || ($item->model && $item->model->require_serial)"
                maxlength="191"
            />
        </x-slot:input>
    </x-form.row>

    {{-- Additional tag + serial rows (indices 2+) for bulk-create. Only
         rendered when creating a new asset. Livewire owns the add/remove
         state and rehydrates from old input on validation redisplay; the
         outer form's plain POST submit picks up the rendered inputs by
         name (asset_tags[N] / serials[N]) alongside row 1. --}}
    @if (! $item->id)
        <livewire:asset-additional-tags
            :autoPrefix="$snipeSettings->auto_increment_prefix ?? ''"
            :autoEnabled="$snipeSettings->auto_increment_assets == '1'"
        />
    @endif

    @include ('partials.forms.edit.model-select', ['translated_name' => trans('admin/hardware/form.model'), 'fieldname' => 'model_id', 'field_req' => true])


    @include ('partials.forms.edit.status', [ 'required' => 'true'])
    @if (!$item->id)
        
                    @include ('partials.forms.checkout-selector', ['user_select' => 'true','asset_select' => 'true', 'location_select' => 'true', 'style' => 'display:none;', 'optional' => true])
        <x-input.user-select
            :label="trans('general.user')"
            name="assigned_user"
            :selected="old('assigned_user')"
            style="display:none;"
        />
        @include ('partials.forms.edit.asset-select', ['translated_name' => trans('general.asset'), 'fieldname' => 'assigned_asset', 'style' => 'display:none;', 'required' => 'false'])
        @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'assigned_location', 'style' => 'display:none;', 'required' => 'false'])
    @endif

    {{-- Notes --}}
    <x-form.row
        :label="trans('general.notes')"
        name="notes"
        type="textarea"
        :item="$item"
        input_div_class="col-md-7 col-sm-12"
    />

    {{-- Current location. When blank, the observer / checkout flow
         updates location_id to match the checkout target's location
         (user's location, target-location, or parent asset's location).
         See the note under general.location_edit_help. --}}
    <x-input.location-select
        :label="trans('general.location')"
        name="location_id"
        :selected="old('location_id', $item?->location_id)"
        :helpText="trans('general.location_edit_help')"
    />

    {{-- Default (ready-to-deploy) location --}}
    <x-input.location-select
        :label="trans('admin/hardware/form.default_location')"
        name="rtd_location_id"
        :selected="old('rtd_location_id', $item?->rtd_location_id)"
        :helpText="trans('general.rtd_location_help')"
    />

    {{-- Requestable --}}
    <x-form.checkbox-row
        name="requestable"
        :label="trans('admin/hardware/general.requestable')"
        :item="$item"
    />



    <x-input.image-upload :item="$item" :imagePath="app('assets_upload_path')" />


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

            <x-form.legend>
                <a id="optional_info">
                    <x-icon type="caret-right" class="fa-fw" id="optional_info_icon" />
                    {{ trans('admin/hardware/form.optional_infos') }}
                </a>
            </x-form.legend>

            <div id="optional_details" class="col-md-12" style="display:none">
                {{-- Name (asset display name, distinct from asset_tag) --}}
                <x-form.row
                    :label="trans('admin/hardware/form.name')"
                    name="name"
                    type="text"
                    :item="$item"
                    :maxlength="191"
                    input_div_class="col-md-8 col-sm-12"
                />

                {{-- Warranty months + unit-suffix addon --}}
                <x-form.row
                    :label="trans('admin/hardware/form.warranty')"
                    name="warranty_months"
                    input_div_class="col-md-9"
                >
                    <x-slot:input>
                        <div class="input-group col-md-4 col-sm-6" style="padding-left: 0;">
                            <input
                                class="form-control"
                                type="text"
                                name="warranty_months"
                                id="warranty_months"
                                value="{{ old('warranty_months', $item->warranty_months) }}"
                                maxlength="3"
                            />
                            <span class="input-group-addon">{{ trans('admin/hardware/form.months') }}</span>
                        </div>
                    </x-slot:input>
                </x-form.row>
                <x-form.row
                    :label="trans('admin/hardware/form.expected_checkin')"
                    name="expected_checkin"
                    type="datetimepicker"
                    :item="$item"
                    :default_now="false"
                    input_div_class="input-group col-md-5"
                />
                {{-- Next audit date. The help block is hand-rolled below
                     the row rather than piped through :help_html so it
                     renders at col-md-8 width (readable) instead of
                     inheriting the col-md-4 input column's narrow width.
                     Static-attribute {!! !!} form is required — the
                     dynamic :help_html="trans(...)" binding runs through
                     BladeCompiler::sanitizeComponentAttribute() and
                     escapes the <code> tags in the translation to
                     literal &lt;code&gt; entities. --}}
                <x-form.row
                    :label="trans('general.next_audit_date')"
                    name="next_audit_date"
                    type="datepicker"
                    :item="$item"
                    input_div_class="input-group col-md-4"
                />
                <div class="form-group">
                    <div class="col-md-8 col-md-offset-3">
                        <p class="help-block">{!! trans('general.next_audit_date_help') !!}</p>
                    </div>
                </div>
                {{-- BYOD. The pre-rewrite markup read old('remote', $item->byod)
                     for its checked state, likely a copy-paste from a legacy
                     'remote' field — so byod ticks silently got lost on
                     validation redisplay. The component's built-in state
                     logic reads old('byod', $item->byod) and restores the
                     tick correctly. --}}
                <x-form.checkbox-row
                    name="byod"
                    :label="trans('general.byod')"
                    :help_text="trans('general.byod_help')"
                    :item="$item"
                />

            </div> <!-- end optional details -->
        </fieldset>

    </div><!-- end col-md-12 col-sm-12-->



    <div class="col-md-12 col-sm-12">
        <fieldset name="order-info">
            <x-form.legend>
                <a id="order_info">
                    <x-icon type="caret-right" class="fa-fw" id="order_info_icon" />
                    {{ trans('admin/hardware/form.order_details') }}
                </a>
            </x-form.legend>

            <div id='order_details' class="col-md-12" style="display:none">
                {{-- Order number --}}
                <x-form.row
                    :label="trans('general.order_number')"
                    name="order_number"
                    type="text"
                    :item="$item"
                    :maxlength="191"
                    input_div_class="col-md-7 col-sm-12"
                />

                {{-- Purchase date --}}
                <x-form.row
                    :label="trans('general.purchase_date')"
                    name="purchase_date"
                    type="datepicker"
                    :item="$item"
                    input_div_class="input-group col-md-4"
                />

                {{-- EOL date --}}
                <x-form.row
                    :label="trans('admin/hardware/form.eol_date')"
                    name="asset_eol_date"
                    type="datepicker"
                    :item="$item"
                    input_div_class="input-group col-md-4"
                />

                {{-- Supplier --}}
                <x-input.supplier-select
                    :label="trans('general.supplier')"
                    name="supplier_id"
                    :selected="old('supplier_id', $item?->supplier_id)"
                />

                {{-- Purchase cost. When the asset has a location whose
                     location.currency is set, we show that currency in the
                     addon so the price reads in the currency it was
                     quoted in; otherwise the component falls back to the
                     system default_currency. Empty-string location
                     currencies also fall through — they'd otherwise
                     render as a blank addon. --}}
                <x-input.purchase-cost
                    :item="$item"
                    :currencyType="$item->model?->default_currency ?: ($item->id && $item->location && $item->location->currency !== '' ? $item->location->currency : null)"
                />

            </div> <!-- end order details -->
        </fieldset>
    </div><!-- end col-md-12 col-sm-12-->

            <x-slot:customfooter>
                <x-redirect_submit_options
                    index_route="hardware.index"
                    :button_label="trans('general.save')"
                    :options="[
                        'back' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.previous_page')]),
                        'index' => trans('admin/hardware/form.redirect_to_all', ['type' => 'assets']),
                        'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.asset')]),
                        'other_redirect' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.asset') . ' ' . trans('general.asset_model')]),
                    ]"
                />
            </x-slot:customfooter>
        </x-box>
    </x-form>
</x-container>

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
                    // Re-init eonasdan datetimepickers on any DATE/DATETIME
                    // custom fields that came with the new HTML. Without
                    // this, the pickers would just be plain text inputs
                    // until page reload.
                    window.snipeitInitDatetimepickers('#custom_fields_content');
                    // Same for the MAC-address input mask on any format=MAC
                    // custom fields in the fresh partial. See
                    // resources/assets/js/snipeit.js for the mask logic.
                    window.snipeitInitMacAddressMask('#custom_fields_content');
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
                        var checkoutType = $('input[name=checkout_to_type]:checked').val() || 'user';
                        $("#assignto_selector").show();
                        $("#assigned_user").toggle(checkoutType === 'user');
                        $("#assigned_asset").toggle(checkoutType === 'asset');
                        $("#assigned_location").toggle(checkoutType === 'location');

                        $("#selected_status_status").removeClass('text-danger');
                        $("#selected_status_status").addClass('text-success');
                        $("#selected_status_status").html('<x-icon type="checkmark" /> {{ trans_choice('admin/hardware/form.asset_deployable', 1)}}');

                    } else {
                        $("#assignto_selector").hide();
                        $("#assigned_user").hide();
                        $("#assigned_asset").hide();
                        $("#assigned_location").hide();
                        $("#selected_status_status").removeClass('text-success');
                        $("#selected_status_status").addClass('text-danger');
                        $("#selected_status_status").html('<x-icon type="warning" /> {{ (($item->assigned_to!='') && ($item->assigned_type!='') && ($item->deleted_at == '')) ? trans_choice('admin/hardware/form.asset_not_deployable_checkin', 1) : trans('admin/hardware/form.asset_not_deployable')  }} ');
                    }
                }
            });
        }
    }


    $(function () {
        //grab custom fields for this model whenever model changes.
        $('#model_select_id').on("change", fetchCustomFields);
        //loads default purchase cost on asset model selection
        @if (! $item->id)
        $('#model_select_id').on('select2:select', function (event) {
            const model = event.params.data;

            $('#purchase_cost').val(model.default_purchase_cost ?? '');
            $('#purchase_cost_currency').text(model.default_currency || @json($snipeSettings->default_currency));
        });
        @if (isset($selected_model))
        @if (! session()->hasOldInput('purchase_cost'))
        $('#purchase_cost').val(@json($selected_model->default_purchase_cost));
        @endif
        $('#purchase_cost_currency').text(@json($selected_model->default_currency ?: $snipeSettings->default_currency));
        @endif
        @endif
        //initialize assigned user/loc/asset based on statuslabel's statustype
        user_add($(".status_id option:selected").val());

        //whenever statuslabel changes, update assigned user/loc/asset
        $(".status_id").on("change", function () {
            user_add($(".status_id").val());
        });

        @if (isset($cloned_model))
            @if ($snipeSettings->auto_increment_assets == '1')
                $('input[name="serials[1]"]').trigger('focus');
            @else
                $('#asset_tag').trigger('focus');
            @endif
        @endif
    });


    {{-- Additional tag/serial row add/remove now lives in the
         asset-additional-tags Livewire component. The plus button in the
         outer blade dispatches an 'addTagRow' event to that component;
         each rendered row has its own remove button wired via wire:click.
         The pre-Livewire jQuery string-HTML injection that used to be
         here was deleted in that sweep. --}}
    $(document).ready(function() {
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
