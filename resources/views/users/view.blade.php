@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/users/general.view_user', ['name' => $user->display_name]) }}
@parent
@stop

@section('header_right')
    <x-button.info-panel-toggle hide-on-xs/>
@endsection

{{-- Page content --}}
@section('content')

    <x-container columns="2">

        @if ($user->deleted_at!='')
            <div class="col-md-12">
                <x-callout type="warning" icon="warning" live="assertive">
                    {{ trans('admin/users/message.user_deleted_warning') }}
                </x-callout>
            </div>
        @endif

        <x-page-column class="col-md-9 main-panel">

            <x-tabs>
                <x-slot:tabnav>
                    <x-tabs.details-tab/>
                    <x-tabs.asset-tab count="{{ $user->assets()->AssetsForShow()->count() }}"/>
                    <x-tabs.license-tab count="{{ $user->licenses()->count() }}"/>
                    <x-tabs.accessory-tab count="{{ $user->accessories()->count() }}"/>
                    <x-tabs.consumable-tab count="{{ $user->consumables()->count() }}"/>
                    <x-tabs.maintenance-tab count="{{ $user->assignedMaintenances()->count() }}"/>
                    <x-tabs.files-tab :item="$user" count="{{ $user->uploads()->count() }}"/>
                    <x-tabs.eula-tab count="{{ $user->eulas()->count() }}"/>
                    <x-tabs.location-tab count="{{ $user->managedLocations()->count() }}"/>
                    <x-tabs.user-tab count="{{ $user->managesUsers()->count() }}" name="managed-users" icon_type="manager" :label="trans('admin/users/table.managed_users')"/>
                    <x-tabs.history-tab count="{{ $user->history->count() }}" :model="$user"/>
                    <x-tabs.upload-tab :item="$user"/>
                </x-slot:tabnav>


                <x-slot:tabpanes>

                    <!-- start details tab content -->
                    <x-tabs.pane name="details">

                        <!-- this just adds a little top space -->
                        <div class="clearfix visible-lg-block" style="padding: 6px;"></div>

                        <!-- well column -->
                        <x-page-column class="col-md-4">
                            <x-well>
                                <x-icon type="calendar" class="fa-fw"/>
                                <strong>{{ trans('general.last_login') }}</strong>
                                @if ($user->last_login != '')
                                    {{ Helper::getFormattedDateObject($user->last_login, 'datetime', false) }}
                                    <span class="text-muted">{{ Carbon::parse($user->last_login)->diffForHumans(['parts' => 2]) }}</span>
                                @else
                                    {{ trans('general.na') }}
                                @endif
                            </x-well>
                        </x-page-column>
                        <!--  ./ well column -->

                        <!-- well column -->
                        <x-page-column class="col-md-4">
                            <x-well>
                                <x-icon type="start_date" class="fa-fw"/>
                                <strong>{{ trans('general.start_date') }}</strong>
                                @if ($user->start_date != '')
                                    {{ Helper::getFormattedDateObject($user->start_date, 'date', false) }}
                                    <span class="text-muted">{{ Carbon::parse($user->start_date)->diffForHumans(['parts' => 2]) }}</span>
                                @else
                                    {{ trans('general.na') }}
                                @endif
                            </x-well>
                        </x-page-column>
                        <!-- ./ well column -->

                        <!-- well column -->
                        <x-page-column class="col-md-4">
                            <x-well>
                                <x-icon type="end_date" class="fa-fw {{ (($user->end_date!='' && $user->end_date < Carbon::now()) ? ' text-danger' : '') }}"/>
                                <strong>{{ trans('general.end_date') }}</strong>
                                @if ($user->end_date != '')
                                    {{ Helper::getFormattedDateObject($user->end_date, 'date', false) }}
                                    <span class="text-muted">{{ Carbon::parse($user->end_date)->diffForHumans(['parts' => 2]) }}</span>
                                @else
                                    {{ trans('general.na') }}
                                @endif
                            </x-well>

                        </x-page-column>
                        <!-- ./ well column -->

                        <!-- set clearfix for responsive design -->
                        <div class="clearfix"></div>

                        <!-- definition list column -->
                        <x-page-column class="col-md-8 col-sm-12">

                            <!-- definition list content -->
                            <x-page-data>

                                <x-data-row :label="trans('general.name')" copy_what="name">
                                    {{ $user->first_name }} {{ $user->last_name }}
                                </x-data-row>

                                <x-data-row :label="trans('admin/users/table.display_name')" copy_what="display_name">
                                    {{ $user->getRawOriginal('display_name') }}
                                </x-data-row>

                                <x-data-row :label="trans('general.username')" copy_what="username">
                                    @if ($user->isSuperUser())
                                        <span class="label label-danger" data-tooltip="true" title="{{ trans('general.superuser_tooltip') }}"><x-icon type="superadmin" style="padding-right: 5px;"/>{{ $user->username }}</span>
                                    @elseif ($user->hasAccess('admin'))
                                        <span class="label label-warning" data-tooltip="true" title="{{ trans('general.admin_tooltip') }}"><x-icon type="superadmin" style="padding-right: 5px;"/>{{ $user->username }}</span>
                                    @else
                                        {{ $user->username }}
                                    @endif
                                </x-data-row>

                                <x-data-row :label="trans('admin/users/table.employee_num')" copy_what="employee_num">
                                    {{ $user->employee_num }}
                                </x-data-row>

                                <x-data-row :label="trans('admin/users/table.job')" copy_what="jobtitle">
                                    {{ $user->jobtitle }}
                                </x-data-row>

                                <x-data-row :label="trans('general.groups')" copy_what="groups">
                                    @if ($user->groups->count() > 0)
                                        @foreach ($user->groups as $group)
                                            @can('superadmin')
                                                <a href="{{ route('groups.show', $group->id) }}" class="label label-default">{{ $group->name }}</a>
                                            @else
                                                <span class="label label-default">{{ $group->name }}</span>
                                            @endcan
                                        @endforeach
                                    @endif

                                    @if ($user->hasIndividualPermissions())
                                        <span class="text-warning"><x-icon type="warning"/> {{ trans('admin/users/general.individual_override') }}</span>
                                    @endif
                                </x-data-row>

                                <x-data-row :label="trans('general.permissions')">
                                    @if ($user->isSuperUser())
                                        <span class="label label-danger" data-tooltip="true" title="{{ trans('general.superuser_tooltip') }}">
                                            <x-icon type="superadmin" style="padding-right: 5px;"/>{{ trans('general.superuser') }}
                                        </span>
                                    @elseif ($user->hasAccess('admin'))
                                        <span class="label label-warning" data-tooltip="true" title="{{ trans('general.admin_tooltip') }}">
                                            <x-icon type="superadmin" style="padding-right: 5px;"/>{{ trans('general.admin_user') }}
                                        </span>
                                    @elseif (!empty($effectivePermissionsBySection))
                                        @foreach ($effectivePermissionsBySection as $section => $permissions)
                                            @foreach ($permissions as $permission)
                                                @if (($permission['status'] ?? 'allowed') === 'denied')
                                                    <span class="label label-danger denied-permission" data-tooltip="true" title="{{ $permission['source_label'] }}"><x-icon type="x" class="fa-fw"/> {{ $permission['permission'] }}</span>
                                                @else
                                                    <span class="label label-success" data-tooltip="true" title="{{ $permission['source_label'] }}"><x-icon type="checkmark" class="fa-fw"/> {{ $permission['permission'] }}</span>
                                                @endif
                                            @endforeach

                                        @endforeach
                                    @endif
                                </x-data-row>

                            @if (($user->email!='') && ($user->activated=='1')  && ($user->getAssignedItemsWithPendingAcceptance()->count() > 0))

                                    <x-data-row :label="trans_choice('admin/users/general.unaccepted_items', $user->getAssignedItemsWithPendingAcceptance()->count())">
                                        <form action="{{ route('users.acceptance_reminder', $user) }}" method="POST" class="form-inline" style="display: inline;">
                                            {{ csrf_field() }}
                                            <button class="btn btn-warning btn-sm" type="submit">
                                                {{ trans('admin/users/general.send_acceptance_reminder') }}
                                            </button>
                                        </form>
                                    </x-data-row>
                                @endif

                            </x-page-data>

                            <!-- ./ definition list column -->

                        </x-page-column>
                        <!-- ./ definition list content -->

                        <!-- begin side stats well column-->
                        <x-page-column class="col-md-4 col-sm-12">


                            @if ($user->getUserTotalCost()->total_user_cost > 0)
                                <x-well class="well-sm">

                                    <div class="well-display">

                                        <x-data-row icon_type="asset" label="{{ trans('general.assets') }}" align="right">
                                            {{ Helper::formatCurrencyOutput($user->getUserTotalCost()->asset_cost) }}
                                        </x-data-row>

                                        <x-data-row icon_type="licenses" label="{{ trans('general.licenses') }}" align="right">
                                            {{ Helper::formatCurrencyOutput($user->getUserTotalCost()->license_cost)}}
                                        </x-data-row>

                                        <x-data-row icon_type="accessories" label="{{ trans('general.accessories') }}" align="right">
                                            {{ Helper::formatCurrencyOutput($user->getUserTotalCost()->accessory_cost)}}
                                        </x-data-row>

                                        {{-- Sum across complete + active maintenances tied
                                             to this user — used to surface "which users
                                             cost the most maintenance over time" as a glance.
                                             The current-active count lives on the tab badge
                                             above so we don't duplicate it here. --}}
                                        <x-data-row icon_type="maintenances" :label="trans('general.maintenance_cost')" align="right">
                                            {{ Helper::formatCurrencyOutput($user->getUserTotalCost()->maintenance_cost) }}
                                        </x-data-row>

                                        <x-data-row icon_type="cost" label=" {{ trans('admin/users/table.total_assets_cost') }}" align="right">
                                            {{ Helper::formatCurrencyOutput($user->getUserTotalCost()->total_user_cost) }}
                                        </x-data-row>

                                    </div>

                                </x-well>
                            @endif


                            <x-well class="well-sm" style="padding-left: 15px;">

                                @if($user->activated == '1')
                                    <x-icon type="checkmark" class="fa-fw text-success"/>
                                    {{ trans('general.login_enabled') }}

                                    <br>
                                    @if ($user->two_factor_active())
                                        <x-icon type="checkmark" class="fa-fw text-success"/>
                                    @else
                                        <x-icon type="x" class="fa-fw text-danger"/>
                                    @endif
                                    {{ trans('admin/users/general.two_factor_active') }}

                                    <br>
                                    @if ($user->two_factor_active_and_enrolled())
                                        <x-icon type="checkmark" class="fa-fw text-success"/>
                                    @else
                                        <x-icon type="x" class="fa-fw text-danger"/>
                                    @endif
                                    {{ trans('admin/users/general.two_factor_enrolled') }}

                                @else
                                    <x-icon type="x" class="fa-fw text-danger"/>
                                    {{ trans('general.login_enabled') }}
                                @endif

                                <br>

                                @if($user->vip == '1')
                                    <x-icon type="vip" class="fa-fw text-warning"/>
                                    {{ trans('admin/users/general.vip_label') }}
                                    <br>
                                @endif

                                <x-icon type="api-key" class="fa-fw"/>
                                {{ $user->tokens()->count() }} API Tokens
                                <br>

                                @if($user->remote == '1')
                                    <x-icon type="remote" class="fa-fw"/>
                                    {{ trans('admin/users/general.remote') }}
                                    <br>
                                @endif

                                @if($user->ldap_import == '1')
                                    <x-icon type="ldap" class="fa-fw"/>
                                    {{ trans('admin/settings/general.ldap_enabled') }}
                                    <br>
                                @endif

                                @if ($user->autoassign_licenses == '1')
                                    <x-icon type="checkmark" class="fa-fw text-success"/>
                                @else
                                    <x-icon type="x" class="fa-fw text-danger"/>
                                @endif
                                {{ trans('general.autoassign_licenses') }}
                                <br>

                            </x-well>


                                <!-- Impersonation button -->
                                @if (Auth::check() && Auth::user()->mayImpersonate($user))
                                    <button type="button" class="btn btn-danger hidden-print btn-social btn-block" data-toggle="modal" data-target="#confirmImpersonateModal" data-tooltip="true" data-title="{{ trans('admin/users/general.impersonate_user', ['name' => $user->display_name]) }}">
                                        <x-icon type="impersonate" class="fa-fw" style="font-size: 17px;"/>
                                        {{ trans('general.impersonate') }}
                                        <span class="sr-only">{{ trans('admin/users/general.impersonate_user', ['name' => $user->display_name]) }}</span>
                                    </button>
                                @endif



                                @if (auth()->user()->isSuperUser() && $user->twoFactorResettable())
                                    <!-- 2FA reset -->
                                    <button type="button" class="btn btn-theme hidden-print btn-social btn-block" data-toggle="modal" data-target="#confirmTwoFactorResetModal" style="margin-right: 10px; margin-top: 10px;">
                                        <x-icon type="mobile" class="fa-fw"/>
                                        {{ trans('admin/settings/general.two_factor_reset') }}
                                    </button>
                                    <br>
                                    <p class="help-block" style="line-height: 1.6;">
                                        {{ trans('admin/settings/general.two_factor_reset_help') }}
                                    </p>
                                @endif



                        </x-page-column>
                        <!-- end side stats well column-->

                    </x-tabs.pane>

                    <x-tabs.pane name="licenses" :count="$user->licenses()->count()">

                        @can('checkin', \App\Models\License::class)
                        <x-slot:table_header>{{ trans('general.licenses') }}</x-slot:table_header>
                        <x-slot:bulkactions>
                            <div class="hidden-print" style="padding-top:10px; min-width:400px;">
                                <form method="POST" action="{{ route('licenses.bulkcheckin.selected') }}" id="userLicenseBulkCheckinForm" class="form-inline">
                                    @csrf
                                    <label for="userLicenseBulkActions"><span class="sr-only">{{ trans('button.bulk_actions') }}</span></label>
                                    <select name="bulk_actions" id="userLicenseBulkActions" class="form-control select2" style="min-width:350px;">
                                        <option value="checkin">{{ trans('general.checkin') }}</option>
                                    </select>
                                    <button type="submit" id="userLicenseBulkCheckinButton" class="btn btn-theme" disabled>{{ trans('button.go') }}</button>
                                    <span id="userLicenseBulkCheckinCount" style="display:none; margin-left:8px; line-height:34px;">&mdash; <span class="badge">0</span> {{ trans('general.selected') }}</span>
                                </form>
                            </div>
                        </x-slot:bulkactions>
                        @endcan

                        @can('view', \App\Models\License::class)
                        <table
                            data-cookie-id-table="userLicenseTable"
                            data-id-table="userLicenseTable"
                            id="userLicenseTable"
                            data-buttons="licenseButtons"
                            data-side-pagination="client"
                            data-show-footer="true"
                            data-sort-name="name"
                            class="table table-striped snipe-table table-hover"
                            data-export-options='{
                    "fileName": "export-license-{{ str_slug($user->username) }}-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","delete","download","icon"]
                    }'>

                            <thead>
                                <tr>
                                    @can('checkin', \App\Models\License::class)
                                    <th scope="col" class="hidden-print"><input type="checkbox" id="userLicenseSelectAll"></th>
                                    @endcan
                                    <th scope="col">{{ trans('general.name') }}</th>
                                    <th scope="col">{{ trans('admin/licenses/form.license_key') }}</th>
                                    <th scope="col" data-footer-formatter="sumFormatter" data-fieldname="purchase_cost">{{ trans('general.purchase_cost') }}</th>
                                    <th scope="col">{{ trans('admin/licenses/form.purchase_order') }}</th>
                                    <th scope="col">{{ trans('general.order_number') }}</th>
                                    <th scope="col" class="col-md-1 hidden-print">{{ trans('general.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($user->licenses as $license)
                                    <tr>
                                        @can('checkin', \App\Models\License::class)
                                        <td class="hidden-print">
                                            <input type="checkbox" class="user-license-seat-checkbox hidden-print" form="userLicenseBulkCheckinForm" name="ids[]" value="{{ $license->pivot->id }}">
                                        </td>
                                        @endcan
                                        <td class="col-md-4">
                                            {!! $license->present()->nameUrl() !!}
                                        </td>
                                        <td class="col-md-4">
                                            @can('viewKeys', $license)
                                                <code class="single-line"><span class="js-copy-link" data-clipboard-target=".js-copy-key-{{ $license->id }}" aria-hidden="true" data-tooltip="true" data-placement="top" title="{{ trans('general.copy_to_clipboard') }}"><span class="js-copy-key-{{ $license->id }}">{{ $license->serial }}</span></span></code>
                                            @else
                                                ------------
                                            @endcan
                                        </td>
                                        <td class="col-md-2">
                                            {{ Helper::formatCurrencyOutput($license->purchase_cost) }}
                                        </td>
                                        <td>
                                            {{ $license->purchase_order }}
                                        </td>
                                        <td>
                                            {{ $license->order_number }}
                                        </td>
                                        <td class="hidden-print col-md-2">
                                            @can('update', $license)
                                                <a href="{{ route('licenses.checkin', $license->pivot->id, ['backto'=>'user']) }}" class="btn bg-purple btn-sm hidden-print">{{ trans('general.checkin') }}</a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endcan

                    </x-tabs.pane>

                    <x-tabs.pane name="assets" :count="$user->assets()->AssetsForShow()->count()">
                        <x-table.assets :route="route('api.assets.index',['assigned_to' => e($user->id), 'assigned_type' => 'App\Models\User'])"/>
                    </x-tabs.pane>

                    <x-tabs.pane name="accessories" :count="$user->accessories()->count()">
                        <x-slot:table_header>
                            {{ trans('general.accessories_assigned') }}
                        </x-slot:table_header>

                        @can('view', \App\Models\Accessory::class)
                        <table
                            data-cookie-id-table="userAccessoryTable"
                            data-id-table="userAccessoryTable"
                            id="userAccessoryTable"
                            data-buttons="accessoryButtons"
                            data-side-pagination="client"
                            data-sort-name="name"
                            class="table table-striped snipe-table table-hover"
                            data-export-options='{
                    "fileName": "export-accessory-{{ str_slug($user->username) }}-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","delete","download","icon"]
                    }'>
                            <thead>
                                <tr>
                                    <th scope="col">{{ trans('general.id') }}</th>
                                    <th scope="col">{{ trans('general.name') }}</th>
                                    <th scope="col">{{ trans('general.date') }}</th>
                                    <th scope="col" data-fieldname="note">{{ trans('general.notes') }}</th>
                                    <th scope="col" data-footer-formatter="sumFormatter" data-fieldname="purchase_cost">{{ trans('general.unit_cost') }}</th>
                                    <th scope="col" class="hidden-print">{{ trans('general.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($user->accessories as $accessory)
                                    <tr>
                                        <td>{{ $accessory->pivot->id }}</td>
                                        <td>{!! $accessory->present()->nameUrl() !!}</td>
                                        <td>{{ Helper::getFormattedDateObject($accessory->pivot->created_at, 'datetime',  false) }}</td>
                                        <td>{{ $accessory->pivot->note }}</td>
                                        <td>
                                            {!! Helper::formatCurrencyOutput($accessory->purchase_cost) !!}
                                        </td>
                                        <td class="hidden-print">
                                            @can('checkin', $accessory)
                                                <a href="{{ route('accessories.checkin.show', array('accessoryID'=> $accessory->pivot->id, 'backto'=>'user')) }}" class="btn bg-purple btn-sm hidden-print">{{ trans('general.checkin') }}</a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endcan

                    </x-tabs.pane>

                    <x-tabs.pane name="consumables" :count="$user->consumables()->count()">
                        @can('view', \App\Models\Consumable::class)
                        <table
                            data-cookie-id-table="userConsumableTable"
                            data-id-table="userConsumableTable"
                            id="userConsumableTable"
                            data-buttons="consumableButtons"
                            data-side-pagination="client"
                            data-show-footer="true"
                            data-sort-name="name"
                            class="table table-striped snipe-table table-hover"
                            data-export-options='{
                    "fileName": "export-consumable-{{ str_slug($user->username) }}-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","delete","download","icon"]
                    }'>
                            <thead>
                                <tr>
                                    <th scope="col" class="col-md-3">{{ trans('general.name') }}</th>
                                    <th scope="col" class="col-md-2" data-footer-formatter="sumFormatter" data-fieldname="purchase_cost">{{ trans('general.unit_cost') }}</th>
                                    <th scope="col" class="col-md-2">{{ trans('general.date') }}</th>
                                    <th scope="col" class="col-md-5">{{ trans('general.notes') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($user->consumables as $consumable)
                                    <tr>
                                        <td>{!! $consumable->present()->nameUrl() !!}</td>
                                        <td>
                                            {!! Helper::formatCurrencyOutput($consumable->purchase_cost) !!}
                                        </td>
                                        <td>{{ Helper::getFormattedDateObject($consumable->pivot->created_at, 'datetime',  false) }}</td>
                                        <td>{{ $consumable->pivot->note }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endcan

                    </x-tabs.pane>

                    {{-- Maintenances tab: every maintenance whose underlying
                         asset was checked out to this user. Shows open and
                         closed; the tab badge only counts the open ones
                         (see assignedMaintenances()->active() above). --}}
                    <x-tabs.pane name="maintenances" :count="$user->assignedMaintenances()->count()">
                        <x-table.maintenances
                            :route="route('api.maintenances.index', ['checked_out_to_id' => $user->id, 'checked_out_to_type' => \App\Models\User::class])"
                            export_filename="export-maintenances-{{ str_slug($user->username) }}-{{ date('Y-m-d') }}"
                        />
                    </x-tabs.pane>

                    <x-tabs.pane name="managed-users" :count="$user->managesUsers()->count()">
                        @include('partials.users-bulk-actions')

                        <table
                            data-columns="{{ \App\Presenters\UserPresenter::dataTableLayout() }}"
                            data-cookie-id-table="managedUsersTable"
                            data-id-table="managedUsersTable"
                            data-toolbar="#usersBulkEditToolbar"
                            data-bulk-button-id="#bulkUserEditButton"
                            data-bulk-form-id="#usersBulkForm"
                            data-side-pagination="server"
                            id="managedUsersTable"
                            data-buttons="userButtons"
                            class="table table-striped snipe-table"
                            data-url="{{ route('api.users.index', ['manager_id' => $user->id]) }}"
                            data-export-options='{
              "fileName": "export-users-{{ date('Y-m-d') }}",
              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
              }'>
                        </table>
                    </x-tabs.pane>


                    <x-tabs.pane name="files" :count="$user->uploads()->count()">
                        <x-table.files object_type="users" :object="$user"/>
                    </x-tabs.pane>

                    <x-tabs.pane name="eulas" :count="$user->eulas()->count()">
                        <x-slot:table_header>
                            {{ trans('general.eula') }}
                        </x-slot:table_header>

                        <table
                            data-toolbar="#userEULAToolbar"
                            data-cookie-id-table="userEULATable"
                            data-id-table="userEULATable"
                            id="userEULATable"
                            data-side-pagination="client"
                            data-show-footer="true"
                            data-show-refresh="false"
                            data-sort-order="asc"
                            data-sort-name="name"
                            class="table table-striped snipe-table table-hover"
                            data-url="{{ route('api.user.eulas', $user) }}"
                            data-export-options='{
                    "fileName": "export-eula-{{ str_slug($user->username) }}-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","delete","purchasecost", "icon"]
                    }'>
                            <thead>
                                <tr>
                                    <th scope="col" data-visible="true" data-field="icon" style="width: 40px;" class="hidden-xs" data-formatter="iconFormatter">{{ trans('admin/hardware/table.icon') }}</th>
                                    <th scope="col" data-visible="true" data-field="item.name">{{ trans('general.item') }}</th>
                                    <th scope="col" data-visible="true" data-field="created_at" data-sortable="true" data-formatter="dateDisplayFormatter">{{ trans('general.accepted_date') }}</th>
                                    <th scope="col" data-field="note">{{ trans('general.notes') }}</th>
                                    <th scope="col" data-field="url" data-formatter="fileDownloadButtonsFormatter">{{ trans('general.download') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </x-tabs.pane>


                    <!-- start locations tab pane -->
                    <x-tabs.pane name="locations">

                        <x-slot:table_header>
                            {{ trans('general.locations') }}
                        </x-slot:table_header>

                        <x-table
                            name="userLocations_{{ $user->id }}"
                            api_url="{{ route('api.locations.index', ['manager_id' => $user->id]) }}"
                            :presenter="\App\Presenters\LocationPresenter::dataTableLayout()"
                            export_filename="export-history-{{ str_slug($user->name) }}-{{ date('Y-m-d') }}"
                        />

                    </x-tabs.pane>


                    <!-- start history tab pane -->
                    <x-tabs.pane name="history">
                        <x-table.history :model="$user" :route="route('api.users.history', $user)"/>
                    </x-tabs.pane>
                    <!-- end history tab pane -->
                </x-slot:tabpanes>
            </x-tabs>
        </x-page-column>

        <x-page-column class="col-md-3">
            <x-box class="side-box expanded">
                <x-info-panel :infoPanelObj="$user" img_path="{{ app('users_upload_url') }}">
                    <x-slot:buttons>
                        <x-button.edit :item="$user" :route="route('users.edit', $user->id)"/>
                        <x-button.clone :item="$user" :route="route('users.clone.show', $user)"/>
                        <x-button.restore :item="$user" :route="route('users.restore.store',  $user)"/>


                    @if($user->allAssignedCount() != '0')
                        <a href="{{ route('users.print', $user->id) }}" class="btn btn-sm btn-theme hidden-print" target="_blank" rel="noopener" data-tooltip="true" data-title="{{ trans('admin/users/general.print_assigned') }}">
                            <x-icon type="print" class="fa-fw"/>
                        </a>
                        @endif

                        @can('checkout', \App\Models\Asset::class)
                            @if (($user->assets()->whereNull('deleted_at')->count() + $user->accessories()->count() + $user->licenses()->count()) > 0)
                                <a href="{{ route('users.transfer.show', $user) }}" class="btn btn-sm btn-theme hidden-print" data-tooltip="true" data-title="{{ trans('admin/users/general.transfer.button_tooltip') }}">
                                    <x-icon type="transfer" class="fa-fw"/>
                                </a>
                            @endif
                        @endcan


                        @if(!empty($user->email) && ($user->allAssignedCount() != '0'))
                            <form class="form-inline" style="display: inline" action="{{ route('users.email',['userId'=> $user->id]) }}" method="POST">
                                {{ csrf_field() }}
                                <button class="btn btn-sm btn-theme hidden-print" rel="noopener" data-tooltip="true" data-title="{{ trans('admin/users/general.email_assigned') }}">
                                    <x-icon type="email" class="fa-fw"/>
                                </button>
                            </form>
                        @endif

                        @if (!empty($user->email) && ($user->activated=='1'))
                            <form action="{{ route('users.password',['userId'=> $user->id]) }}" method="POST" class="form-inline" style="display: inline">
                                {{ csrf_field() }}
                                <button class="btn btn-sm btn-primary hidden-print" data-tooltip="true" data-title="{{ trans('button.send_password_link') }}">
                                    <x-icon type="password" class="fa-fw"/>
                                </button>
                            </form>
                        @else
                            <button class="btn btn-sm btn-primary hidden-print" rel="noopener" disabled title="{{ trans('admin/users/message.user_has_no_email') }}">
                                <x-icon class="fa-solid fa-key"/>
                            </button>
                        @endif

                        <x-button.delete :item="$user"/>

                        @can('delete', $user)
                            <form action="{{ route('users/bulkedit') }}" method="POST" class="form-inline" style="display: inline; padding-right: 5px;">
                                <!-- CSRF Token -->
                                <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                                <input type="hidden" name="bulk_actions" value="delete"/>
                                <input type="hidden" name="ids[{{ $user->id }}]" value="{{ $user->id }}"/>
                                <button class="btn btn-sm btn-danger hidden-print pull-right" style="margin-right: 3px;" data-tooltip="true" data-title="{{ trans('tooltips.checkin_all.user') }}">
                                    <x-icon type="checkin-and-delete" class="fa-fw"/>
                                </button>
                            </form>
                        @endcan
                    </x-slot:buttons>
                </x-info-panel>
            </x-box>

        </x-page-column>
    </x-container>

    @if (Auth::check() && Auth::user()->mayImpersonate($user))
        @include('users.impersonate-confirm-modal')
    @endif

    @if (auth()->user()->isSuperUser() && $user->twoFactorResettable())
        @include('modals.confirm-action', [
            'modal_name' => 'confirmTwoFactorResetModal',
            'route' => route('users.two_factor_reset', $user->id),
            'title' => trans('admin/settings/general.two_factor_reset'),
            'body' => trans('admin/settings/general.two_factor_reset_confirm', ['name' => $user->display_name]),
        ])
    @endif

@endsection


@section('moar_scripts')
    @can('files', $user)
        @include ('modals.upload-file', ['item_type' => 'users', 'item_id' => $user->id])
    @endcan

    @include ('partials.bootstrap-table', ['simple_view' => true])
<script nonce="{{ csrf_token() }}">
$(function () {

    $("#optional_info").on("click",function(){
        $('#optional_details').fadeToggle(100);
        $('#optional_info_icon').toggleClass('fa-caret-right fa-caret-down');
        var optional_info_open = $('#optional_info_icon').hasClass('fa-caret-down');
        document.cookie = "optional_info_open="+optional_info_open+'; path=/';
    });

    $(document).on('change', '.user-license-seat-checkbox', function () {
        var count = $('.user-license-seat-checkbox:checked').length;
        $('#userLicenseBulkCheckinButton').prop('disabled', count === 0);
        $('#userLicenseBulkCheckinCount .badge').text(count);
        if (count > 0) {
            $('#userLicenseBulkCheckinCount').show();
        } else {
            $('#userLicenseBulkCheckinCount').hide();
        }
        var total = $('.user-license-seat-checkbox').length;
        $('#userLicenseSelectAll').prop('indeterminate', count > 0 && count < total);
        $('#userLicenseSelectAll').prop('checked', count === total);
    });

    $(document).on('change', '#userLicenseSelectAll', function () {
        $('.user-license-seat-checkbox').prop('checked', $(this).is(':checked')).trigger('change');
    });
});
</script>

@endsection
