@extends('layouts/default')
{{-- Page title --}}
@section('title')
{{ trans('general.dashboard') }}
@parent
@stop

{{-- Header Right Buttons --}}
@section('header_right')
<button type="button" class="btn btn-default" id="toggleEditModeBtn" title="Edit Layout">
    <i class="fas fa-edit"></i> Edit
</button>
<button type="button" class="btn btn-default" id="widgetVisibilityBtn" title="Widget Visibility">
    <i class="fas fa-eye"></i> Visibility
</button>
@stop

@section('css')
<link rel="stylesheet" href="{{ url('css/dashboard-custom.css') }}">
<link rel="stylesheet" href="{{ url('css/dashboard-customization.css?v=2') }}">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style nonce="{{ csrf_token() }}">
    /* Force hide drag handles by default with maximum specificity */
    .widget-container .widget-handle,
    div.widget-handle,
    .widget-handle {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        height: 0 !important;
        overflow: hidden !important;
    }
    
    /* Show drag handles only in edit mode */
    body.dashboard-customization-mode .widget-container .widget-handle,
    body.dashboard-customization-mode div.widget-handle,
    body.dashboard-customization-mode .widget-handle {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        height: auto !important;
        overflow: visible !important;
    }
</style>
@stop

{{-- Page content --}}
@section('content')

