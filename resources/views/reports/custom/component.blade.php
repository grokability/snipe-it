@extends('layouts/default')

{{-- Page Title --}}
@section('title')
    @if (request()->routeIs('report-templates.edit'))
        {{ trans('general.update') }} {{ $template->name }}
    @elseif(request()->routeIs('report-templates.show'))
        {{ trans('general.custom_component_report') }}: {{ $template->name }}
    @else
        {{ trans('general.custom_component_report') }}
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
                action="{{ request()->routeIs('report-templates.edit') ? route('report-templates.update', $template) : route('reports.custom.component.run') }}"
                accept-charset="UTF-8"
                class="form-horizontal"
                id="custom-report-form"
            >
                {{csrf_field()}}

                <!-- Horizontal Form -->
                <div class="box box-default">
                    <div class="box-header with-border">
                        @if (request()->routeIs('reports.custom.component', 'report-templates.show'))
                            <h2 class="box-title" style="padding-top: 7px;">
                                {{ trans('general.customize_report') }}
                            </h2>

                        @endif

                        @if (request()->routeIs('report-templates.edit'))
                            <div class="form-group {{ $errors->has('name') ? ' has-error' : '' }}">
                                <label
                                    for="name"
                                    class="col-md-2 control-label"
                                >
                                    {{ trans('admin/reports/general.template_name') }}
                                </label>
                                <div class="col-md-5">
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
                                @if ($template->created_by == auth()->id())
                                    <div class="col-md-3">
                                        <label class="form-control">
                                            <input type="checkbox" name="is_shared" value="1" @checked($template->is_shared) />
                                            {{ trans('admin/reports/general.share_template') }}
                                        </label>
                                    </div>
                                @endif
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
                                <input type="checkbox" name="category" value="1" @checked($template->checkmarkValue('category')) />
                                {{ trans('general.category') }}
                            </label>

                            <label class="form-control">
                                <input type="checkbox" name="component_name" value="1" @checked($template->checkmarkValue('component_name')) />
                                {{ trans('admin/components/general.component_name') }}
                            </label>

                            <label class="form-control">
                                <input type="checkbox" name="manufacturer" value="1" @checked($template->checkmarkValue('manufacturer')) />
                                {{ trans('general.manufacturer') }}
                            </label>

                            <label class="form-control">
                                <input type="checkbox" name="model" value="1" @checked($template->checkmarkValue('model_number')) />
                                {{ trans('general.model_no') }}
                            </label>

                            <label class="form-control">
                                <input type="checkbox" name="serial" value="1" @checked($template->checkmarkValue('serial')) />
                                {{ trans('general.serial_number') }}
                            </label>

                            <label class="form-control">
                                <input type="checkbox" name="purchase_date" value="1" @checked($template->checkmarkValue('purchase_date')) />
                                {{ trans('admin/licenses/table.purchase_date') }}
                            </label>

                            <label class="form-control">
                                <input type="checkbox" name="quantity" value="1" @checked($template->checkmarkValue('quantity')) />
                                {{ trans('general.quantity') }}
                            </label>

                            <label class="form-control">
                                <input type="checkbox" name="min_amount" value="1" @checked($template->checkmarkValue('min_amount')) />
                                {{ trans('general.min_amt') }}
                            </label>

                            <label class="form-control">
                                <input type="checkbox" name="unit_cost" value="1" @checked($template->checkmarkValue('unit_cost')) />
                                {{ trans('general.unit_cost') }}
                            </label>

                            <label class="form-control">
                                <input type="checkbox" name="order" value="1" @checked($template->checkmarkValue('order')) />
                                {{ trans('admin/hardware/form.order') }}
                            </label>

                            <label class="form-control">
                                <input type="checkbox" name="supplier" value="1" @checked($template->checkmarkValue('supplier')) />
                                {{ trans('general.supplier') }}
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
                                <input type="checkbox" name="checkout_date" value="1" @checked($template->checkmarkValue('checkout_date')) />
                                {{ trans('admin/hardware/table.checkout_date') }}
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
                                <input type="checkbox" name="notes" value="1" @checked($template->checkmarkValue('notes')) />
                                {{ trans('general.notes') }}
                            </label>

                            <h2>{{ trans('general.assigned') }}: </h2>
                            <label class="form-control">
                                <input type="checkbox" name="include_assignments" value="1" @checked($template->checkmarkValue('include_assignments', '0')) />
                                {{ trans('general.include_assignments') }}
                            </label>

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

                            @include ('partials.forms.edit.category-select', [
                                    'translated_name' => trans('general.category'),
                                    'fieldname' => 'by_category_id[]',
                                    'multiple' => 'true',
                                    'hide_new' => 'true',
                                    'category_type' => 'component',
                                    'selected' => $template->selectValues('by_category_id', \App\Models\Category::class),
                            ])

                            @include ('partials.forms.edit.manufacturer-select', [
                                    'translated_name' => trans('general.manufacturer'),
                                    'fieldname' => 'by_manufacturer_id[]',
                                    'multiple' => 'true',
                                    'hide_new' => 'true',
                                    'selected' => $template->selectValues('by_manufacturer_id', \App\Models\Manufacturer::class),
                            ])

                            @include ('partials.forms.edit.supplier-select', [
                                    'translated_name' => trans('general.supplier'),
                                    'fieldname' => 'by_supplier_id[]',
                                    'multiple' => 'true',
                                    'hide_new' => 'true',
                                    'selected' => $template->selectValues('by_supplier_id', \App\Models\Supplier::class),
                            ])

                            @include ('partials.forms.edit.location-select', [
                                    'translated_name' => trans('general.location'),
                                    'fieldname' => 'by_location_id[]',
                                    'multiple' => 'true',
                                    'hide_new' => 'true',
                                    'selected' => $template->selectValues('by_location_id', \App\Models\Location::class),
                            ])

                            <!-- Name -->
                            <div class="form-group">
                                <label for="by_name" class="col-md-3 control-label">{{ trans('general.name') }}</label>
                                <div class="col-md-7">
                                    <input class="form-control" type="text" name="by_name" value="{{ $template->textValue('by_name', old('by_name')) }}" aria-label="by_name">
                                </div>
                            </div>

                            <!-- Model Number -->
                            <div class="form-group">
                                <label for="by_model_number" class="col-md-3 control-label">{{ trans('general.model_no') }}</label>
                                <div class="col-md-7">
                                    <input class="form-control" type="text" name="by_model_number" value="{{ $template->textValue('by_model_number', old('by_model_number')) }}" aria-label="by_model_number">
                                </div>
                            </div>

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

                                    <input type="text" placeholder="{{ trans('general.select_date') }}" class="form-control" name="purchase_start" aria-label="purchase_start" value="{{ $template->textValue('purchase_start', old('purchase_start')) }}">
                                    <span class="input-group-addon"> - </span>
                                    <input type="text" placeholder="{{ trans('general.select_date') }}" class="form-control" name="purchase_end" aria-label="purchase_end" value="{{ $template->textValue('purchase_end', old('purchase_end')) }}">
                                </div>

                                @if ($errors->has('purchase_start') || $errors->has('purchase_end'))
                                    <div class="col-md-9 col-lg-offset-3">
                                        {!! $errors->first('purchase_start', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                        {!! $errors->first('purchase_end', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                    </div>
                                @endif
                            </div>

                            <!-- Quantity -->
                            <div class="form-group quantity-range{{ ($errors->has('quantity_start') || $errors->has('quantity_end')) ? ' has-error' : '' }}">
                                <label for="quantity_start" class="col-md-3 control-label">{{ trans('general.quantity') }}</label>
                                <div class="input-group col-md-7">
                                    <input type="number" min="0" class="form-control" name="quantity_start" aria-label="quantity_start" value="{{ $template->textValue('quantity_start', old('quantity_start')) }}">
                                    <span class="input-group-addon"> - </span>
                                    <input type="number" min="0" class="form-control" name="quantity_end" aria-label="quantity_end" value="{{ $template->textValue('quantity_end', old('quantity_end')) }}">
                                </div>

                                @if ($errors->has('quantity_start') || $errors->has('quantity_end'))
                                    <div class="col-md-9 col-lg-offset-3">
                                        {!! $errors->first('quantity_start', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                        {!! $errors->first('quantity_end', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                    </div>
                                @endif
                            </div>

                            <!-- Min. Quantity -->
                            <div class="form-group min_quantity-range{{ ($errors->has('min_quantity_start') || $errors->has('min_quantity_end')) ? ' has-error' : '' }}">
                                <label for="min_quantity_start" class="col-md-3 control-label">{{ trans('mail.min_QTY') }}</label>
                                <div class="input-group col-md-7">
                                    <input type="number" min="0" class="form-control" name="min_quantity_start" aria-label="min_quantity_start" value="{{ $template->textValue('min_quantity_start', old('min_quantity_start')) }}">
                                    <span class="input-group-addon"> - </span>
                                    <input type="number" min="0" class="form-control" name="min_quantity_end" aria-label="min_quantity_end" value="{{ $template->textValue('min_quantity_end', old('min_quantity_end')) }}">
                                </div>

                                @if ($errors->has('min_quantity_start') || $errors->has('min_quantity_end'))
                                    <div class="col-md-9 col-lg-offset-3">
                                        {!! $errors->first('min_quantity_start', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                        {!! $errors->first('min_quantity_end', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                    </div>
                                @endif
                            </div>

                            <!-- Unit Cost -->
                            <div class="form-group unit-range{{ ($errors->has('unit_cost_start') || $errors->has('unit_cost_end')) ? ' has-error' : '' }}">
                                <label for="unit_cost_start" class="col-md-3 control-label">{{ trans('general.unit_cost') }}</label>
                                <div class="input-group col-md-7">
                                    <input type="number" min="0" step="0.01" class="form-control" name="unit_cost_start" aria-label="unit_cost_start" value="{{ $template->textValue('unit_cost_start', old('unit_cost_start')) }}">
                                    <span class="input-group-addon"> - </span>
                                    <input type="number" min="0" step="0.01" class="form-control" name="unit_cost_end" aria-label="unit_cost_end" value="{{ $template->textValue('unit_cost_end', old('unit_cost_end')) }}">
                                </div>

                                @if ($errors->has('unit_cost_start') || $errors->has('unit_cost_end'))
                                    <div class="col-md-9 col-lg-offset-3">
                                        {!! $errors->first('unit_cost_start', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                        {!! $errors->first('unit_cost_end', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                    </div>
                                @endif
                            </div>

                            <!-- Checkout Date -->
                            <div class="form-group checkout-range{{ ($errors->has('checkout_date_start') || $errors->has('checkout_date_end')) ? ' has-error' : '' }}">
                                <label for="checkout_date" class="col-md-3 control-label">{{ trans('general.checkout') }} </label>
                                <div class="input-daterange input-group col-md-7" id="checkout-range-datepicker">
                                    <input type="text" placeholder="{{ trans('general.select_date') }}"  class="form-control" name="checkout_date_start" aria-label="checkout_date_start" value="{{ $template->textValue('checkout_date_start', old('checkout_date_start')) }}">
                                    <span class="input-group-addon"> - </span>
                                    <input type="text" placeholder="{{ trans('general.select_date') }}" class="form-control" name="checkout_date_end" aria-label="checkout_date_end" value="{{ $template->textValue('checkout_date_end', old('checkout_date_end')) }}">
                                </div>

                                @if ($errors->has('checkout_date_start') || $errors->has('checkout_date_end'))
                                    <div class="col-md-9 col-lg-offset-3">
                                        {!! $errors->first('checkout_date_start', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                        {!! $errors->first('checkout_date_end', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                    </div>
                                @endif
                            </div>

                            <!-- Created Date -->
                            <div class="form-group created-range{{ ($errors->has('created_start') || $errors->has('created_end')) ? ' has-error' : '' }}">
                                <label for="created_start" class="col-md-3 control-label">{{ trans('general.created_at') }} </label>
                                <div class="input-daterange input-group col-md-7" id="created-range-datepicker">
                                    <input type="text" placeholder="{{ trans('general.select_date') }}" class="form-control" name="created_start" aria-label="created_start" value="{{ $template->textValue('created_start', old('created_start')) }}">
                                    <span class="input-group-addon"> - </span>
                                    <input type="text" placeholder="{{ trans('general.select_date') }}" class="form-control" name="created_end" aria-label="created_end" value="{{ $template->textValue('created_end', old('created_end')) }}">
                                </div>

                                @if ($errors->has('created_start') || $errors->has('created_end'))
                                    <div class="col-md-9 col-lg-offset-3">
                                        {!! $errors->first('created_start', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                        {!! $errors->first('created_end', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                    </div>
                                @endif
                            </div>

                            <!-- Last updated Date -->
                            <div class="form-group last_updated-range{{ ($errors->has('last_updated_start') || $errors->has('last_updated_end')) ? ' has-error' : '' }}">
                                <label for="last_updated_start" class="col-md-3 control-label">{{ trans('general.updated_at') }}</label>
                                <div class="input-daterange input-group col-md-7" id="last_updated-range-datepicker">
                                    <input type="text" placeholder="{{ trans('general.select_date') }}"  class="form-control" name="last_updated_start" aria-label="last_updated_start" value="{{ $template->textValue('last_updated_start', old('last_updated_start')) }}">
                                    <span class="input-group-addon"> - </span>
                                    <input type="text" placeholder="{{ trans('general.select_date') }}"  class="form-control" name="last_updated_end" aria-label="last_updated_end" value="{{ $template->textValue('last_updated_end', old('last_updated_end')) }}">
                                </div>

                                @if ($errors->has('last_updated_start') || $errors->has('last_updated_end'))
                                    <div class="col-md-9 col-lg-offset-3">
                                        {!! $errors->first('last_updated_start', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                        {!! $errors->first('last_updated_end', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                    </div>
                                @endif
                            </div>

                            <!-- Last Updated before -->
                            <div class="form-group">
                                <label for="last_updated_before" class="col-md-3 control-label">{{ trans('general.updated_before') }}</label>
                                <div class="input-group col-md-2">
                                    <input class="form-control input-group" type="number" min="0" name="last_updated_before" value="{{ $template->textValue('last_updated_before', old('last_updated_before')) }}" aria-label="last_updated_before">
                                    {{ trans('general.days_ago') }}
                                </div>

                                @if ($errors->has('last_updated_before'))
                                    <div class="col-md-9 col-lg-offset-3">
                                        {!! $errors->first('last_updated_before', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                    </div>
                                @endif
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
                                        name="deleted_components"
                                        id="deleted_components_exclude_deleted"
                                        type="radio"
                                        value="exclude_deleted"
                                        @checked($template->radioValue('deleted_components', 'exclude_deleted', true))
                                        aria-label="deleted_components"
                                    >{{ trans('admin/components/general.exclude_deleted') }}
                                </label>
                                <label class="form-control">
                                    <input
                                        name="deleted_components"
                                        id="deleted_components_include_deleted"
                                        type="radio"
                                        value="include_deleted"
                                        @checked($template->radioValue('deleted_components', 'include_deleted'))
                                        aria-label="deleted_components"
                                    >{{ trans('admin/components/general.include_deleted') }}
                                </label>
                                <label class="form-control">
                                    <input
                                        name="deleted_components"
                                        type="radio"
                                        id="deleted_components_only_deleted"
                                        value="only_deleted"
                                        @checked($template->radioValue('deleted_components', 'only_deleted'))
                                        aria-label="deleted_components"
                                    >{{ trans('admin/components/general.only_deleted') }}
                                </label>
                            </div>
                        </div>

                    </div> <!-- /.box-body-->
                    <div class="box-footer text-right">
                        @if(request()->routeIs('report-templates.edit'))
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-download icon-white" aria-hidden="true"></i>
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
                <livewire:report-template-select type="component" />

                <div class="row">
                    <div class="col-md-12">

                        <div style="margin-bottom: 5px;">
                            @if($template->name)
                                @if($template->created_by == auth()->id())
                                    <span class="text-center">{!!  ($template->is_shared ? '<i class="fa fa-users"></i>'." ".(trans('admin/reports/general.template_shared_with_others')) : '<i class="fa fa-user"></i>'." ".(trans('admin/reports/general.template_not_shared')) )!!}</span>
                                @else
                                    <span class="text-center">{!!  ($template->is_shared ? '<i class="fa fa-users"></i>'." ".(trans('admin/reports/general.template_shared')) : '<i class="fa fa-user"></i>'." ".(trans('admin/reports/general.template_not_shared')) )!!}</span>
                                @endif
                            @endif
                        </div>


                        @if($template->created_by == auth()->id())
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
                               class="btn btn-sm btn-danger btn-social btn-block delete-component"
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
                        @endif
                    </div>
                </div>
            @endif
            @if (request()->routeIs('reports/custom', 'reports.custom.component'))
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
        $("#checkAll").change(function () {
            $("#included_fields_wrapper input:checkbox").prop('checked', $(this).prop("checked"));
        });

        $('.purchase-range .input-daterange').datepicker({
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

        $('.created-range .input-daterange').datepicker({
            clearBtn: true,
            todayHighlight: true,
            endDate:'0d',
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

        $("#savetemplateform").submit(function(e) {
            e.preventDefault(e);

            let form = $('#custom-report-form');
            $('<input>').attr({
                type: 'hidden',
                name: 'name',
                value: $('#name').val(),
            }).appendTo(form);

            $('<input>').attr({
                type: 'hidden',
                name: 'type',
                value: 'component',
            }).appendTo(form);

            form.attr('action', '{{ route('report-templates.store') }}').submit();
        });

        $('#saved_report_select').on('select2:select', function (event) {
            window.location.href = event.params.data.element.dataset.route;
        });

    </script>
@stop
