@extends('layouts/default')

{{-- Page Title --}}
@section('title')
    @if (request()->routeIs('report-templates.edit'))
        {{ trans('general.update') }} {{ $template->name }}
    @elseif(request()->routeIs('report-templates.show'))
        {{ trans('general.custom_report') }}: {{ $template->name }}
    @else
        {{ trans('general.custom_report') }}
    @endif
@parent
@stop

@section('header_right')
    @if (request()->routeIs('report-templates.edit'))
        <a href="{{ route('report-templates.show', $template) }}" class="btn btn-primary pull-right">
            {{ trans('general.back') }}
        </a>
    @elseif (request()->routeIs('report-templates.show'))
        <a href="{{ route('reports/custom') }}" class="btn btn-primary pull-right">
            {{ trans('general.back') }}
        </a>
    @else
        <a href="{{ URL::previous() }}" class="btn btn-primary pull-right">
            {{ trans('general.back') }}
        </a>
    @endif
@stop


{{-- Page content --}}
@section('content')

<div class="row">
    <div class="col-md-9">

        <form
            method="POST"
            action="{{ request()->routeIs('report-templates.edit') ? route('report-templates.update', $template) : route('reports.post-custom') }}"
            accept-charset="UTF-8"
            class="form-horizontal"
            id="custom-report-form"
        >
        {{csrf_field()}}

    <!-- Horizontal Form -->
        <div class="box box-default">
            <div class="box-header with-border">
                @if (request()->routeIs('reports/custom') || request()->routeIs('report-templates.show'))
                    <h2 class="box-title" style="padding-top: 7px;">
                        {{ trans('general.customize_report') }}
                    </h2>

                @endif

                @if (request()->routeIs('report-templates.edit'))
                    <div class="row">
                        <div class="col-md-7 col-md-offset-4">
                            <div class="{{ $errors->has('name') ? ' has-error' : '' }}">
                                <label
                                    for="name"
                                    class="col-md-4 control-label"
                                >
                                    {{ trans('admin/reports/general.template_name') }}
                                </label>
                                <div class="col-md-8">
                                    <input
                                        class="form-control"
                                        placeholder=""
                                        name="name"
                                        type="text"
                                        id="name"
                                        value="{{ $template->name }}"
                                        required
                                    >
                                </div>
                                {!! $errors->first('name', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>
                    </div>
                @endif

        </div><!-- /.box-header -->

        <div class="box-body">

            <div class="col-md-4" id="included_fields_wrapper">

                <label class="form-control">
                    <input type="checkbox" id="checkAll" checked="checked">
                    {{ trans('general.select_all') }}
                </label>

                <label class="form-control">
                    <input type="checkbox" name="id" value="1" @checked($template->checkmarkValue('id')) />
                    {{ trans('general.id') }}
                </label>

                <label class="form-control">
                    <input type="checkbox" name="company" value="1" @checked($template->checkmarkValue('company')) />
                    {{ trans('general.company') }}
                </label>

                <label class="form-control">
                    <input type="checkbox" name="asset_tag" value="1" @checked($template->checkmarkValue('asset_tag')) />
                    {{ trans('general.asset_tag') }}
                </label>

                <label class="form-control">
                    <input type="checkbox" name="asset_name" value="1" @checked($template->checkmarkValue('asset_name')) />
                    {{ trans('admin/hardware/form.name') }}
                </label>

                <label class="form-control">
                    <input type="checkbox" name="manufacturer" value="1" @checked($template->checkmarkValue('manufacturer')) />
                    {{ trans('general.manufacturer') }}
                </label>

                <label class="form-control">
                    <input type="checkbox" name="model" value="1" @checked($template->checkmarkValue('model')) />
                    {{ trans('general.asset_model') }}
                </label>

                <label class="form-control">
                    <input type="checkbox" name="category" value="1" @checked($template->checkmarkValue('category')) />
                    {{ trans('general.category') }}
                </label>

                <label class="form-control">
                    <input type="checkbox" name="serial" value="1" @checked($template->checkmarkValue('serial')) />
                    {{ trans('admin/hardware/table.serial') }}
                </label>

                <label class="form-control">
                    <input type="checkbox" name="purchase_date" value="1" @checked($template->checkmarkValue('purchase_date')) />
                    {{ trans('admin/licenses/table.purchase_date') }}
                </label>

                <label class="form-control">
                    <input type="checkbox" name="purchase_cost" value="1" @checked($template->checkmarkValue('purchase_cost')) />
                    {{ trans('admin/hardware/form.cost') }}
                </label>

                <label class="form-control">
                    <input type="checkbox" name="eol" value="1" @checked($template->checkmarkValue('eol')) />
                    {{ trans('admin/hardware/form.eol_date') }}
                </label>

                <label class="form-control">
                    <input type="checkbox" name="warranty" value="1" @checked($template->checkmarkValue('warranty')) />
                    {{ trans('admin/hardware/form.warranty') }}
                </label>

                <label class="form-control">
                    <input type="checkbox" name="depreciation" value="1" @checked($template->checkmarkValue('depreciation')) />
                    {{ trans('general.depreciation') }}
                </label>

                <label class="form-control">
                    <input type="checkbox" name="order" value="1" @checked($template->checkmarkValue('order')) />
                    {{ trans('admin/hardware/form.order') }}
                </label>

                <label class="form-control">
                    <input type="checkbox" name="supplier" value="1" @checked($template->checkmarkValue('supplier')) />
                    {{ trans('general.suppliers') }}
                </label>

                <label class="form-control">
                    <input type="checkbox" name="location" value="1" @checked($template->checkmarkValue('location')) />
                    {{ trans('general.location') }}
                </label>

              <label class="form-control" style="margin-left: 25px;">
                  <input type="checkbox" name="location_address" value="1" @checked($template->checkmarkValue('location_address')) />
                  {{ trans('general.address') }}
              </label>

                <label class="form-control">
                    <input type="checkbox" name="rtd_location" value="1" @checked($template->checkmarkValue('rtd_location')) />
                    {{ trans('admin/hardware/form.default_location') }}
                </label>

              <label class="form-control" style="margin-left: 25px;">
                  <input type="checkbox" name="rtd_location_address" value="1" @checked($template->checkmarkValue('rtd_location_address')) />
                {{ trans('general.address') }}
              </label>

                <label class="form-control">
                    <input type="checkbox" name="status" value="1" @checked($template->checkmarkValue('status')) />
                    {{ trans('general.status') }}
                </label>

                <label class="form-control">
                    <input type="checkbox" name="checkout_date" value="1" @checked($template->checkmarkValue('checkout_date')) />
                    {{ trans('admin/hardware/table.checkout_date') }}
                </label>

              <label class="form-control">
                  <input type="checkbox" name="checkin_date" value="1" @checked($template->checkmarkValue('checkin_date')) />
                {{ trans('admin/hardware/table.last_checkin_date') }}
              </label>

              <label class="form-control">
                  <input type="checkbox" name="expected_checkin" value="1" @checked($template->checkmarkValue('expected_checkin')) />
                {{ trans('admin/hardware/form.expected_checkin') }}
              </label>

                <label class="form-control">
                    <input type="checkbox" name="created_at" value="1" @checked($template->checkmarkValue('created_at')) />
                    {{ trans('general.created_at') }}
                </label>

                <label class="form-control">
                    <input type="checkbox" name="updated_at" value="1" @checked($template->checkmarkValue('updated_at')) />
                    {{ trans('general.updated_at') }}
                </label>

                <label class="form-control">
                    <input type="checkbox" name="deleted_at" value="1" @checked($template->checkmarkValue('deleted_at')) />
                    {{ trans('general.deleted') }}
                </label>

                <label class="form-control">
                    <input type="checkbox" name="last_audit_date" value="1" @checked($template->checkmarkValue('last_audit_date')) />
                    {{ trans('general.last_audit') }}
                </label>

                <label class="form-control">
                    <input type="checkbox" name="next_audit_date" value="1" @checked($template->checkmarkValue('next_audit_date')) />
                    {{ trans('general.next_audit_date') }}
                </label>

                <label class="form-control">
                    <input type="checkbox" name="notes" value="1" @checked($template->checkmarkValue('notes')) />
                    {{ trans('general.notes') }}
                </label>

              <label class="form-control" style="margin-left: 25px;">
                  <input type="checkbox" name="url" value="1" @checked($template->checkmarkValue('url')) />
                {{ trans('general.url') }}
              </label>


            <!-- User fields -->

              <h2>{{ trans('general.checked_out_to_fields') }}: </h2>

              <label class="form-control">
                  <input type="checkbox" name="assigned_to" value="1" @checked($template->checkmarkValue('assigned_to')) />
                {{ trans('admin/licenses/table.assigned_to') }}
              </label>

              <label class="form-control">
                  <input type="checkbox" name="username" value="1" @checked($template->checkmarkValue('username')) />
                {{ trans('admin/users/table.username') }}
              </label>

              <label class="form-control">
                  <input type="checkbox" name="employee_num" value="1" @checked($template->checkmarkValue('employee_num')) />
                {{ trans('general.employee_number') }}
              </label>

              <label class="form-control">
                  <input type="checkbox" name="manager" value="1" @checked($template->checkmarkValue('manager')) />
                {{ trans('admin/users/table.manager') }}
              </label>

              <label class="form-control">
                  <input type="checkbox" name="department" value="1" @checked($template->checkmarkValue('department')) />
                {{ trans('general.department') }}
              </label>

              <label class="form-control">
                  <input type="checkbox" name="title" value="1" @checked($template->checkmarkValue('title')) />
                {{ trans('admin/users/table.title') }}
              </label>

                <!-- new -->

              <label class="form-control">
                  <input type="checkbox" name="phone" value="1" @checked($template->checkmarkValue('phone')) />
                  {{ trans('admin/users/table.phone') }}
              </label>

              <label class="form-control">
                  <input type="checkbox" name="user_address" value="1" @checked($template->checkmarkValue('user_address')) />
                  {{ trans('general.address') }}
              </label>

              <label class="form-control">
                  <input type="checkbox" name="user_city" value="1" @checked($template->checkmarkValue('user_city')) />
                  {{ trans('general.city') }}
              </label>

              <label class="form-control">
                  <input type="checkbox" name="user_state" value="1" @checked($template->checkmarkValue('user_state')) />
                  {{ trans('general.state') }}
              </label>

              <label class="form-control">
                  <input type="checkbox" name="user_country" value="1" @checked($template->checkmarkValue('user_country')) />
                  {{ trans('general.country') }}
              </label>

              <label class="form-control">
                  <input type="checkbox" name="user_zip" value="1" @checked($template->checkmarkValue('user_zip')) />
                  {{ trans('general.zip') }}
              </label>



            @if ($customfields->count() > 0)

                <h2>{{ trans('admin/custom_fields/general.custom_fields') }}</h2>

              @foreach ($customfields as $customfield)

                  <label class="form-control">
                      <input type="checkbox" name="{{ $customfield->db_column_name() }}" value="1" @checked($template->checkmarkValue($customfield->db_column_name())) />
                    {{ $customfield->name }}
                  </label>

              @endforeach
            @endif
          </div> <!-- /.col-md-4-->

          <div class="col-md-8">

            <p>
                {!! trans('general.report_fields_info') !!}
            </p>

              <br>

            @include ('partials.forms.edit.company-select', [
                    'translated_name' => trans('general.company'),
                    'fieldname' =>
                    'by_company_id[]',
                    'multiple' => 'true',
                    'hide_new' => 'true',
                    'selected' => $template->selectValues('by_company_id', \App\Models\Company::class),
            ])
            @include ('partials.forms.edit.location-select', [
                    'translated_name' => trans('general.location'),
                    'fieldname' => 'by_location_id[]',
                    'multiple' => 'true',
                    'hide_new' => 'true',
                    'selected' => $template->selectValues('by_location_id', \App\Models\Location::class),
            ])
            @include ('partials.forms.edit.location-select', [
                    'translated_name' => trans('admin/hardware/form.default_location'),
                    'fieldname' => 'by_rtd_location_id[]',
                    'multiple' => 'true',
                    'hide_new' => 'true',
                    'selected' => $template->selectValues('by_rtd_location_id', \App\Models\Location::class),
            ])
            @include ('partials.forms.edit.department-select',[
                    'translated_name' => trans('general.department'),
                    'fieldname' => 'by_dept_id[]',
                    'multiple' => 'true',
                    'hide_new' => 'true',
                    'selected' => $template->selectValues('by_dept_id', \App\Models\Department::class),
            ])
            @include ('partials.forms.edit.supplier-select', [
                    'translated_name' => trans('general.supplier'),
                    'fieldname' => 'by_supplier_id[]',
                    'multiple' => 'true',
                    'hide_new' => 'true',
                    'selected' => $template->selectValues('by_supplier_id', \App\Models\Supplier::class),
            ])
            @include ('partials.forms.edit.model-select', [
                    'translated_name' => trans('general.asset_model'),
                    'fieldname' => 'by_model_id[]',
                    'multiple' => 'true',
                    'hide_new' => 'true',
                    'selected' => $template->selectValues('by_model_id', \App\Models\AssetModel::class),
            ])
            @include ('partials.forms.edit.manufacturer-select', [
                    'translated_name' => trans('general.manufacturer'),
                    'fieldname' => 'by_manufacturer_id[]',
                    'multiple' => 'true',
                    'hide_new' => 'true',
                    'selected' => $template->selectValues('by_manufacturer_id', \App\Models\Manufacturer::class),
            ])
            @include ('partials.forms.edit.category-select', [
                    'translated_name' => trans('general.category'),
                    'fieldname' => 'by_category_id[]',
                    'multiple' => 'true',
                    'hide_new' => 'true',
                    'category_type' => 'asset',
                    'selected' => $template->selectValues('by_category_id', \App\Models\Category::class),
            ])
            @include ('partials.forms.edit.status-select', [
                    'translated_name' => trans('admin/hardware/form.status'),
                    'fieldname' => 'by_status_id[]',
                    'multiple' => 'true',
                    'hide_new' => 'true',
                    'selected' => $template->selectValues('by_status_id', \App\Models\Statuslabel::class),
            ])

            <!-- Order Number -->
            <div class="form-group">
              <label for="by_order_number" class="col-md-3 control-label">{{ trans('general.order_number') }}</label>
              <div class="col-md-7">
                <input class="form-control" type="text" name="by_order_number" value="{{ $template->textValue('by_order_number', old('by_order_number')) }}" aria-label="by_order_number">
              </div>
            </div>

          <!-- Purchase Date -->
            <div class="form-group purchase-range{{ ($errors->has('purchase_start') || $errors->has('purchase_end')) ? ' has-error' : '' }}">
              <label for="purchase_start" class="col-md-3 control-label">{{ trans('general.purchase_date') }}</label>
              <div class="input-daterange input-group col-md-7" id="purchase-range-datepicker">
                  <input type="text" class="form-control" name="purchase_start" aria-label="purchase_start" value="{{ $template->textValue('purchase_start', old('purchase_start')) }}">
                  <span class="input-group-addon">{{ strtolower(trans('general.to')) }}</span>
                  <input type="text" class="form-control" name="purchase_end" aria-label="purchase_end" value="{{ $template->textValue('purchase_end', old('purchase_end')) }}">
              </div>

                @if ($errors->has('purchase_start') || $errors->has('purchase_end'))
                    <div class="col-md-9 col-lg-offset-3">
                        {!! $errors->first('purchase_start', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                        {!! $errors->first('purchase_end', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                    </div>
                @endif

            </div>

          <!-- Purchase Cost -->
            <div class="form-group purchase-range{{ ($errors->has('purchase_cost_start') || $errors->has('purchase_cost_end')) ? ' has-error' : '' }}">
              <label for="purchase_cost_start" class="col-md-3 control-label">{{ trans('admin/hardware/form.cost') }}</label>
              <div class="input-group col-md-7">
                  <input type="number" min="0" step="0.01" class="form-control" name="purchase_cost_start" aria-label="purchase_cost_start" value="{{ $template->textValue('purchase_cost_start', old('purchase_cost_start')) }}">
                  <span class="input-group-addon">{{ strtolower(trans('general.to')) }}</span>
                  <input type="number" min="0" step="0.01" class="form-control" name="purchase_cost_end" aria-label="purchase_cost_end" value="{{ $template->textValue('purchase_cost_end', old('purchase_cost_end')) }}">
              </div>

              @if ($errors->has('purchase_cost_start') || $errors->has('purchase_cost_end'))
                  <div class="col-md-9 col-lg-offset-3">
                      {!! $errors->first('purchase_cost_start', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                      {!! $errors->first('purchase_cost_end', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                  </div>
              @endif

            </div>

          <!-- Created Date -->
            <div class="form-group created-range{{ ($errors->has('created_start') || $errors->has('created_end')) ? ' has-error' : '' }}">
              <label for="created_start" class="col-md-3 control-label">{{ trans('general.created_at') }} </label>
              <div class="input-daterange input-group col-md-7" id="created-range-datepicker">
                  <input type="text" class="form-control" name="created_start" aria-label="created_start" value="{{ $template->textValue('created_start', old('created_start')) }}">
                  <span class="input-group-addon">{{ strtolower(trans('general.to')) }}</span>
                  <input type="text" class="form-control" name="created_end" aria-label="created_end" value="{{ $template->textValue('created_end', old('created_end')) }}">
              </div>

                @if ($errors->has('created_start') || $errors->has('created_end'))
                    <div class="col-md-9 col-lg-offset-3">
                        {!! $errors->first('created_start', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                        {!! $errors->first('created_end', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                    </div>
                @endif
            </div>

          <!-- Checkout Date -->
          <div class="form-group checkout-range{{ ($errors->has('checkout_date_start') || $errors->has('checkout_date_end')) ? ' has-error' : '' }}">
              <label for="checkout_date" class="col-md-3 control-label">{{ trans('general.checkout') }} </label>
              <div class="input-daterange input-group col-md-7" id="checkout-range-datepicker">
                  <input type="text" class="form-control" name="checkout_date_start" aria-label="checkout_date_start" value="{{ $template->textValue('checkout_date_start', old('checkout_date_start')) }}">
                  <span class="input-group-addon">{{ strtolower(trans('general.to')) }}</span>
                  <input type="text" class="form-control" name="checkout_date_end" aria-label="checkout_date_end" value="{{ $template->textValue('checkout_date_end', old('checkout_date_end')) }}">
              </div>

              @if ($errors->has('checkout_date_start') || $errors->has('checkout_date_end'))
                  <div class="col-md-9 col-lg-offset-3">
                      {!! $errors->first('checkout_date_start', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                      {!! $errors->first('checkout_date_end', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                  </div>
              @endif

          </div>

          <!-- Last Checkin Date -->
          <div class="form-group checkin-range{{ ($errors->has('checkin_date_start') || $errors->has('checkin_date_end')) ? ' has-error' : '' }}">
              <label for="checkin_date" class="col-md-3 control-label">{{ trans('admin/hardware/table.last_checkin_date') }}</label>
              <div class="input-daterange input-group col-md-7" id="checkin-range-datepicker">
                  <input type="text" class="form-control" name="checkin_date_start" aria-label="checkin_date_start" value="{{ $template->textValue('checkin_date_start', old('checkin_date_start')) }}">
                  <span class="input-group-addon">{{ strtolower(trans('general.to')) }}</span>
                  <input type="text" class="form-control" name="checkin_date_end" aria-label="checkin_date_end" value="{{ $template->textValue('checkin_date_end', old('checkin_date_end')) }}">
              </div>

              @if ($errors->has('checkin_date_start') || $errors->has('checkin_date_end'))
                  <div class="col-md-9 col-lg-offset-3">
                      {!! $errors->first('checkin_date_start', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                      {!! $errors->first('checkin_date_end', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                  </div>
              @endif
          </div>

            <!-- Expected Checkin Date -->
            <div class="form-group expected_checkin-range{{ ($errors->has('expected_checkin_start') || $errors->has('expected_checkin_end')) ? ' has-error' : '' }}">
              <label for="expected_checkin_start" class="col-md-3 control-label">{{ trans('admin/hardware/form.expected_checkin') }}</label>
              <div class="input-daterange input-group col-md-7" id="expected_checkin-range-datepicker">
                  <input type="text" class="form-control" name="expected_checkin_start" aria-label="expected_checkin_start" value="{{ $template->textValue('expected_checkin_start', old('expected_checkin_start')) }}">
                  <span class="input-group-addon">{{ strtolower(trans('general.to')) }}</span>
                  <input type="text" class="form-control" name="expected_checkin_end" aria-label="expected_checkin_end" value="{{ $template->textValue('expected_checkin_end', old('expected_checkin_end')) }}">
              </div>

                @if ($errors->has('expected_checkin_start') || $errors->has('expected_checkin_end'))
                    <div class="col-md-9 col-lg-offset-3">
                        {!! $errors->first('expected_checkin_start', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                        {!! $errors->first('expected_checkin_end', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                    </div>
                @endif

            </div>

              <!-- EoL Date -->
              <div class="form-group asset_eol_date-range {{ ($errors->has('asset_eol_date_start') || $errors->has('asset_eol_date_end')) ? ' has-error' : '' }}">
                  <label for="asset_eol_date" class="col-md-3 control-label">{{ trans('admin/hardware/form.eol_date') }}</label>
                  <div class="input-daterange input-group col-md-7" id="asset_eol_date-range-datepicker">
                      <input type="text" class="form-control" name="asset_eol_date_start" aria-label="asset_eol_date_start" value="{{ $template->textValue('asset_eol_date_start', old('asset_eol_date_start')) }}">
                      <span class="input-group-addon">to</span>
                      <input type="text" class="form-control" name="asset_eol_date_end" aria-label="asset_eol_date_end" value="{{ $template->textValue('asset_eol_date_end', old('asset_eol_date_end')) }}">
                  </div>

                  @if ($errors->has('asset_eol_date_start') || $errors->has('asset_eol_date_end'))
                      <div class="col-md-9 col-lg-offset-3">
                          {!! $errors->first('asset_eol_date_start', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                          {!! $errors->first('asset_eol_date_end', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                      </div>
                  @endif
              </div>

              <!-- Last Audit Date -->
              <div class="form-group last_audit-range{{ ($errors->has('last_audit_start') || $errors->has('last_audit_end')) ? ' has-error' : '' }}">
                  <label for="last_audit_start" class="col-md-3 control-label">{{ trans('general.last_audit') }}</label>
                  <div class="input-daterange input-group col-md-7" id="last_audit-range-datepicker">
                      <input type="text" class="form-control" name="last_audit_start" aria-label="last_audit_start" value="{{ $template->textValue('last_audit_start', old('last_audit_start')) }}">
                      <span class="input-group-addon">{{ strtolower(trans('general.to')) }}</span>
                      <input type="text" class="form-control" name="last_audit_end" aria-label="last_audit_end" value="{{ $template->textValue('last_audit_end', old('last_audit_end')) }}">
                  </div>

                  @if ($errors->has('last_audit_start') || $errors->has('last_audit_end'))
                      <div class="col-md-9 col-lg-offset-3">
                          {!! $errors->first('last_audit_start', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                          {!! $errors->first('last_audit_end', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                      </div>
                  @endif
              </div>

              <!-- Next Audit Date -->
              <div class="form-group next_audit-range{{ ($errors->has('next_audit_start') || $errors->has('next_audit_end')) ? ' has-error' : '' }}">
                  <label for="next_audit_start" class="col-md-3 control-label">{{ trans('general.next_audit_date') }}</label>
                  <div class="input-daterange input-group col-md-7" id="next_audit-range-datepicker">
                      <input type="text" class="form-control" name="next_audit_start" aria-label="next_audit_start" value="{{ $template->textValue('next_audit_start', old('next_audit_start')) }}">
                      <span class="input-group-addon">{{ strtolower(trans('general.to')) }}</span>
                      <input type="text" class="form-control" name="next_audit_end" aria-label="next_audit_end" value="{{ $template->textValue('next_audit_end', old('next_audit_end')) }}">
                  </div>

                  @if ($errors->has('next_audit_start') || $errors->has('next_audit_end'))
                      <div class="col-md-9 col-lg-offset-3">
                          {!! $errors->first('next_audit_start', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                          {!! $errors->first('next_audit_end', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                      </div>
                  @endif
              </div>

              <!-- Last updated Date -->
              <div class="form-group last_updated-range{{ ($errors->has('last_updated_start') || $errors->has('last_updated_end')) ? ' has-error' : '' }}">
                  <label for="last_updated_start" class="col-md-3 control-label">{{ trans('general.updated_at') }}</label>
                  <div class="input-daterange input-group col-md-7" id="last_updated-range-datepicker">
                      <input type="text" class="form-control" name="last_updated_start" aria-label="last_updated_start" value="{{ $template->textValue('last_updated_start', old('last_updated_start')) }}">
                      <span class="input-group-addon">{{ strtolower(trans('general.to')) }}</span>
                      <input type="text" class="form-control" name="last_updated_end" aria-label="last_updated_end" value="{{ $template->textValue('last_updated_end', old('last_updated_end')) }}">
                  </div>

                  @if ($errors->has('last_updated_start') || $errors->has('last_updated_end'))
                      <div class="col-md-9 col-lg-offset-3">
                          {!! $errors->first('last_updated_start', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                          {!! $errors->first('last_updated_end', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                      </div>
                  @endif

              </div>

            <div class="col-md-9 col-md-offset-3">
            <label class="form-control">
                <input type="checkbox" name="exclude_archived" value="1" @checked($template->checkmarkValue('exclude_archived', '0')) />
              {{ trans('general.exclude_archived') }}
            </label>
            </div>
            <div class="col-md-9 col-md-offset-3">
              <label class="form-control">
                  <input type="checkbox" name="use_bom" value="1" @checked($template->checkmarkValue('use_bom', '0')) />
                {{ trans('general.bom_remark') }}
              </label>
            </div>

              <div class="col-md-9 col-md-offset-3">
                  <label class="form-control">
                      <input
                          name="deleted_assets"
                          id="deleted_assets_exclude_deleted"
                          type="radio"
                          value="exclude_deleted"
                          @checked($template->radioValue('deleted_assets', 'exclude_deleted', true))
                          aria-label="deleted_assets"
                      >
                      {{ trans('general.exclude_deleted') }}
                  </label>
                  <label class="form-control">
                      <input
                          name="deleted_assets"
                          id="deleted_assets_include_deleted"
                          type="radio"
                          value="include_deleted"
                          @checked($template->radioValue('deleted_assets', 'include_deleted'))
                          aria-label="deleted_assets"
                      >
                    {{ trans('general.include_deleted') }}
                  </label>
                  <label class="form-control">
                      <input
                          name="deleted_assets"
                          type="radio"
                          id="deleted_assets_only_deleted"
                          value="only_deleted"
                          @checked($template->radioValue('deleted_assets', 'only_deleted'))
                          aria-label="deleted_assets"
                      >
                    {{ trans('general.only_deleted') }}
                  </label>
              </div>
          </div>

        </div> <!-- /.box-body-->
            <div class="box-footer text-right">
                @if (request()->routeIs('report-templates.edit'))
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check icon-white" aria-hidden="true"></i>
                        {{ trans('general.save') }}
                    </button>
                @else
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-download icon-white" aria-hidden="true"></i>
                        {{ trans('general.generate') }}
                    </button>
                @endif
            </div>
        </div> <!--/.box.box-default-->
        </form>
    </div>

    <!-- Saved Reports right column -->
    <div class="col-md-3">
        @if (! request()->routeIs('report-templates.edit'))
            <div class="form-group">
                <label for="saved_report_select">{{ trans('admin/reports/general.open_saved_template') }}</label>
                <select
                    id="saved_report_select"
                    class="form-control select2"
                    data-placeholder="{{ trans('admin/reports/general.select_a_template') }}"
                >
                    <option></option>
                    @foreach($report_templates as $savedTemplate)
                        <option
                            value="{{ $savedTemplate->id }}"
                            data-route="{{ route('report-templates.show', $savedTemplate->id) }}"
                            @selected($savedTemplate->is(request()->route()->parameter('reportTemplate')))
                        >
                            {{ $savedTemplate->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="row">
                <div class="col-md-12">
                @if (request()->routeIs('report-templates.show'))
                        <a
                                href="{{ route('report-templates.edit', $template) }}"
                                class="btn btn-sm btn-warning btn-social btn-block"
                                data-tooltip="true"
                                title="{{ trans('admin/reports/general.update_template') }}"
                                style="margin-bottom: 5px;"
                        >
                            <x-icon type="edit" />
                            {{ trans('general.update') }}
                        </a>

                    <span data-tooltip="true" title="{{ trans('general.delete') }}">
                        <a href="#"
                                class="btn btn-sm btn-danger btn-social btn-block delete-asset"
                                data-toggle="modal"
                                data-title="{{ trans('general.delete') }}"
                                data-content="{{ trans('general.delete_confirm', ['item' => $template->name]) }}"
                                data-target="#dataConfirmModal"
                                type="button"
                        >

                                <x-icon type="delete" />
                                {{ trans('general.delete') }}

                        </a>
                    </span>


                @endif
                </div>
            </div>
        @endif
        @if (request()->routeIs('reports/custom'))
            <hr>
            <div class="form-group">
                <form method="post" id="savetemplateform" action="{{ route("report-templates.store") }}">
                    @csrf
                    <input type="hidden" id="savetemplateform" name="options">
                    <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                        <label for="name">{{ trans('admin/reports/general.template_name') }}</label>
                        <input
                            class="form-control"
                            placeholder=""
                            name="name"
                            type="text"
                            id="name"
                            value="{{ $template->name }}"
                            required
                        >
                        {!! $errors->first('name', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                    </div>
                    <button class="btn btn-primary" style="width: 100%">
                        {{ trans('admin/reports/general.save_template') }}
                    </button>
                </form>
            </div>
            <div class="box box-success">
                <div class="box-header with-border">
                    <h4>{{ trans('admin/reports/message.about_templates') }}</h4>
                </div>
                <div class="box-body">
                    <p>{!!  trans('admin/reports/message.saving_templates_description')  !!}</p>
                </div>
            </div>
        @endif
    </div>
</div>

@stop

@section('moar_scripts')
  <script>

      $('.purchase-range .input-daterange').datepicker({
          clearBtn: true,
          todayHighlight: true,
          endDate: '0d',
          format: 'yyyy-mm-dd',
          keepEmptyValues: true,
      });

      $('.created-range .input-daterange').datepicker({
          clearBtn: true,
          todayHighlight: true,
          endDate: '0d',
          format: 'yyyy-mm-dd',
          keepEmptyValues: true,
      });

      $('.checkout-range .input-daterange').datepicker({
          clearBtn: true,
          todayHighlight: true,
          endDate: '0d',
          format: 'yyyy-mm-dd',
          keepEmptyValues: true,
      });

      $('.checkin-range .input-daterange').datepicker({
          clearBtn: true,
          todayHighlight: true,
          endDate: '0d',
          format: 'yyyy-mm-dd',
          keepEmptyValues: true,
      });

      $('.expected_checkin-range .input-daterange').datepicker({
          clearBtn: true,
          todayHighlight: true,
          format: 'yyyy-mm-dd',
          keepEmptyValues: true,
      });

      $('.asset_eol_date-range .input-daterange').datepicker({
          clearBtn: true,
          todayHighlight: true,
          format: 'yyyy-mm-dd',
          keepEmptyValues: true,
      });

      $('.last_audit-range .input-daterange').datepicker({
          clearBtn: true,
          todayHighlight: true,
          endDate:'0d',
          format: 'yyyy-mm-dd',
          keepEmptyValues: true,
      });

      $('.next_audit-range .input-daterange').datepicker({
          clearBtn: true,
          todayHighlight: true,
          format: 'yyyy-mm-dd',
          keepEmptyValues: true,
      });

      $('.last_updated-range .input-daterange').datepicker({
          clearBtn: true,
          todayHighlight: true,
          endDate:'0d',
          format: 'yyyy-mm-dd',
          keepEmptyValues: true,
      });


      $("#checkAll").change(function () {
        $("#included_fields_wrapper input:checkbox").prop('checked', $(this).prop("checked"));
      });

      $("#savetemplateform").submit(function(e) {
          e.preventDefault(e);

          let form = $('#custom-report-form');
          $('<input>').attr({
              type: 'hidden',
              name: 'name',
              value: $('#name').val(),
          }).appendTo(form);

          form.attr('action', '{{ route('report-templates.store') }}').submit();
      });

      $('#saved_report_select')
          .on('select2:select', function (event) {
              window.location.href = event.params.data.element.dataset.route;
          });
      
  </script>
@stop