@if ($snipeSettings->dashboard_message!='')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        {!!  Helper::parseEscapedMarkedown($snipeSettings->dashboard_message)  !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row sortable-row">

    <!-- panel -->
    <div class="col-lg-2 col-xs-6 widget-container" data-widget-id="assets">
        <div class="widget-handle" style="display:none;"><i class="fas fa-arrows-alt"></i> Drag</div>
        <a href="{{ route('hardware.index') }}">
            <!-- small hardware box -->
            <div class="dashboard small-box bg-teal">
                <div class="inner">
                    <h3>{{ number_format(\App\Models\Asset::AssetsForShow()->count()) }}</h3>
                    <p>{{ trans('general.assets') }}</p>
                </div>
                <div class="icon" aria-hidden="true">
                    <x-icon type="assets" />
                </div>
                <span class="small-box-footer">
                    {{ trans('general.view_all') }}
                    <x-icon type="arrow-circle-right" />
                </span>
            </div>
        </a>
    </div><!-- ./col -->

    <div class="col-lg-2 col-xs-6 widget-container" data-widget-id="licenses">
        <div class="widget-handle"><i class="fas fa-arrows-alt"></i> Drag</div>
        <a href="{{ route('licenses.index') }}" aria-hidden="true">
            <!-- small license box -->
            <div class="dashboard small-box bg-maroon">
                <div class="inner">
                    <h3>{{ number_format($counts['license']) }}</h3>
                    <p>{{ trans('general.licenses') }}</p>
                </div>
                <div class="icon" aria-hidden="true">
                    <x-icon type="licenses" />
                </div>
                <span class="small-box-footer">
                    {{ trans('general.view_all') }}
                    <x-icon type="arrow-circle-right" />
                </span>
            </div>
        </a>
    </div><!-- ./col -->


    <div class="col-lg-2 col-xs-6 widget-container" data-widget-id="accessories">
        <div class="widget-handle"><i class="fas fa-arrows-alt"></i> Drag</div>
        <a href="{{ route('accessories.index') }}">
            <div class="dashboard small-box bg-orange">
                <div class="inner">
                    <h3> {{ number_format($counts['accessory']) }}</h3>
                    <p>{{ trans('general.accessories') }}</p>
                </div>
                <div class="icon" aria-hidden="true">
                    <x-icon type="accessories" />
                </div>
                <span class="small-box-footer">
                    {{ trans('general.view_all') }}
                <x-icon type="arrow-circle-right" />
                </span>
            </div>
        </a>
    </div><!-- ./col -->

    <div class="col-lg-2 col-xs-6 widget-container" data-widget-id="consumables">
        <div class="widget-handle"><i class="fas fa-arrows-alt"></i> Drag</div>
        <a href="{{ route('consumables.index') }}">
            <div class="dashboard small-box bg-purple">
                <div class="inner">
                    <h3> {{ number_format($counts['consumable']) }}</h3>
                    <p>{{ trans('general.consumables') }}</p>
                </div>
                <div class="icon" aria-hidden="true">
                    <x-icon type="consumables" />
                </div>
                <span class="small-box-footer">
                    {{ trans('general.view_all') }}
                    <x-icon type="arrow-circle-right" />
                </span>
            </div>
        </a>
    </div><!-- ./col -->

    <div class="col-lg-2 col-xs-6 widget-container" data-widget-id="components">
        <div class="widget-handle"><i class="fas fa-arrows-alt"></i> Drag</div>
        <a href="{{ route('components.index') }}">
            <div class="dashboard small-box bg-yellow">
                <div class="inner">
                    <h3>{{ number_format($counts['component']) }}</h3>
                    <p>{{ trans('general.components') }}</p>
                </div>
                <div class="icon" aria-hidden="true">
                    <x-icon type="components" />
                </div>
                <span class="small-box-footer">
                    {{ trans('general.view_all') }}
                    <x-icon type="arrow-circle-right" />
                </span>
            </div>
        </a>
    </div><!-- ./col -->

    <div class="col-lg-2 col-xs-6 widget-container" data-widget-id="users">
        <div class="widget-handle"><i class="fas fa-arrows-alt"></i> Drag</div>
        <a href="{{ route('users.index') }}">
            <div class="dashboard small-box bg-light-blue">
                <div class="inner">
                    <h3>{{ number_format($counts['user']) }}</h3>
                    <p>{{ trans('general.people') }}</p>
                </div>
                <div class="icon" aria-hidden="true">
                    <x-icon type="users" />
                </div>
                <span class="small-box-footer">
                    {{ trans('general.view_all') }}
                    <x-icon type="arrow-circle-right" />
                </span>
            </div>
        </a>
    </div><!-- ./col -->

</div>

{{-- Maintenance Work Orders & Alerts (Only for users with kits.view permission) --}}
@if(auth()->user()->hasAccess('kits.view'))
<div class="row sortable-row" style="margin-top: 20px;">
    
    {{-- Combined Work Orders Summary Widget --}}
    <div class="col-md-12 widget-container" data-widget-id="workorders_summary">
        <div class="widget-handle"><i class="fas fa-arrows-alt"></i> Drag</div>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h2 class="box-title">
                    <i class="fas fa-clipboard-list"></i> Work Orders Summary
                </h2>
            </div>
            <div class="box-body">
                <div class="row">
                    {{-- Total Work Orders --}}
                    <div class="col-lg-3 col-xs-6">
                        <a href="{{ route('maintenance.workorders.index') }}">
                            <div class="dashboard small-box bg-aqua">
                                <div class="inner">
                                    <h3>{{ number_format($counts['workorder_total']) }}</h3>
                                    <p>Total Work Orders</p>
                                </div>
                                <div class="icon" aria-hidden="true">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <span class="small-box-footer">
                                    View All
                                    <i class="fas fa-arrow-circle-right"></i>
                                </span>
                            </div>
                        </a>
                    </div>

                    {{-- Pending Work Orders --}}
                    <div class="col-lg-3 col-xs-6">
                        <a href="{{ route('maintenance.workorders.index') }}?status=pending">
                            <div class="dashboard small-box bg-yellow">
                                <div class="inner">
                                    <h3>{{ number_format($counts['workorder_pending']) }}</h3>
                                    <p>Pending Orders</p>
                                </div>
                                <div class="icon" aria-hidden="true">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <span class="small-box-footer">
                                    View Pending
                                    <i class="fas fa-arrow-circle-right"></i>
                                </span>
                            </div>
                        </a>
                    </div>

                    {{-- In Progress Work Orders --}}
                    <div class="col-lg-3 col-xs-6">
                        <a href="{{ route('maintenance.workorders.index') }}?status=in_progress">
                            <div class="dashboard small-box bg-blue">
                                <div class="inner">
                                    <h3>{{ number_format($counts['workorder_in_progress']) }}</h3>
                                    <p>In Progress</p>
                                </div>
                                <div class="icon" aria-hidden="true">
                                    <i class="fas fa-cog fa-spin"></i>
                                </div>
                                <span class="small-box-footer">
                                    View Active
                                    <i class="fas fa-arrow-circle-right"></i>
                                </span>
                            </div>
                        </a>
                    </div>

                    {{-- Overdue Work Orders --}}
                    <div class="col-lg-3 col-xs-6">
                        <a href="{{ route('maintenance.workorders.index') }}?overdue=true">
                            <div class="dashboard small-box bg-red">
                                <div class="inner">
                                    <h3>{{ number_format($counts['workorder_overdue']) }}</h3>
                                    <p>Overdue Orders</p>
                                </div>
                                <div class="icon" aria-hidden="true">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <span class="small-box-footer">
                                    View Overdue
                                    <i class="fas fa-arrow-circle-right"></i>
                                </span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Maintenance To-Do List & Schedule Status (Next 7 Days) --}}
