@can('canViewUsersAndCheckoutables')
    <div class="box box-default">
        <div class="box-header with-border">
            <h2 class="box-title">{{ trans('general.locations') }}</h2>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <x-icon type="minus"/>
                    <span class="sr-only">{{ trans('general.collapse') }}</span>
                </button>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
                    <table
                        data-cookie-id-table="dashLocationSummary"
                        data-height="400"
                        data-side-pagination="server"
                        data-pagination="false"
                        data-advanced-search="false"
                        data-sort-order="desc"
                        data-sort-field="assets_count"
                        data-sticky-header="false"
                        id="dashLocationSummary"
                        data-show-columns="false"
                        class="table table-striped snipe-table"
                        data-url="{{ route('api.dashboard.locations', ['sort' => 'assets_count', 'order' => 'asc']) }}">
                        <thead>
                            <tr>
                                <th scope="col" class="col-sm-3" data-visible="true" data-field="name" data-formatter="locationsLinkFormatter" data-sortable="true">{{ trans('general.name') }}</th>
                                @can('view', \App\Models\Asset::class)
                                    <th scope="col" class="col-sm-1" data-visible="true" data-field="assets_count" data-sortable="true">
                                        <x-icon type="assets"/>
                                        <span class="sr-only">{{ trans('general.asset_count') }}</span>
                                    </th>
                                    <th scope="col" class="col-sm-1" data-visible="true" data-field="assigned_assets_count" data-sortable="true">
                                        {{ trans('general.assigned') }}
                                    </th>
                                @endcan
                                @can('view', \App\Models\Accessory::class)
                                    <th scope="col" class="col-sm-1" data-visible="true" data-field="accessories_count" data-sortable="true">
                                        <x-icon type="accessories"/>
                                        <span class="sr-only">{{ trans('general.accessories_count') }}</span>
                                    </th>
                                @endcan
                                @can('view', \App\Models\Consumable::class)
                                    <th scope="col" class="col-sm-1" data-visible="true" data-field="consumables_count" data-sortable="true">
                                        <x-icon type="consumables"/>
                                        <span class="sr-only">{{ trans('general.consumables_count') }}</span>
                                    </th>
                                @endcan
                                @can('view', \App\Models\Component::class)
                                    <th scope="col" class="col-sm-1" data-visible="true" data-field="components_count" data-sortable="true">
                                        <x-icon type="components"/>
                                        <span class="sr-only">{{ trans('general.components_count') }}</span>
                                    </th>
                                @endcan
                                @can('view', \App\Models\User::class)
                                    <th scope="col" class="col-sm-1" data-visible="true" data-field="users_count" data-sortable="true">
                                        <x-icon type="users"/>
                                        <span class="sr-only">{{ trans('general.people') }}</span>
                                    </th>
                                @endcan
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <div class="box-footer text-center">
            <a href="{{ route('locations.index') }}" class="btn btn-theme btn-sm btn-block">{{ trans('general.viewall') }}</a>
        </div>
    </div>
@endcan
