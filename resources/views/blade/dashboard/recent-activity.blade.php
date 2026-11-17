{{-- Dashboard recent-activity table. Renders just the box. The
     parent dashboard blade owns the row + col wrapper so it can
     redistribute width when neighbor widgets hide. Kept the internal
     gate as defense in depth for direct-invocation cases. Scoped
     viewers (canViewUsersAndCheckoutables but not activity.view) get
     results filtered server-side to actionlogs whose item_type or
     target_type they can view. See Api\DashboardController@activity. --}}
@can('canViewUsersAndCheckoutables')
    <div class="box box-default">
        <div class="box-header with-border">
            <h2 class="box-title">{{ trans('general.recent_activity') }}</h2>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true">
                    <x-icon type="minus" />
                    <span class="sr-only">{{ trans('general.collapse') }}</span>
                </button>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
                    <table
                        data-cookie-id-table="dashActivityReport"
                        data-pagination="false"
                        data-side-pagination="server"
                        data-advanced-search="false"
                        data-id-table="dashActivityReport"
                        data-sort-order="desc"
                        data-show-columns="false"
                        data-sort-name="created_at"
                        data-sticky-header="false"
                        data-empty-message="{{ trans('general.dashboard_activity_empty') }}"
                        id="dashActivityReport"
                        class="table table-striped snipe-table"
                        data-url="{{ route('api.dashboard.activity', ['limit' => 25]) }}">
                        <thead>
                            <tr>
                                <th scope="col" data-field="icon" data-visible="true" style="width: 40px;" class="hidden-xs" data-formatter="iconFormatter"><span class="sr-only">{{ trans('admin/hardware/table.icon') }}</span></th>
                                <th scope="col" class="col-sm-3" data-visible="true" data-field="created_at" data-formatter="dateDisplayFormatter">{{ trans('general.date') }}</th>
                                <th scope="col" class="col-sm-2" data-visible="true" data-field="admin" data-formatter="usersLinkObjFormatter">{{ trans('general.created_by') }}</th>
                                <th scope="col" class="col-sm-2" data-visible="true" data-field="action_type">{{ trans('general.action') }}</th>
                                <th scope="col" class="col-sm-3" data-visible="true" data-field="item" data-formatter="polymorphicItemFormatter">{{ trans('general.item') }}</th>
                                <th scope="col" class="col-sm-2" data-visible="true" data-field="target" data-formatter="polymorphicItemFormatter">{{ trans('general.target') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        {{-- reports.activity is widened to accept scoped viewers via
             canViewUsersAndCheckoutables, backed by
             api.dashboard.activity for callers without activity.view.
             CSV export and other admin UI on the report page stay
             gated inside that view. --}}
        <div class="box-footer text-center">
            <a href="{{ route('reports.activity') }}" class="btn btn-theme btn-sm btn-block">{{ trans('general.viewall') }}</a>
        </div>
    </div>
@endcan