<div class="row sortable-row">
  <div class="col-md-12 widget-container" data-widget-id="maintenance_todo">
    <div class="widget-handle"><i class="fas fa-arrows-alt"></i> Drag</div>
    <div class="box box-primary">
      <div class="box-header with-border">
        <h2 class="box-title">
            <i class="fas fa-tasks"></i> Maintenance To-Do List (Next 7 Days)
        </h2>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true">
                <i class="fas fa-minus"></i>
                <span class="sr-only">Collapse</span>
            </button>
        </div>
      </div>
      <div class="box-body">
        {{-- Schedule Status Alerts --}}
        @if ($counts['schedule_overdue'] > 0 || $counts['schedule_upcoming'] > 0)
        <div class="row" style="margin-bottom: 20px;">
            @if ($counts['schedule_overdue'] > 0)
            <div class="col-md-6">
                <a href="{{ route('maintenance.scheduler.overdue') }}" style="text-decoration: none; display: block;">
                    <div class="alert alert-danger" style="margin-bottom: 0; cursor: pointer; transition: opacity 0.2s;" 
                         onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                        <h4><i class="icon fas fa-calendar-times"></i> Overdue Schedules</h4>
                        <p>You have <strong>{{ $counts['schedule_overdue'] }}</strong> overdue schedule(s).</p>
                    </div>
                </a>
            </div>
            @endif

            @if ($counts['schedule_upcoming'] > 0)
            <div class="col-md-{{ $counts['schedule_overdue'] > 0 ? '6' : '12' }}">
                <a href="{{ route('maintenance.scheduler.upcoming') }}" style="text-decoration: none; display: block;">
                    <div class="alert alert-info" style="margin-bottom: 0; cursor: pointer; transition: opacity 0.2s;"
                         onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                        <h4><i class="icon fas fa-calendar-check"></i> Upcoming Schedules</h4>
                        <p>You have <strong>{{ $counts['schedule_upcoming'] }}</strong> schedule(s) in next 7 days.</p>
                    </div>
                </a>
            </div>
            @endif
        </div>
        @endif

        <div class="row">
          <div class="col-md-12">
            <div class="table-responsive">
                <table
                    data-cookie-id-table="dashMaintenanceTodo"
                    data-height="400"
                    data-pagination="false"
                    data-side-pagination="server"
                    data-id-table="dashMaintenanceTodo"
                    id="dashMaintenanceTodo"
                    class="table table-striped snipe-table"
                    data-url="{{ route('api.maintenance.dashboard.todo') }}">
                    <thead>
                        <tr>
                            <th class="col-sm-1" data-field="priority_label" data-formatter="priorityFormatter">Priority</th>
                            <th class="col-sm-3" data-field="task" data-formatter="taskFormatter">Task</th>
                            <th class="col-sm-1" data-field="type" data-formatter="typeFormatter">Type</th>
                            <th class="col-sm-2" data-field="due_date" data-formatter="dueDateFormatter">Due Date</th>
                            <th class="col-sm-1" data-field="status" data-formatter="statusFormatter">Status</th>
                            <th class="col-sm-1" data-field="url" data-formatter="actionsFormatter">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endif
{{-- End of Maintenance Section --}}

<!-- recent activity -->
<div class="row sortable-row">
  <div class="col-md-8 widget-container" data-widget-id="recent_activity">
    <div class="widget-handle"><i class="fas fa-arrows-alt"></i> Drag</div>
    <div class="box">
      <div class="box-header with-border">
        <h2 class="box-title">{{ trans('general.recent_activity') }}</h2>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true">
                <x-icon type="minus" />
                <span class="sr-only">{{ trans('general.collapse') }}</span>
            </button>
        </div>
      </div><!-- /.box-header -->
      <div class="box-body">
        <div class="row">
          <div class="col-md-12">
            <div class="table-responsive">

                <table
                    data-cookie-id-table="dashActivityReport"
                    data-height="500"
                    data-pagination="false"
                    data-side-pagination="server"
                    data-id-table="dashActivityReport"
                    data-sort-order="desc"
                    data-sort-name="created_at"
                    id="dashActivityReport"
                    class="table table-striped snipe-table"
                    data-url="{{ route('api.activity.index', ['limit' => 25]) }}">
                    <thead>
                    <tr>
                        <th data-field="icon" data-visible="true" style="width: 40px;" class="hidden-xs" data-formatter="iconFormatter"><span  class="sr-only">{{ trans('admin/hardware/table.icon') }}</span></th>
                        <th class="col-sm-3" data-visible="true" data-field="created_at" data-formatter="dateDisplayFormatter">{{ trans('general.date') }}</th>
                        <th class="col-sm-2" data-visible="true" data-field="admin" data-formatter="usersLinkObjFormatter">{{ trans('general.created_by') }}</th>
                        <th class="col-sm-2" data-visible="true" data-field="action_type">{{ trans('general.action') }}</th>
                        <th class="col-sm-3" data-visible="true" data-field="item" data-formatter="polymorphicItemFormatter">{{ trans('general.item') }}</th>
                        <th class="col-sm-2" data-visible="true" data-field="target" data-formatter="polymorphicItemFormatter">{{ trans('general.target') }}</th>
                    </tr>
                    </thead>
                </table>



            </div><!-- /.responsive -->
          </div><!-- /.col -->
          <div class="text-center col-md-12" style="padding-top: 10px;">
            <a href="{{ route('reports.activity') }}" class="btn btn-primary btn-sm" style="width: 100%">{{ trans('general.viewall') }}</a>
          </div>
        </div><!-- /.row -->
      </div><!-- ./box-body -->
    </div><!-- /.box -->
  </div>
  <div class="col-md-4 widget-container" data-widget-id="asset_chart">
    <div class="widget-handle"><i class="fas fa-arrows-alt"></i> Drag</div>
        <div class="box box-default">
            <div class="box-header with-border">
                <h2 class="box-title">
                    {{ (\App\Models\Setting::getSettings()->dash_chart_type == 'name') ? trans('general.assets_by_status') : trans('general.assets_by_status_type') }}
                </h2>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true">
                        <x-icon type="minus" />
                        <span class="sr-only">{{ trans('general.collapse') }}</span>
                    </button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="chart-responsive">
                            <canvas id="statusPieChart" height="260"></canvas>
                        </div> <!-- ./chart-responsive -->
                    </div> <!-- /.col -->
                </div> <!-- /.row -->
            </div><!-- /.box-body -->
        </div> <!-- /.box -->
  </div>

