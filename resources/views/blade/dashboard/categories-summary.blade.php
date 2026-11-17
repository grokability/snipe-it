@if (Gate::allows('view', \App\Models\Asset::class)
     || Gate::allows('view', \App\Models\Accessory::class)
     || Gate::allows('view', \App\Models\Consumable::class)
     || Gate::allows('view', \App\Models\Component::class)
     || Gate::allows('view', \App\Models\License::class))
    <div class="box box-default">
        <div class="box-header with-border">
            <h2 class="box-title">{{ trans('general.categories') }}</h2>
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
                        data-cookie-id-table="dashCategorySummary"
                        data-height="400"
                        data-pagination="false"
                        data-side-pagination="server"
                        data-advanced-search="false"
                        data-show-columns="false"
                        data-sort-order="desc"
                        data-sort-field="assets_count"
                        data-sticky-header="false"
                        id="dashCategorySummary"
                        class="table table-striped snipe-table"
                        data-url="{{ route('api.dashboard.categories', ['sort' => 'assets_count', 'order' => 'asc']) }}">
                        <thead>
                            <tr>
                                <th scope="col" class="col-sm-3" data-visible="true" data-field="name" data-formatter="categoriesLinkFormatter" data-sortable="true">{{ trans('general.name') }}</th>
                                <th scope="col" class="col-sm-3" data-visible="true" data-field="category_type" data-sortable="true">
                                    {{ trans('general.type') }}
                                </th>
                                @can('view', \App\Models\Asset::class)
                                    <th scope="col" class="col-sm-1" data-visible="true" data-field="assets_count" data-sortable="true">
                                        <x-icon type="assets"/>
                                        <span class="sr-only">{{ trans('general.asset_count') }}</span>
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
                                @can('view', \App\Models\License::class)
                                    <th scope="col" class="col-sm-1" data-visible="true" data-field="licenses_count" data-sortable="true">
                                        <x-icon type="licenses"/>
                                        <span class="sr-only">{{ trans('general.licenses_count') }}</span>
                                    </th>
                                @endcan
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <div class="box-footer text-center">
            <a href="{{ route('categories.index') }}" class="btn btn-theme btn-sm btn-block">{{ trans('general.viewall') }}</a>
        </div>
    </div>
@endif
