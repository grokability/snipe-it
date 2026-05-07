@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/hardware/general.view') }} {{ $asset->asset_tag }}
    @parent
@stop

@section('header_right')
    <x-button.info-panel-toggle hide-on-xs/>
@endsection

{{-- Page content --}}
@section('content')


    <x-container columns="2">

        @if (!$asset->model)
            <div class="col-md-12">
                <div class="callout callout-danger">
                    <p>
                        <strong>{{ trans('admin/models/message.no_association') }}</strong> {{ trans('admin/models/message.no_association_fix') }}
                    </p>
                </div>
            </div>
        @endif

        @if ($asset->checkInvalidNextAuditDate())
            <div class="col-md-12">
                <div class="callout callout-warning">
                    <p><strong>{{ trans('general.warning',
                        [
                            'warning' => trans('admin/hardware/message.warning_audit_date_mismatch',
                                    [
                                        'last_audit_date' => Helper::getFormattedDateObject($asset->last_audit_date, 'datetime', false),
                                        'next_audit_date' => Helper::getFormattedDateObject($asset->next_audit_date, 'date', false)
                                    ]
                                    )
                        ]
                        ) }}</strong></p>
                </div>
            </div>
        @endif

        @if ($asset->deleted_at!='')
            <div class="col-md-12">
                <div class="callout callout-warning">
                    <x-icon type="warning"/>
                    {{ trans('general.asset_deleted_warning') }}
                </div>
            </div>
        @endif

        <x-page-column class="col-md-9 main-panel">

            <x-tabs>
                <x-slot:tabnav>
                    <x-tabs.details-tab/>
                    <x-tabs.license-tab count="{{ $asset->licenses->count() }}"/>
                    <x-tabs.component-tab count="{{ $asset->components()->sum('assigned_qty') }}"/>
                    <x-tabs.asset-tab count="{{ $asset->assignedAssets()->AssetsForShow()->count() }}"/>
                    <x-tabs.accessory-tab count="{{ $asset->assignedAccessories()->count() }}"/>
                    <x-tabs.maintenance-tab count="{{ $asset->maintenances->count() }}"/>

                    <x-tabs.nav-item
                        name="audits"
                        icon_type="audit"
                        label="{{ trans('general.audits') }}"
                        count="{{ $asset->audits()->count() }}"
                        tooltip="{{ trans('general.audits') }}"
                    />
                    <x-tabs.note-tab :item="$asset" count="{{ $asset->journal->count() }}"/>
                    <x-tabs.files-tab :item="$asset" count="{{ $asset->uploads()->count() }}"/>
                    <x-tabs.model-files-tab count="{{ $asset->model?->uploads()->count() }}"/>
                    <x-tabs.history-tab count="{{ $asset->history()->count() }}" :model="$asset"/>
                    <x-tabs.upload-tab :item="$asset"/>
                </x-slot:tabnav>

                <x-slot:tabpanes>

                    <!-- start details tab content -->
                    <x-tabs.pane name="details">

                        <!-- this just adds a little top space -->
                        <div class="clearfix visible-lg-block" style="padding: 6px;"></div>

                        <!--  well column -->
                        <x-page-column class="col-md-4">
                            <x-well>
                                <x-info-element.status :infoObject="$asset"/>
                            </x-well>
                        </x-page-column>
                        <!-- ./ well column -->

                        <!--  well column -->
                        <x-page-column class="col-md-4">
                            <x-well>
                                <x-icon type="calendar" class="fa-fw"/>
                                <strong>{{ trans('general.last_checkout') }}</strong>
                                @if ($asset->last_checkout != '')
                                    {{ Helper::getFormattedDateObject($asset->last_checkout, 'date', false) }}
                                    <span class="text-muted">{{ Carbon::parse($asset->last_checkout)->diffForHumans(['parts' => 2]) }}</span>
                                @else
                                    {{ trans('general.na') }}
                                @endif
                            </x-well>
                        </x-page-column>
                        <!--  ./ well column -->

                        <!--  well column -->
                        <x-page-column class="col-md-4">
                            <x-well>
                                <x-icon type="expected_checkin" class="fa-fw"/>
                                <strong>{{ trans('general.expected_checkin') }}</strong>
                                @if ($asset->expected_checkin!='')
                                    {{ Helper::getFormattedDateObject($asset->expected_checkin, 'date', false) }}
                                    <span class="text-muted hidden-sm hidden-md">{{ Carbon::parse($asset->expected_checkin)->diffForHumans(['parts' => 2]) }}</span>
                                @else
                                    {{ trans('general.na') }}
                                @endif
                            </x-well>

                        </x-page-column>
                        <!--  ./ well column -->

                        <!-- set clearfix for responsive design -->
                        <div class="clearfix"></div>


                        <!--  definition list column -->
                        <x-page-column class="col-md-8">

                            <!-- definition list content -->
                            <x-page-data>

                                <x-data-row :label="trans('admin/hardware/form.tag')" copy_what="asset_tag">
                                    {{ $asset->asset_tag }}
                                </x-data-row>

                                <x-data-row :label="trans('admin/hardware/form.name')" copy_what="asset_name">
                                    {{ $asset->name }}
                                </x-data-row>

                                <x-data-row :label="trans('admin/hardware/table.current_value')" copy_what="current_value">
                                    {{ (($asset->id) && ($asset->location) ? $asset->location->currency : $snipeSettings->default_currency) }}
                                    {{ Helper::formatCurrencyOutput($asset->getDepreciatedValue() )}}
                                </x-data-row>

                                <x-data-row :label="trans('general.last_audit')" copy_what="audit_date">
                                    @if ((isset($audit_log)) && ($audit_log->created_at))
                                        {!! $asset->checkInvalidNextAuditDate() ? '<i class="fas fa-exclamation-triangle text-orange" aria-hidden="true"></i>' : '' !!}
                                        {{ Helper::getFormattedDateObject($audit_log->created_at, 'datetime', false) }}
                                        <span class="text-muted">{{ Carbon::parse($audit_log->created_at)->diffForHumans(['parts' => 2]) }}</span>
                                        @if ($audit_log->user)
                                            -
                                            <a href="{{ route('users.show', $audit_log->user->id) }}">{{ $audit_log->user->display_name }}</a>
                                        @endif
                                    @endif
                                </x-data-row>

                                <x-data-row :label="trans('general.next_audit_date')" copy_what="next_audit_date">
                                    {!! $asset->checkInvalidNextAuditDate() ? '<i class="fas fa-exclamation-triangle text-orange" aria-hidden="true"></i>' : '' !!}
                                    {{ Helper::getFormattedDateObject($asset->next_audit_date, 'date', false) }}

                                    @if ($asset->next_audit_date)
                                        <span class="text-muted">{{ Carbon::parse($asset->next_audit_date)->diffForHumans(['parts' => 2]) }}</span>
                                    @endif
                                </x-data-row>

                                <x-data-row :label="trans('admin/hardware/form.default_location')" copy_what="default_location">
                                    {!!  $asset->defaultLoc?->present()->formattedNameLink !!}
                                </x-data-row>

                                @if ($asset->asset_eol_date)
                                    <x-data-row :label="trans('general.device_eol')" copy_what="eol_date">
                                        @if ($asset->asset_eol_date)
                                            {{ Helper::getFormattedDateObject($asset->asset_eol_date, 'date', false) }}
                                            -
                                            <span class="text-muted">{{ Carbon::parse($asset->asset_eol_date)->locale(app()->getLocale())->diffForHumans(['parts' => 3]) }}</span>
                                        @else
                                            {{ trans('general.na_no_purchase_date') }}
                                        @endif
                                        @if ($asset->eol_explicit =='1')
                                            <span data-tooltip="true" data-placement="top" data-title="Explicit EOL" title="Explicit EOL">
                                                <x-icon type="warning" class="text-primary"/>
                                            </span>
                                        @endif
                                    </x-data-row>
                                @endif


                                @if (($asset->model) && ($asset->model->fieldset))
                                    @foreach($asset->model->fieldset->fields as $field)
                                        <x-data-row :label="$field->name">
                                            <x-info-element.customfield :item="$asset" :field="$field"/>
                                        </x-data-row>
                                    @endforeach
                                @endif



                                @if($asset->journal->last())
                                    <x-data-row :label="trans('general.last_note')" copy_what="last_note">
                                        <i class="fa-solid fa-quote-left"></i>
                                        {{ $asset->journal->last()->note }}
                                        <i class="fa-solid fa-quote-right"></i>
                                        <span class="text-muted">
                                            - {!!  $asset->journal->last()->adminuser->present()->formattedNameLink !!}
                                            ({{ Helper::getFormattedDateObject($asset->journal->last()->created_at, 'datetime', false) }})
                                        </span>
                                    </x-data-row>
                                @endif

                            </x-page-data>
                            <!-- ./ definition list content -->

                        </x-page-column>
                        <!-- ./ definition list column -->

                        <!-- start side stats column -->
                        <x-page-column class="col-md-4 col-sm-12">

                            @if ($asset->hasOrphanedAssignment())
                                <x-well class="well-sm">
                                    <p class="text-danger" style="line-height: 20px;">
                                        <x-icon type="warning" class="text-danger"/> {{ trans('general.warning', ['warning' => trans('general.item_target_not_found_hard', ['item_type' => $asset->assignedType(), 'id' => $asset->assigned_to])]) }}
                                    </p>

                                    <form action="{{ route('asset.checkin.force', $asset) }}" method="POST" class="form-inline" style="display: inline;">
                                        {{ csrf_field() }}
                                        {{ method_field('POST') }}
                                        <button class="btn btn-sm btn-danger btn-block hidden-print" type="submit" data-tooltip="true" data-placement="top" data-title="{{ trans('general.force_checkin') }}">
                                            <x-icon type="checkin" class="fa-fw"/>
                                            {{ trans('general.force_checkin') }}
                                        </button>
                                    </form>

                                </x-well>
                            @endif


                            @if(($asset->purchase_date && $asset->asset_eol_date) || $asset->depreciated_date() || $asset->warranty_expires)
                                <x-well class="well-sm">
                                    @if($asset->purchase_date && $asset->asset_eol_date)
                                        <x-progressbar use_well="false" columns="12" text="{{ trans('general.device_eol') }}" :percent="$asset->eolProgressPercent()">
                                            (<strong>{{ (int) Carbon::now()->diffInMonths($asset->asset_eol_date, true) }}</strong>/{{ $asset->model?->eol }} {{ trans('general.months') }})
                                        </x-progressbar>
                                    @endif

                                    @if($asset->depreciated_date())
                                        <x-progressbar use_well="false" columns="12" :text="trans('admin/hardware/form.fully_depreciated')" :percent="$asset->depreciationProgressPercent()">
                                            {{ Helper::getFormattedDateObject($asset->depreciated_date()->format('Y-m-d'), 'date', false) }}
                                        </x-progressbar>
                                    @endif

                                    @if($asset->warranty_expires)
                                        <x-progressbar use_well="false" columns="12" :text="trans('admin/hardware/form.warranty_expires')" :percent="$asset->warrantyProgressPercent()">
                                        {{ Helper::getFormattedDateObject($asset->warranty_expires, 'date', false) }}
                                        </x-progressbar>
                                    @endif

                                </x-well>
                            @endif

                            <x-well class="well-sm">
                                <div class="well-display">
                                    <x-data-row icon_type="money" :label="trans('general.purchase_cost')" align="right">
                                        {{ Helper::formatCurrencyOutput($asset->purchase_cost) }}
                                    </x-data-row>

                                    <x-data-row icon_type="maintenances" :label="trans('general.maintenances')" align="right">
                                        {{ Helper::formatCurrencyOutput($total_maintenance_cost) }}
                                    </x-data-row>

                                    <x-data-row icon_type="accessories" :label="trans('general.accessories')" align="right">
                                        {{ Helper::formatCurrencyOutput($total_accessory_cost) }}
                                    </x-data-row>

                                    <x-data-row icon_type="licenses" :label="trans('general.licenses')" align="right">
                                        {{ Helper::formatCurrencyOutput($total_license_cost) }}
                                    </x-data-row>

                                    <x-data-row icon_type="components" :label="trans('general.components')" align="right">
                                        {{ Helper::formatCurrencyOutput($total_component_cost) }}
                                    </x-data-row>

                                    <x-data-row icon_type="assets" :label="trans('general.assets')" align="right">
                                        {{ Helper::formatCurrencyOutput($total_asset_cost) }}
                                    </x-data-row>

                                    <x-data-row :label="trans('general.total_cost')" align="right" style="border-top: 1px solid var(--box-header-top-border-color) !important;">
                                        {{ Helper::formatCurrencyOutput($total_cost_for_asset) }}
                                    </x-data-row>

                                </div>
                            </x-well>

                            <x-well class="well-sm">
                                <div class="well-display">
                                    <x-data-row icon_type="maintenances" label="Active Maintenances" align="right">
                                        {{ $asset->maintenances->whereNull('completion_date')->count() }}
                                    </x-data-row>

                                    <x-data-row icon_type="checkout" :label="trans('general.checkouts_count')" align="right">
                                        {{ ($asset->checkouts) ? (int) $asset->checkouts->count() : '0' }}
                                    </x-data-row>

                                    <x-data-row icon_type="checkin" :label="trans('general.checkins_count')" align="right">
                                        {{ ($asset->checkins) ? (int) $asset->checkins->count() : '0' }}
                                    </x-data-row>

                                    <x-data-row icon_type="request" :label="trans('general.user_requests_count')" align="right">
                                        {{ ($asset->userRequests) ? (int) $asset->userRequests->count() : '0' }}
                                    </x-data-row>
                                </div>
                            </x-well>



                            @if (($snipeSettings->qr_code=='1') || $snipeSettings->label2_2d_type!='none')
                                <div class="col-md-12 text-center asset-qr-img" style="padding-top: 15px;">
                                    <img src="{{ config('app.url') }}/hardware/{{ $asset->id }}/qr_code" class="img-thumbnail" style="height: 150px; width: 150px; margin-right: 10px;" alt="QR code for {{ $asset->getDisplayNameAttribute() }}">
                                </div>
                            @endif

                        </x-page-column>
                        <!-- end side stats  column -->

                    </x-tabs.pane>

                    <x-tabs.pane name="licenses" :count="$asset->licenses->count()">
                        <x-table.licenses show_search="false" :route="route('api.assets.licenselist', $asset)" :presenter="\App\Presenters\LicensePresenter::dataTableLayoutSeatsCheckedOutToAssets()"/>
                    </x-tabs.pane>

                    <x-tabs.pane name="components" :count="$asset->components->sum('assigned_qty')">
                        <x-table.components :table_header="trans('general.components')" :presenter="\App\Presenters\ComponentPresenter::checkedOut()" :route="route('api.assets.assigned_components', $asset)"/>
                    </x-tabs.pane>

                    <x-tabs.pane name="assets" :count="$asset->assignedAssets()->AssetsForShow()->count()">
                        <x-table.assets :route="route('api.assets.index',['assigned_to' => $asset->id, 'assigned_type' => 'App\Models\Asset'])"/>
                    </x-tabs.pane>

                    <x-tabs.pane name="accessories" :count="$asset->assignedAccessories->count()">
                        <x-slot:table_header>
                            {{ trans('general.accessories_assigned') }}
                        </x-slot:table_header>

                        <x-table
                            name="assetAccessories"
                            buttons="accessoryButtons"
                            api_url="{{ route('api.assets.assigned_accessories', ['asset' => $asset]) }}"
                            :presenter="\App\Presenters\AssetPresenter::assignedAccessoriesDataTableLayout()"
                            export_filename="export-maintenances-{{ str_slug($asset->name) }}-{{ date('Y-m-d') }}"
                        />
                    </x-tabs.pane>


                    <!-- start maintenances tab pane -->
                    <x-tabs.pane name="maintenances">

                        <x-slot:table_header>
                            {{ trans('general.maintenances') }}
                        </x-slot:table_header>

                        <x-table
                            name="assetMaintenances"
                            buttons="maintenanceButtons"
                            api_url="{{ route('api.maintenances.index', array('asset_id' => $asset->id)) }}"
                            :presenter="\App\Presenters\MaintenancesPresenter::dataTableLayout()"
                            export_filename="export-maintenances-{{ str_slug($asset->name) }}-{{ date('Y-m-d') }}"
                        />
                    </x-tabs.pane>
                    <!-- end maintenances tab pane -->

                    <!-- start audits tab pane -->
                    <x-tabs.pane name="audits">
                        <x-table.history
                            :table_header="trans('general.audits')"
                            :model="$asset"
                            :route="route('api.activity.index', ['item_id' => $asset->id, 'item_type' => 'asset', 'action_type' => 'audit'])"
                            :hide_fields="['id','action_type', 'item', 'changed', 'target','quantity','changed','serial','signature_file','log_meta']"/>
                    </x-tabs.pane>
                    <!-- end audits tab pane -->

                    <!-- start notes tab pane -->
                    <x-tabs.pane name="notes">
                        <x-table.history
                            :table_header="trans('general.notes')"
                            :model="$asset" :route="route('api.activity.index', ['item_id' => $asset->id, 'item_type' => 'asset', 'action_type' => 'note added'])"
                            :hide_fields="['id','action_type', 'item', 'changed', 'target','file','file_download','quantity','changed','serial','signature_file','log_meta']"
                        />
                    </x-tabs.pane>
                    <!-- end audits tab pane -->


                    <x-tabs.pane name="files">
                        <x-table.files object_type="assets" :object="$asset"/>
                    </x-tabs.pane>

                    <x-tabs.pane name="model-files">
                        <x-table.files :table_header="trans('general.additional_files')" object_type="models" :object="$asset->model"/>
                    </x-tabs.pane>

                    <!-- start history tab pane -->
                    <x-tabs.pane name="history">
                        <x-table.history
                            :model="$asset"
                            :route="route('api.assets.history', $asset)"
                        />
                    </x-tabs.pane>
                    <!-- end history tab pane -->


                </x-slot:tabpanes>

            </x-tabs>

        </x-page-column>

        <x-page-column class="col-md-3">
            <x-box class="side-box expanded">
                <x-info-panel :infoPanelObj="$asset" img_path="{{ app('assets_upload_url') }}">
                    <x-slot:buttons>

                        @if (!$asset->assignedTo)
                        <x-button.checkout permission="checkout" :item="$asset" :route="route('hardware.checkout.create', $asset->id)"/>
                        @endif

                        @if (!$asset->hasOrphanedAssignment())
                            <x-button.checkin permission="checkin" :item="$asset" :route="route('hardware.checkin.create', $asset->id)"/>
                        @endif

                        <x-button.edit :item="$asset" :route="route('hardware.edit', $asset->id)"/>
                        <x-button.clone :item="$asset" :route="route('clone/hardware', $asset->id)"/>
                        <x-button.note :item="$asset" :route="route('clone/hardware', $asset->id)"/>
                        <x-button.audit :item="$asset" :route="route('asset.audit.create', $asset->id)"/>
                        <x-button.label :item="$asset" :route="route('hardware.bulkedit.show')"/>
                        <x-button.delete :item="$asset"/>
                        <x-button.restore :item="$asset" :route="route('restore/hardware', ['asset' => $asset->id])"/>
                    </x-slot:buttons>
                </x-info-panel>
            </x-box>

        </x-page-column>

    </x-container>


    @section('moar_scripts')
        @can('files', $asset)
            @include ('modals.upload-file', ['item_type' => 'asset', 'item_id' => $asset->id])
        @endcan
        @can('update', $asset)
        @include ('modals.add-note', ['type' => 'asset', 'id' => $asset->id])
    @endcan
        @include ('partials.bootstrap-table')
    @endsection

@stop