</div> <!--/row-->
<div class="row sortable-row">
    <div class="col-md-6 widget-container" data-widget-id="companies">
        <div class="widget-handle"><i class="fas fa-arrows-alt"></i> Drag</div>
		@if ((($snipeSettings->scope_locations_fmcs!='1') && ($snipeSettings->full_multiple_companies_support=='1')))
			 <!-- Companies -->	
			<div class="box box-default">
				<div class="box-header with-border">
					<h2 class="box-title">{{ trans('general.companies') }}</h2>
					<div class="box-tools pull-right">
						<button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <x-icon type="minus" />
							<span class="sr-only">{{ trans('general.collapse') }}</span>
						</button>
					</div>
				</div>
				<!-- /.box-header -->
				<div class="box-body">
					<div class="row">
						<div class="col-md-12">
							<div class="table-responsive">
							<table
									data-cookie-id-table="dashCompanySummary"
									data-height="400"
                                    data-pagination="false"
									data-side-pagination="server"
									data-sort-order="desc"
									data-sort-field="assets_count"
									id="dashCompanySummary"
									class="table table-striped snipe-table"
									data-url="{{ route('api.companies.index', ['sort' => 'assets_count', 'order' => 'asc']) }}">

								<thead>
								<tr>
									<th class="col-sm-3" data-visible="true" data-field="name" data-formatter="companiesLinkFormatter" data-sortable="true">{{ trans('general.name') }}</th>
									<th class="col-sm-1" data-visible="true" data-field="users_count" data-sortable="true">
                                        <x-icon type="users" />
										<span class="sr-only">{{ trans('general.people') }}</span>
									</th>
									<th class="col-sm-1" data-visible="true" data-field="assets_count" data-sortable="true">
                                        <x-icon type="assets" />
										<span class="sr-only">{{ trans('general.asset_count') }}</span>
									</th>
									<th class="col-sm-1" data-visible="true" data-field="accessories_count" data-sortable="true">
                                        <x-icon type="accessories" />
										<span class="sr-only">{{ trans('general.accessories_count') }}</span>
									</th>
									<th class="col-sm-1" data-visible="true" data-field="consumables_count" data-sortable="true">
                                        <x-icon type="consumables" />
										<span class="sr-only">{{ trans('general.consumables_count') }}</span>
									</th>
									<th class="col-sm-1" data-visible="true" data-field="components_count" data-sortable="true">
                                        <x-icon type="components" />
										<span class="sr-only">{{ trans('general.components_count') }}</span>
									</th>
									<th class="col-sm-1" data-visible="true" data-field="licenses_count" data-sortable="true">
                                        <x-icon type="licenses" />
										<span class="sr-only">{{ trans('general.licenses_count') }}</span>
									</th>
								</tr>
								</thead>
							</table>
							</div>
						</div> <!-- /.col -->
						<div class="text-center col-md-12" style="padding-top: 10px;">
							<a href="{{ route('companies.index') }}" class="btn btn-primary btn-sm" style="width: 100%">{{ trans('general.viewall') }}</a>
						</div>
					</div> <!-- /.row -->

				</div><!-- /.box-body -->
			</div> <!-- /.box -->
		
		@else
            <div class="widget-container" data-widget-id="locations">
                <div class="widget-handle"><i class="fas fa-arrows-alt"></i> Drag</div>
			    <!-- Locations -->
			    <div class="box box-default">
				<div class="box-header with-border">
					<h2 class="box-title">{{ trans('general.locations') }}</h2>
					<div class="box-tools pull-right">
						<button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <x-icon type="minus" />
							<span class="sr-only">{{ trans('general.collapse') }}</span>
						</button>
					</div>
				</div>
				<!-- /.box-header -->
				<div class="box-body">
					<div class="row">
						<div class="col-md-12">
							<div class="table-responsive">
							<table
									data-cookie-id-table="dashLocationSummary"
									data-height="400"
									data-side-pagination="server"
                                    data-pagination="false"
									data-sort-order="desc"
									data-sort-field="assets_count"
									id="dashLocationSummary"
									class="table table-striped snipe-table"
									data-url="{{ route('api.locations.index', ['sort' => 'assets_count', 'order' => 'asc']) }}">
								<thead>
								<tr>
									<th class="col-sm-3" data-visible="true" data-field="name" data-formatter="locationsLinkFormatter" data-sortable="true">{{ trans('general.name') }}</th>
									
									<th class="col-sm-1" data-visible="true" data-field="assets_count" data-sortable="true">
                                        <x-icon type="assets" />
										<span class="sr-only">{{ trans('general.asset_count') }}</span>
									</th>
									<th class="col-sm-1" data-visible="true" data-field="assigned_assets_count" data-sortable="true">
										
										{{ trans('general.assigned') }}
									</th>
									<th class="col-sm-1" data-visible="true" data-field="users_count" data-sortable="true">
                                        <x-icon type="users" />
										<span class="sr-only">{{ trans('general.people') }}</span>
										
									</th>
									
								</tr>
								</thead>
							</table>
							</div>
						</div> <!-- /.col -->
						<div class="text-center col-md-12" style="padding-top: 10px;">
							<a href="{{ route('locations.index') }}" class="btn btn-primary btn-sm" style="width: 100%">{{ trans('general.viewall') }}</a>
						</div>
					</div> <!-- /.row -->

				</div><!-- /.box-body -->
			</div> <!-- /.box -->
            </div><!-- /.widget-container -->

		@endif
			
    </div>
    <div class="col-md-6 widget-container" data-widget-id="categories">
        <div class="widget-handle"><i class="fas fa-arrows-alt"></i> Drag</div>
        <!-- Categories -->
        <div class="box box-default">
            <div class="box-header with-border">
                <h2 class="box-title">{{ trans('general.asset') }} {{ trans('general.categories') }}</h2>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <x-icon type="minus" />
                        <span class="sr-only">{{ trans('general.collapse') }}</span>
                    </button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                        <table
                                data-cookie-id-table="dashCategorySummary"
                                data-height="400"
                                data-pagination="false"
                                data-side-pagination="server"
                                data-sort-order="desc"
                                data-sort-field="assets_count"
                                id="dashCategorySummary"
                                class="table table-striped snipe-table"
                                data-url="{{ route('api.categories.index', ['sort' => 'assets_count', 'order' => 'asc']) }}">
                            <thead>
                            <tr>
                                <th class="col-sm-3" data-visible="true" data-field="name" data-formatter="categoriesLinkFormatter" data-sortable="true">{{ trans('general.name') }}</th>
                                <th class="col-sm-3" data-visible="true" data-field="category_type" data-sortable="true">
                                    {{ trans('general.type') }}
                                </th>
                                <th class="col-sm-1" data-visible="true" data-field="assets_count" data-sortable="true">
                                    <x-icon type="assets" />
                                    <span class="sr-only">{{ trans('general.asset_count') }}</span>
                                </th>
                                <th class="col-sm-1" data-visible="true" data-field="accessories_count" data-sortable="true">
                                    <x-icon type="licenses" />
                                    <span class="sr-only">{{ trans('general.accessories_count') }}</span>
                                </th>
                                <th class="col-sm-1" data-visible="true" data-field="consumables_count" data-sortable="true">
                                    <x-icon type="consumables" />
                                    <span class="sr-only">{{ trans('general.consumables_count') }}</span>
                                </th>
                                <th class="col-sm-1" data-visible="true" data-field="components_count" data-sortable="true">
                                    <x-icon type="components" />
                                    <span class="sr-only">{{ trans('general.components_count') }}</span>
                                </th>
                                <th class="col-sm-1" data-visible="true" data-field="licenses_count" data-sortable="true">
                                    <x-icon type="licenses" />
                                    <span class="sr-only">{{ trans('general.licenses_count') }}</span>
                                </th>
                            </tr>
                            </thead>
                        </table>
                        </div>
                    </div> <!-- /.col -->
                    <div class="text-center col-md-12" style="padding-top: 10px;">
                        <a href="{{ route('categories.index') }}" class="btn btn-primary btn-sm" style="width: 100%">{{ trans('general.viewall') }}</a>
                    </div>
                </div> <!-- /.row -->

            </div><!-- /.box-body -->
        </div> <!-- /.box -->
    </div>
    </div><!-- /.widget-container -->

