{{-- bs-table backed by /api/v1/low-stock which delegates to
     Helper::checkLowInventory so this widget and the top-nav alert
     bell can't drift. polymorphicItemFormatter handles the per-type
     icon + drilldown link. The shared adjust-quantity button hangs
     off each row's available_actions.adjust_quantity via the generic
     actions column. LowStockController filters rows by per-type view
     permission server-side, so a viewer only sees rows for the types
     they can view. --}}
@if (Gate::allows('view', \App\Models\Consumable::class)
     || Gate::allows('view', \App\Models\Accessory::class)
     || Gate::allows('view', \App\Models\Component::class)
     || Gate::allows('view', \App\Models\AssetModel::class)
     || Gate::allows('view', \App\Models\License::class))
    <div class="box box-default">
            <div class="box-header with-border">
                <h2 class="box-title">
                    {{ trans('general.dashboard_low_stock') }}
                    {{-- Info icon: explains the formula the alert
                         uses (remaining < min + alert_threshold) so
                         the widget doesn't look arbitrary to someone
                         who hasn't set a min_amt or threshold. No
                         link on the icon since non-superuser admins
                         see the dashboard but can't reach the alert-
                         threshold setting. --}}
                    <span data-tooltip="true"
                          title="{{ trans('general.dashboard_low_stock_help', ['threshold' => (int) $snipeSettings->alert_threshold]) }}"
                          class="text-muted"
                          style="cursor: help;">
                        <x-icon type="more-info" class="fa-fw"/>
                        <span class="sr-only">{{ trans('general.dashboard_low_stock_help', ['threshold' => (int) $snipeSettings->alert_threshold]) }}</span>
                    </span>
                </h2>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true">
                        <x-icon type="minus"/>
                        <span class="sr-only">{{ trans('general.collapse') }}</span>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <table
                    data-cookie-id-table="dashLowStock"
                    data-pagination="false"
                    data-side-pagination="server"
                    data-id-table="dashLowStock"
                    data-advanced-search="false"
                    data-sticky-header="false"
                    data-search="false"
                    data-show-columns="false"
                    data-show-columns-toggle-all="false"
                    data-show-fullscreen="false"
                    data-show-print="false"
                    data-show-refresh="false"
                    data-show-export="false"
                    data-empty-message="{{ trans('general.dashboard_low_stock_empty') }}"
                    id="dashLowStock"
                    class="table table-striped snipe-table snipe-table--sticky-right-1"
                    data-url="{{ route('api.low-stock.index', ['limit' => 25]) }}">
                    <thead>
                        <tr>
                            <th scope="col" data-field="item" data-formatter="polymorphicItemFormatter">{{ trans('general.name') }}</th>
                            <th scope="col" data-field="remaining" data-sortable="true" class="text-right">{{ trans('general.remaining') }}</th>
                            <th scope="col" data-field="min_amt" data-sortable="true" data-formatter="minAmtFormatter" class="text-right">{{ trans('general.min_amt') }}</th>
                            <th scope="col" data-field="available_actions" data-formatter="lowStockActionsFormatter" class="hidden-print text-right">
                                <span class="sr-only">{{ trans('table.actions') }}</span>
                            </th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
@endif
