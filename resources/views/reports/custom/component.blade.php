@extends('layouts/default')

{{-- Page Title --}}
@section('title')
    @if ($template->exists)
        {{ trans('general.custom_component_report') }}: {{ $template->name }}
    @else
        {{ trans('general.custom_component_report') }}
    @endif
    @parent
@stop

@section('header_right')
    @if ($template->exists)
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

            {{-- Form action is always custom.component.run (download). The
                 sidebar Save button overrides this via formaction so it
                 POSTs to report-templates.update instead. --}}
            <form
                method="POST"
                action="{{ route('reports.custom.component.run') }}"
                accept-charset="UTF-8"
                class="form-horizontal"
                id="custom-report-form"
            >
                {{csrf_field()}}

                <!-- Horizontal Form -->
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h2 class="box-title" style="padding-top: 7px;">
                            @if ($template->exists)
                                {{ trans('general.customize_report') }}: {{ $template->name }}
                            @else
                                {{ trans('general.customize_report') }}
                            @endif
                        </h2>

                    </div><!-- /.box-header -->

                    <div class="box-body">

                        <div class="col-md-4" id="included_fields_wrapper">

                            <label class="form-control">
                                <input type="checkbox" data-toggle="check-all" data-check-scope="#included_fields_wrapper" checked="checked">
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
                                <input type="checkbox" name="archived" value="1" @checked($template->checkmarkValue('archived')) />
                                {{ trans('general.archived') }}
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

                            <x-input.select2-checkboxes
                                name="by_company_id"
                                :label="trans('general.company')"
                                endpoint="companies"
                                :selected="$template->selectValues('by_company_id', \App\Models\Company::class)"
                                :modelClass="\App\Models\Company::class"
                            />
                            <x-input.select2-checkboxes
                                name="by_category_id"
                                :label="trans('general.category')"
                                endpoint="categories/component"
                                :selected="$template->selectValues('by_category_id', \App\Models\Category::class)"
                                :modelClass="\App\Models\Category::class"
                            />
                            <x-input.select2-checkboxes
                                name="by_manufacturer_id"
                                :label="trans('general.manufacturer')"
                                endpoint="manufacturers"
                                :selected="$template->selectValues('by_manufacturer_id', \App\Models\Manufacturer::class)"
                                :modelClass="\App\Models\Manufacturer::class"
                            />
                            <x-input.select2-checkboxes
                                name="by_supplier_id"
                                :label="trans('general.supplier')"
                                endpoint="suppliers"
                                :selected="$template->selectValues('by_supplier_id', \App\Models\Supplier::class)"
                                :modelClass="\App\Models\Supplier::class"
                            />
                            <x-input.select2-checkboxes
                                name="by_location_id"
                                :label="trans('general.location')"
                                endpoint="locations"
                                :selected="$template->selectValues('by_location_id', \App\Models\Location::class)"
                                :modelClass="\App\Models\Location::class"
                            />

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
                                        <x-form.error name="quantity_start" />
                                        <x-form.error name="quantity_end" />
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
                                        <x-form.error name="min_quantity_start" />
                                        <x-form.error name="min_quantity_end" />
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
                                        <x-form.error name="unit_cost_start" />
                                        <x-form.error name="unit_cost_end" />
                                    </div>
                                @endif
                            </div>

                            <!-- Purchase Date -->
                            <div class="form-group purchase-range{{ ($errors->has('purchase_start') || $errors->has('purchase_end')) ? ' has-error' : '' }}">
                                <label for="purchase_start" class="col-md-3 control-label">{{ trans('general.purchase_date') }}</label>
                                <x-input.date-range
                                    class="col-md-8"
                                    id="purchase-range-datepicker"
                                    name_start="purchase_start"
                                    name_end="purchase_end"
                                    :value_start="$template->textValue('purchase_start', old('purchase_start'))"
                                    :value_end="$template->textValue('purchase_end', old('purchase_end'))"
                                    max_date="today"
                                />

                                @if ($errors->has('purchase_start') || $errors->has('purchase_end'))
                                    <div class="col-md-9 col-lg-offset-3">
                                        <x-form.error name="purchase_start"/>
                                        <x-form.error name="purchase_end"/>
                                    </div>
                                @endif
                            </div>

                            <!-- Checkout Date -->
                            <div class="form-group checkout-range{{ ($errors->has('checkout_date_start') || $errors->has('checkout_date_end')) ? ' has-error' : '' }}">
                                <label for="checkout_date" class="col-md-3 control-label">{{ trans('general.checkout') }} </label>
                                <x-input.date-range
                                    class="col-md-8"
                                    id="checkout-range-datepicker"
                                    name_start="checkout_date_start"
                                    name_end="checkout_date_end"
                                    :value_start="$template->textValue('checkout_date_start', old('checkout_date_start'))"
                                    :value_end="$template->textValue('checkout_date_end', old('checkout_date_end'))"
                                    max_date="today"
                                />

                                @if ($errors->has('checkout_date_start') || $errors->has('checkout_date_end'))
                                    <div class="col-md-9 col-lg-offset-3">
                                        <x-form.error name="checkout_date_start" />
                                        <x-form.error name="checkout_date_end" />
                                    </div>
                                @endif
                            </div>

                            <!-- Created Date -->
                            <div class="form-group created-range{{ ($errors->has('created_start') || $errors->has('created_end')) ? ' has-error' : '' }}">
                                <label for="created_start" class="col-md-3 control-label">{{ trans('general.created_at') }} </label>
                                <x-input.date-range
                                    class="col-md-8"
                                    id="created-range-datepicker"
                                    name_start="created_start"
                                    name_end="created_end"
                                    :value_start="$template->textValue('created_start', old('created_start'))"
                                    :value_end="$template->textValue('created_end', old('created_end'))"
                                    max_date="today"
                                />

                                @if ($errors->has('created_start') || $errors->has('created_end'))
                                    <div class="col-md-9 col-lg-offset-3">
                                        <x-form.error name="created_start" />
                                        <x-form.error name="created_end" />
                                    </div>
                                @endif
                            </div>

                            <!-- Last updated Date -->
                            <div class="form-group last_updated-range{{ ($errors->has('last_updated_start') || $errors->has('last_updated_end')) ? ' has-error' : '' }}">
                                <label for="last_updated_start" class="col-md-3 control-label">{{ trans('general.updated_at') }}</label>
                                <x-input.date-range
                                    class="col-md-8"
                                    id="last_updated-range-datepicker"
                                    name_start="last_updated_start"
                                    name_end="last_updated_end"
                                    :value_start="$template->textValue('last_updated_start', old('last_updated_start'))"
                                    :value_end="$template->textValue('last_updated_end', old('last_updated_end'))"
                                    max_date="today"
                                />

                                @if ($errors->has('last_updated_start') || $errors->has('last_updated_end'))
                                    <div class="col-md-9 col-lg-offset-3">
                                        <x-form.error name="last_updated_start" />
                                        <x-form.error name="last_updated_end" />
                                    </div>
                                @endif
                            </div>

                            <!-- Last Updated before -->
                            <div class="form-group">
                                <label for="last_updated_before" class="col-md-3 control-label">{{ trans('general.updated_before') }}</label>
                                <div class="input-group col-md-3">
                                    <input class="form-control input-group" type="number" min="0" name="last_updated_before" value="{{ $template->textValue('last_updated_before', old('last_updated_before')) }}" aria-label="last_updated_before">
                                    <span class="input-group-addon">{{ trans('general.days_ago') }}</span>
                                </div>

                                @if ($errors->has('last_updated_before'))
                                    <div class="col-md-9 col-lg-offset-3">
                                        <x-form.error name="last_updated_before" />
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

                            <x-form.radio-row
                                name="deleted_components"
                                :selected="$template->options['deleted_components'] ?? 'exclude_deleted'"
                                :options="[
                                    'exclude_deleted' => trans('admin/components/general.exclude_deleted'),
                                    'include_deleted' => trans('admin/components/general.include_deleted'),
                                    'only_deleted' => trans('admin/components/general.only_deleted'),
                                ]"
                            />
                        </div>

                    </div> <!-- /.box-body-->
                    <div class="box-footer text-right">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-download icon-white" aria-hidden="true"></i>
                            {{ trans('general.download') }}
                        </button>
                    </div>
                </div> <!--/.box.box-default-->
            </form>
        </div>

        <!-- Saved Reports right column -->
        <div class="col-md-3">
            <x-reports.custom-template-panel type="component" :template="$template"/>
        </div>
    </div>

@stop