</div><!-- /.row sortable-row -->

@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['simple_view' => true, 'nopages' => true])
@stop

@push('js')

<script nonce="{{ csrf_token() }}">
    // Maintenance To-Do List Table Formatters
    function priorityFormatter(value, row) {
        if (!row.priority_class || !row.priority_label) return '';
        return '<span class="badge ' + row.priority_class + '"><i class="fas fa-exclamation-circle"></i> ' + row.priority_label + '</span>';
    }

    function taskFormatter(value, row) {
        var html = '<strong>' + row.task + '</strong>';
        if (row.description) {
            html += '<br><small class="text-muted">' + row.description + '</small>';
        }
        return html;
    }

    function typeFormatter(value, row) {
        if (!row.type_class || !row.type_icon) return row.type;
        return '<span class="badge ' + row.type_class + '"><i class="' + row.type_icon + '"></i> ' + row.type + '</span>';
    }

    function dueDateFormatter(value, row) {
        var dateClass = row.is_overdue ? 'text-danger' : '';
        var html = '<span class="' + dateClass + '">' + row.due_date_human + '</span>';
        if (row.is_overdue) {
            html += ' <i class="fas fa-exclamation-triangle text-danger"></i>';
        }
        return html;
    }

    function statusFormatter(value, row) {
        if (!row.status_class) return row.status;
        return '<span class="label ' + row.status_class + '">' + row.status + '</span>';
    }

    function actionsFormatter(value, row) {
        if (!row.url) return '';
        return '<a href="' + row.url + '" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a>';
    }
</script>

<script src="{{ url(mix('js/dist/Chart.min.js')) }}"></script>
<script nonce="{{ csrf_token() }}">
    // ---------------------------
    // - ASSET STATUS CHART -
    // ---------------------------
      var pieChartCanvas = $("#statusPieChart").get(0).getContext("2d");
      var pieChart = new Chart(pieChartCanvas);
      var ctx = document.getElementById("statusPieChart");
      var pieOptions = {
              legend: {
                  position: 'top',
                  responsive: true,
                  maintainAspectRatio: true,
              },
              tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        counts = data.datasets[0].data;
                        total = 0;
                        for(var i in counts) {
                            total += counts[i];
                        }
                        prefix = data.labels[tooltipItem.index] || '';
                        return prefix+" "+Math.round(counts[tooltipItem.index]/total*100)+"%";
                    }
                }
              }
          };

      $.ajax({
          type: 'GET',
          url: '{{ (\App\Models\Setting::getSettings()->dash_chart_type == 'name') ? route('api.statuslabels.assets.byname') : route('api.statuslabels.assets.bytype') }}',
          headers: {
              "X-Requested-With": 'XMLHttpRequest',
              "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
          },
          dataType: 'json',
          success: function (data) {
              var myPieChart = new Chart(ctx,{
                  type   : 'pie',
                  data   : data,
                  options: pieOptions
              });
          },
          error: function (data) {
              // window.location.reload(true);
          },
      });
        var last = document.getElementById('statusPieChart').clientWidth;
        addEventListener('resize', function() {
        var current = document.getElementById('statusPieChart').clientWidth;
        if (current != last) location.reload();
        last = current;
    });
</script>

{{-- Widget Visibility Modal --}}
<div class="modal fade" id="widgetVisibilityModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">
                    <i class="fas fa-eye"></i> Widget Visibility Settings
                </h4>
            </div>
            <div class="modal-body settings-modal">
                <p class="text-muted">
                    <i class="fas fa-info-circle"></i>
                    Select which widgets to display on your dashboard.
                </p>
                <hr>
                <h5><strong>Widget Visibility</strong></h5>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary"><i class="fas fa-chart-bar"></i> Asset Widgets</h6>
                        <div class="widget-checkbox">
                            <label>
                                <input type="checkbox" class="widget-toggle" data-widget="assets" checked>
                                <i class="fas fa-server"></i> Assets
                            </label>
                        </div>
                        <div class="widget-checkbox">
                            <label>
                                <input type="checkbox" class="widget-toggle" data-widget="licenses" checked>
                                <i class="fas fa-certificate"></i> Licenses
                            </label>
                        </div>
                        <div class="widget-checkbox">
                            <label>
                                <input type="checkbox" class="widget-toggle" data-widget="accessories" checked>
                                <i class="fas fa-keyboard"></i> Accessories
                            </label>
                        </div>
                        <div class="widget-checkbox">
                            <label>
                                <input type="checkbox" class="widget-toggle" data-widget="consumables" checked>
                                <i class="fas fa-tint"></i> Consumables
                            </label>
                        </div>
                        <div class="widget-checkbox">
                            <label>
                                <input type="checkbox" class="widget-toggle" data-widget="components" checked>
                                <i class="fas fa-hdd"></i> Components
                            </label>
                        </div>
                        <div class="widget-checkbox">
                            <label>
                                <input type="checkbox" class="widget-toggle" data-widget="users" checked>
                                <i class="fas fa-users"></i> Users
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        @if(auth()->user()->hasAccess('kits.view'))
                        <h6 class="text-success"><i class="fas fa-wrench"></i> Maintenance Widgets</h6>
                        <div class="widget-checkbox">
                            <label>
                                <input type="checkbox" class="widget-toggle" data-widget="workorders_summary" checked>
                                <i class="fas fa-clipboard-list"></i> Work Orders Summary
                            </label>
                        </div>
                        <div class="widget-checkbox">
                            <label>
                                <input type="checkbox" class="widget-toggle" data-widget="maintenance_todo" checked>
                                <i class="fas fa-tasks"></i> Maintenance To-Do List
                            </label>
                        </div>
                        @endif
                        
                        <h6 class="text-info"><i class="fas fa-chart-line"></i> Activity & Reports</h6>
                        <div class="widget-checkbox">
                            <label>
                                <input type="checkbox" class="widget-toggle" data-widget="recent_activity" checked>
                                <i class="fas fa-history"></i> Recent Activity
                            </label>
                        </div>
                        <div class="widget-checkbox">
                            <label>
                                <input type="checkbox" class="widget-toggle" data-widget="asset_chart" checked>
                                <i class="fas fa-chart-pie"></i> Asset Status Chart
                            </label>
                        </div>
                        <div class="widget-checkbox">
                            <label>
                                <input type="checkbox" class="widget-toggle" data-widget="companies" checked>
                                <i class="fas fa-building"></i> Companies
                            </label>
                        </div>
                        <div class="widget-checkbox">
                            <label>
                                <input type="checkbox" class="widget-toggle" data-widget="locations" checked>
                                <i class="fas fa-map-marker-alt"></i> Locations
                            </label>
                        </div>
                        <div class="widget-checkbox">
                            <label>
                                <input type="checkbox" class="widget-toggle" data-widget="categories" checked>
                                <i class="fas fa-sitemap"></i> Categories
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" id="resetDashboardBtn">
                    <i class="fas fa-undo"></i> Reset to Default
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveVisibilityBtn">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script nonce="{{ csrf_token() }}">
$(document).ready(function() {
    // Widget configuration from backend
    let widgetConfig = @json($widgets ?? []);
    let editMode = false;
    
    // FORCE HIDE all drag handles on page load
    $('.widget-handle').hide();
    
    // Open visibility modal
    $('#widgetVisibilityBtn').click(function() {
        $('#widgetVisibilityModal').modal('show');
        loadWidgetStates();
    });
    
    // Toggle Edit Mode
    $('#toggleEditModeBtn').click(function() {
        editMode = !editMode;
        
        if (editMode) {
            // Enter edit mode
            $('body').addClass('dashboard-customization-mode');
            $(this).removeClass('btn-default').addClass('btn-warning');
            $(this).html('<i class="fas fa-save"></i> Save');
            
            // Show all drag handles
            $('.widget-handle').show();
            
            // Disable all buttons and links inside widget boxes (but not the header buttons)
            $('.box button, .box a, .small-box a').addClass('disabled-in-edit').css('pointer-events', 'none');
            
            enableSortable();
        } else {
            // Exit edit mode and save
            saveLayout();
            $('body').removeClass('dashboard-customization-mode');
            $(this).removeClass('btn-warning').addClass('btn-default');
            $(this).html('<i class="fas fa-edit"></i> Edit');
            
            // Hide all drag handles
            $('.widget-handle').hide();
            
            // Re-enable all buttons and links
            $('.disabled-in-edit').removeClass('disabled-in-edit').css('pointer-events', '');
            
            disableSortable();
        }
    });
    
    // Load widget visibility states
    function loadWidgetStates() {
        $.get('{{ route('api.dashboard.widgets.index') }}', function(response) {
            if (response.success) {
                response.widgets.forEach(function(widget) {
                    const checkbox = $('.widget-toggle[data-widget="' + widget.widget_id + '"]');
                    checkbox.prop('checked', widget.is_visible);
                    
                    // Apply visibility immediately
                    const widgetElement = $('[data-widget-id="' + widget.widget_id + '"]');
                    if (widget.is_visible) {
                        widgetElement.removeClass('widget-hidden').show();
                    } else {
                        widgetElement.addClass('widget-hidden').hide();
                    }
                });
            }
        });
    }
    
    // Toggle widget visibility (immediate preview, but not saved until Save button)
    $('.widget-toggle').change(function() {
        const widgetId = $(this).data('widget');
        const isVisible = $(this).is(':checked');
        
        // Immediately show/hide the widget as preview
        const widgetElement = $('[data-widget-id="' + widgetId + '"]');
        if (isVisible) {
            widgetElement.removeClass('widget-hidden').fadeIn();
        } else {
            widgetElement.addClass('widget-hidden').fadeOut();
        }
    });
    
    // Save visibility changes
    $('#saveVisibilityBtn').click(function() {
        const visibilityChanges = [];
        
        $('.widget-toggle').each(function() {
            const widgetId = $(this).data('widget');
            const isVisible = $(this).is(':checked');
            visibilityChanges.push({
                widget_id: widgetId,
                is_visible: isVisible ? 1 : 0
            });
        });
        
        // Save all visibility states
        const widgets = [];
        let sortOrder = 0;
        
        $('.sortable-row').each(function(rowIndex) {
            $(this).find('[data-widget-id]').each(function(colIndex) {
                const widgetId = $(this).data('widget-id');
                const checkbox = $('.widget-toggle[data-widget="' + widgetId + '"]');
                
                let isVisible = 1;
                if (checkbox.length > 0) {
                    isVisible = checkbox.prop('checked') ? 1 : 0;
                }
                
                widgets.push({
                    widget_id: String(widgetId),
                    is_visible: isVisible,
                    grid_x: parseInt(colIndex) || 0,
                    grid_y: parseInt(rowIndex) || 0,
                    grid_width: 2,
                    grid_height: 1,
                    sort_order: parseInt(sortOrder++)
                });
            });
        });
        
        $.ajax({
            url: '{{ route('api.dashboard.widgets.update') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                widgets: widgets
            },
            success: function(response) {
                if (response.success) {
                    $('#widgetVisibilityModal').modal('hide');
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Widget visibility saved successfully!');
                    } else {
                        alert('Widget visibility saved successfully!');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.log('Error saving visibility:', xhr.responseText);
                if (typeof toastr !== 'undefined') {
                    toastr.error('Error saving visibility settings');
                } else {
                    alert('Error saving visibility settings');
                }
            }
        });
    });
    
    // Enable sortable
    function enableSortable() {
        $('.sortable-row').sortable({
            items: '.widget-container',
            handle: '.widget-handle',
            placeholder: 'ui-sortable-placeholder',
            connectWith: '.sortable-row',
            tolerance: 'pointer',
            cursor: 'move',
            opacity: 0.8,
            forcePlaceholderSize: true,
            start: function(e, ui) {
                ui.placeholder.height(ui.item.height());
                ui.placeholder.width(ui.item.width());
            },
            stop: function(e, ui) {
                console.log('Widget moved');
            }
        });
        console.log('Sortable enabled on ' + $('.sortable-row').length + ' rows');
    }
    
    // Disable sortable
    function disableSortable() {
        if ($('.sortable-row').hasClass('ui-sortable')) {
            $('.sortable-row').sortable('destroy');
        }
    }
    
    // Save layout function
    function saveLayout() {
        const widgets = [];
        let sortOrder = 0;
        
        $('.sortable-row').each(function(rowIndex) {
            $(this).find('[data-widget-id]').each(function(colIndex) {
                const widgetId = $(this).data('widget-id');
                const checkbox = $('.widget-toggle[data-widget="' + widgetId + '"]');
                
                // Determine visibility - default to 1 if checkbox doesn't exist
                let isVisible = 1;
                if (checkbox.length > 0) {
                    isVisible = checkbox.prop('checked') ? 1 : 0;
                }
                
                // Ensure all fields are provided with correct types
                widgets.push({
                    widget_id: String(widgetId),
                    is_visible: isVisible,
                    grid_x: parseInt(colIndex) || 0,
                    grid_y: parseInt(rowIndex) || 0,
                    grid_width: 2,
                    grid_height: 1,
                    sort_order: parseInt(sortOrder++)
                });
            });
        });
        
        console.log('Saving widgets:', widgets);
        console.log('First widget is_visible type:', typeof widgets[0].is_visible, 'value:', widgets[0].is_visible);
        
        $.ajax({
            url: '{{ route('api.dashboard.widgets.update') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                widgets: widgets
            },
            success: function(response) {
                console.log('Save success:', response);
                if (response.success) {
                    // Show success message
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Dashboard layout saved successfully!');
                    } else {
                        alert('Dashboard layout saved successfully!');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.log('Error details:', xhr.responseText);
                console.log('Status:', status);
                console.log('Error:', error);
                
                let errorMessage = 'Error saving dashboard configuration';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage += ': ' + xhr.responseJSON.message;
                    }
                    if (xhr.responseJSON.errors) {
                        console.log('Validation errors:', xhr.responseJSON.errors);
                        errorMessage += '\nValidation errors: ' + JSON.stringify(xhr.responseJSON.errors);
                    }
                }
                
                if (typeof toastr !== 'undefined') {
                    toastr.error(errorMessage);
                } else {
                    alert(errorMessage);
                }
            }
        });
    }
    
    // Reset to default
    $('#resetDashboardBtn').click(function() {
        if (confirm('Are you sure you want to reset the dashboard to default configuration?')) {
            $.post('{{ route('api.dashboard.widgets.reset') }}', {
                _token: '{{ csrf_token() }}'
            }, function(response) {
                if (response.success) {
                    alert('Dashboard reset successfully!');
                    location.reload();
                }
            });
        }
    });
    
    // Load initial state
    loadWidgetStates();
});
</script>

@endpush

