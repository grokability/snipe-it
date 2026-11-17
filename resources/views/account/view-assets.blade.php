@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.hello_name', array('name' => auth()->user()->display_name)) }}
@parent
@stop

{{-- Account page content --}}
@section('content')

    @if (!request()->filled('user_id') || auth()->user()->id == $user->id)
        @if ($acceptanceQuantity = \App\Models\CheckoutAcceptance::forUser(Auth::user())->pending()->sum('qty'))
          <div class="row">
            <div class="col-md-12">
              <x-alert type="warning" icon="warning">
                <strong>
                  <a href="{{ route('account.accept') }}" style="color: white;">
                    {{ trans_choice('general.unaccepted_profile_warning', $acceptanceQuantity, ['count' => $acceptanceQuantity]) }}
                  </a>
                </strong>
              </x-alert>
            </div>
          </div>
        @endif
    @endif

{{-- Manager View Dropdown --}}
@if (isset($settings) && $settings->manager_view_enabled && isset($subordinates) && $subordinates->count() > 1)
  <div class="row hidden-print" style="margin-bottom: 15px;">
    <div class="col-md-12">
      <form method="GET" action="{{ route('view-assets') }}" class="pull-right" role="form">
        <div class="form-group" style="margin-bottom: 0;">
          <label for="user_id" class="control-label" style="margin-right: 10px;">
            {{ trans('general.view_user_assets') }}:
          </label>
          <select name="user_id" id="user_id" class="form-control select2" onchange="this.form.submit()" style="width: 250px; display: inline-block;">
            @foreach ($subordinates as $subordinate)
              <option value="{{ $subordinate->id }}" {{ (int)$selectedUserId === (int)$subordinate->id ? ' selected' : '' }}>
                {{ $subordinate->display_name }}
                @if ($subordinate->id == auth()->id())
                  ({{ trans('general.me') }})
                @endif
              </option>
            @endforeach
          </select>
        </div>
      </form>
    </div>
  </div>
@endif

  <div class="row">
    <div class="col-md-12">
      <div class="nav-tabs-custom">
        <ul class="nav nav-tabs hidden-print">

          <li class="active">
            <a href="#details" data-toggle="tab" data-breadcrumb-label="{{ trans('admin/users/general.info') }}">
              <span class="hidden-lg hidden-md" aria-hidden="true">
              <i class="fas fa-info-circle fa-2x"></i>
              </span>
              <span class="hidden-xs hidden-sm">
                {{ trans('admin/users/general.info') }}
              </span>
            </a>
          </li>

          <li>
            <a href="#assets" data-toggle="tab" data-breadcrumb-label="{{ trans('general.assets') }}">
              <span class="hidden-lg hidden-md" aria-hidden="true">
                <x-icon type="assets" class="fa-2x" />
              </span>
              <span class="hidden-xs hidden-sm">
                {{ trans('general.assets') }}
                {!! ($user->assets()->AssetsForShow()->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($user->assets()->AssetsForShow()->count()).'</span>' : '' !!}
            </span>
            </a>
          </li>

          <li>
            <a href="#licenses" data-toggle="tab" data-breadcrumb-label="{{ trans('general.licenses') }}">
            <span class="hidden-lg hidden-md">
            <i class="far fa-save fa-2x"></i>
            </span>
              <span class="hidden-xs hidden-sm">{{ trans('general.licenses') }}
                {!! ($user->licenses->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($user->licenses->count()).'</span>' : '' !!}
            </span>
            </a>
          </li>

          <li>
            <a href="#accessories" data-toggle="tab" data-breadcrumb-label="{{ trans('general.accessories') }}">
            <span class="hidden-lg hidden-md">
            <x-icon type="accessories" class="fa-2x" />
            </span>
              <span class="hidden-xs hidden-sm">{{ trans('general.accessories') }}
                {!! ($user->accessories->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($user->accessories->count()).'</span>' : '' !!}
            </span>
            </a>
          </li>

          <li>
            <a href="#consumables" data-toggle="tab" data-breadcrumb-label="{{ trans('general.consumables') }}">
            <span class="hidden-lg hidden-md" aria-hidden="true">
                <x-icon type="consumables" class="fa-2x" />
              </span>
              <span class="hidden-xs hidden-sm">{{ trans('general.consumables') }}
                {!! ($user->consumables->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($user->consumables->count()).'</span>' : '' !!}
            </span>
            </a>
          </li>

          <li>
            <a href="#eulas" data-toggle="tab" data-breadcrumb-label="{{ trans('general.eula') }}">
            <span class="hidden-lg hidden-md" aria-hidden="true">
                <x-icon type="files" class="fa-2x" />
              </span>
              <span class="hidden-xs hidden-sm">{{ trans('general.eula') }}
                {!! ($user->eulas->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($user->eulas->count()).'</span>' : '' !!}
            </span>
            </a>
          </li>

        </ul>

        <div class="tab-content">
          <div class="tab-pane active" id="details">
            <div class="row">


              <!-- Start button column -->
                <div class="info-stack-container">
                    <!-- Start button column -->
                    <div class="col-md-3 col-xs-12 col-sm-push-9 info-stack">

                            <div class="col-md-12 text-center">
                              <img src="{{ $user->present()->gravatar() }}" class="img-thumbnail hidden-print" style="margin-bottom: 20px;" referrerpolicy="no-referrer" alt="{{ $user->display_name }}">
                            </div>

                              @if (!request()->filled('user_id') || auth()->user()->id == $user->id)
                                  <div class="col-md-12">
                                    <a href="{{ route('profile') }}" style="width: 100%;" class="btn btn-sm btn-warning btn-social btn-block hidden-print">
                                      <x-icon type="edit" />
                                      {{ trans('general.editprofile') }}
                                    </a>
                                  </div>


                                  @can('self.profile')
                                  @if (Auth::user()->ldap_import!='1')
                                <div class="col-md-12" style="padding-top: 5px;">
                                  <a href="{{ route('account.password.index') }}" style="width: 100%;" class="btn btn-sm btn-theme btn-social btn-block hidden-print" rel="noopener">
                                    <x-icon type="password" class="fa-fw" />
                                    {{ trans('general.changepassword') }}
                                  </a>
                                </div>
                                @endif
                              @endcan

                            @can('self.api')
                            <div class="col-md-12" style="padding-top: 5px;">
                              <a href="{{ route('user.api') }}" style="width: 100%;" class="btn btn-sm btn-theme btn-social btn-block hidden-print" rel="noopener">
                                <x-icon type="api-key" class="fa-fw" />
                                {{ trans('general.manage_api_keys') }}
                              </a>
                            </div>
                            @endcan
                            @endif

                              <div class="col-md-12" style="padding-top: 5px;">
                                <a href="{{ route('profile.print') }}" style="width: 100%;" class="btn btn-sm btn-theme btn-social btn-block hidden-print" target="_blank" rel="noopener">
                                  <x-icon type="print" class="fa-fw" />
                                  {{ trans('admin/users/general.print_assigned') }}
                                </a>
                              </div>


                              <div class="col-md-12" style="padding-top: 5px;">
                                @if (!empty($user->email))
                                  <form action="{{ route('profile.email_assets') }}" method="POST">
                                    {{ csrf_field() }}
                                    <button style="width: 100%;" class="btn btn-sm btn-theme btn-social btn-block hidden-print" rel="noopener">
                                      <x-icon type="email" class="fa-fw" />
                                      {{ trans('admin/users/general.email_assigned') }}
                                    </button>
                                  </form>
                                @else
                                  <button style="width: 100%;" class="btn btn-sm btn-theme btn-social btn-block hidden-print disabled" rel="noopener" disabled title="{{ trans('admin/users/message.user_has_no_email') }}">
                                    <x-icon type="email" class="fa-fw" />
                                    {{ trans('admin/users/general.email_assigned') }}
                                  </button>
                                @endif
                              </div>
                    <br><br>
                    </div>
              </div>

              <!-- End button column -->

                <x-page-column class="col-md-9 col-xs-12 col-sm-pull-3 info-stack">
                    <x-page-data>
                        <x-data-row :label="trans('admin/users/table.name')">
                            {{ $user->display_name }}
                        </x-data-row>

                        @if ($user->companies->isNotEmpty())
                            <x-data-row :label="trans_choice('general.companies_var', $user->companies->count())">
                                @foreach ($user->companies as $userCompany)
                                    <span class="label label-light">{!! $userCompany->present()->formattedNameLink !!}</span>
                                @endforeach
                            </x-data-row>
                        @endif

                        <x-data-row :label="trans('admin/users/table.username')">
                            @if ($user->isSuperUser())
                                <label class="label label-danger"><i class="fas fa-crown" title="superuser"></i></label>&nbsp;
                            @elseif ($user->hasAccess('admin'))
                                <label class="label label-warning"><i class="fas fa-crown" title="admin"></i></label>&nbsp;
                            @endif
                            {{ $user->username }}
                        </x-data-row>

                        @if ($user->address || $user->city || $user->state || $user->country)
                            <x-data-row :label="trans('general.address')">
                                @if ($user->address) {{ $user->address }} <br> @endif
                                @if ($user->city) {{ $user->city }} @endif
                                @if ($user->state) {{ $user->state }} @endif
                                @if ($user->country) {{ $user->country }} @endif
                                @if ($user->zip) {{ $user->zip }} @endif
                            </x-data-row>
                        @endif

                        @if ($user->jobtitle)
                            <x-data-row :label="trans('admin/users/table.job')">
                                {{ $user->jobtitle }}
                            </x-data-row>
                        @endif

                        @if ($user->employee_num)
                            <x-data-row :label="trans('admin/users/table.employee_num')">
                                {{ $user->employee_num }}
                            </x-data-row>
                        @endif

                        @if ($user->manager)
                            <x-data-row :label="trans('admin/users/table.manager')">
                                {!! $user->manager?->present()->formattedNameLink !!}
                            </x-data-row>
                        @endif

                        @if ($user->email)
                            <x-data-row :label="trans('admin/users/table.email')">
                                <a href="mailto:{{ $user->email }}"><x-icon type="email" /> {{ $user->email }}</a>
                            </x-data-row>
                        @endif

                        @if ($user->website)
                            <x-data-row :label="trans('general.website')">
                                <a href="{{ $user->website }}" target="_blank"><x-icon type="external-link" /> {{ $user->website }}</a>
                            </x-data-row>
                        @endif

                        @if ($user->phone)
                            <x-data-row :label="trans('admin/users/table.phone')">
                                <a href="tel:{{ $user->phone }}"><x-icon type="phone" /> {{ $user->phone }}</a>
                            </x-data-row>
                        @endif

                        @if ($user->mobile)
                            <x-data-row :label="trans('admin/users/table.mobile')">
                                <a href="tel:{{ $user->mobile }}" data-tooltip="true" title="{{ trans('general.call') }}">
                                    <x-icon type="mobile" />
                                    {{ $user->mobile }}
                                </a>
                            </x-data-row>
                        @endif

                        @if ($user->userloc)
                            <x-data-row :label="trans('admin/users/table.location')">
                                {!! $user->userloc->present()->formattedNameLink !!}
                            </x-data-row>
                        @endif

                        <x-data-row :label="trans('general.last_login')">
                            {{ \App\Helpers\Helper::getFormattedDateObject($user->last_login, 'datetime', false) }}
                        </x-data-row>

                        @if ($user->department)
                            <x-data-row :label="trans('general.department')">
                                {!! $user->department->present()->formattedNameLink !!}
                            </x-data-row>
                        @endif

                        @if ($user->created_at)
                            <x-data-row :label="trans('general.created_at')">
                                {{ \App\Helpers\Helper::getFormattedDateObject($user->created_at, 'datetime')['formatted'] }}
                            </x-data-row>
                        @endif
                    </x-page-data>
                </x-page-column>



            </div> <!--/.row-->
          </div><!-- /.tab-pane -->

          <div class="tab-pane" id="assets">
            <table
                data-cookie-id-table="userAssignedAssets"
                data-toolbar="#userAssetToolbar"
                data-id-table="userAssets"
                data-side-pagination="server"
                data-sort-order="asc"
                data-sort-name="asset_tag"
                id="userAssets"
                class="table table-striped snipe-table"
                data-url="{{ route('api.account.assets') }}"
                data-export-options='{
                "fileName": "my-assets-{{ date('Y-m-d') }}",
                "ignoreColumn": ["image"]
                }'>

                <caption id="userAssetToolbar" class="tableCaption">
                    {{ trans('general.assets') }}
                </caption>

                <thead>
                <tr>
                    <th scope="col" data-field="image" data-formatter="imageFormatter" data-sortable="false">{{ trans('general.image') }}</th>
                    <th scope="col" data-field="category" data-sortable="true" data-visible="true">{{ trans('general.category') }}</th>
                    <th scope="col" data-field="asset_tag" data-sortable="true" data-visible="true">{{ trans('admin/hardware/table.asset_tag') }}</th>
                    <th scope="col" data-field="name" data-sortable="true" data-visible="false">{{ trans('general.name') }}</th>
                    <th scope="col" data-field="status_label" data-sortable="false" data-visible="false">{{ trans('general.status') }}</th>
                    <th scope="col" data-field="model" data-sortable="true" data-visible="true">{{ trans('admin/hardware/table.asset_model') }}</th>
                    <th scope="col" data-field="model_number" data-sortable="true" data-visible="false">{{ trans('general.model_no') }}</th>
                    <th scope="col" data-field="serial" data-sortable="true" data-visible="true">{{ trans('admin/hardware/table.serial') }}</th>
                    <th scope="col" data-field="manufacturer" data-sortable="true" data-visible="true">{{ trans('general.manufacturer') }}</th>
                    <th scope="col" data-field="rtd_location" data-sortable="true" data-visible="false">{{ trans('admin/hardware/form.default_location') }}</th>
                    <th scope="col" data-field="location" data-sortable="true" data-visible="false">{{ trans('general.location') }}</th>
                    <th scope="col" data-field="last_checkout" data-formatter="dateDisplayFormatter" data-sortable="true" data-visible="false">{{ trans('general.last_checkout') }}</th>
                    <th scope="col" data-field="expected_checkin" data-formatter="dateDisplayFormatter" data-sortable="true" data-visible="true">{{ trans('admin/hardware/form.expected_checkin') }}</th>
                    @can('self.view_purchase_cost')
                        <th scope="col" data-field="purchase_cost" data-sortable="true">{{ trans('general.purchase_cost') }}</th>
                    @endcan
                    <th scope="col" data-field="asset_eol_date" data-formatter="dateDisplayFormatter" data-sortable="true" data-visible="true">{{ trans('admin/hardware/form.eol_date') }}</th>
                    <th scope="col" data-field="last_audit_date" data-formatter="dateDisplayFormatter" data-sortable="true" data-visible="false">{{ trans('general.last_audit') }}</th>
                    <th scope="col" data-field="next_audit_date" data-formatter="dateDisplayFormatter" data-sortable="true" data-visible="false">{{ trans('general.next_audit_date') }}</th>
                    <th scope="col" data-field="byod" data-formatter="trueFalseFormatter" data-sortable="true" data-visible="false">{{ trans('general.byod') }}</th>

                    @foreach ($field_array as $db_column => $field_name)
                        <th scope="col" data-field="custom_fields.{{ $db_column }}" data-formatter="customFieldsFormatter" data-cell-style="customFieldsCellStyle" data-sortable="true" data-visible="true">{{ $field_name }}</th>
                    @endforeach
                </tr>
                </thead>
            </table>
          </div><!-- /asset -->


          <div class="tab-pane" id="licenses">

              <table
                      data-toolbar="#userLicensesToolbar"
                      data-cookie-id-table="userLicenses"
                      data-id-table="userLicenses"
                      data-side-pagination="client"
                      data-show-refresh="false"
                      data-sort-order="asc"
                      id="userLicenses"
                      class="table table-striped snipe-table"
                      data-export-options='{
                    "fileName": "my-licenses-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                    }'>

                <caption id="userLicensesToolbar" class="tableCaption">
                  {{ trans('general.licenses') }}
                </caption>

                <thead>
                <tr>
                  <th scope="col" class="col-md-2">{{ trans('general.name') }}</th>
                  <th scope="col" class="col-md-4">{{ trans('admin/licenses/form.license_key') }}</th>
                  <th scope="col" class="col-md-2">{{ trans('admin/licenses/form.to_name') }}</th>
                  <th scope="col" class="col-md-2">{{ trans('admin/licenses/form.to_email') }}</th>
                  <th scope="col" class="col-md-2">{{ trans('general.category') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($user->licenses as $license)
                  <tr>
                    <td>
                      {{ $license->name }}
                    </td>
                    <td>
                      @can('viewKeys', $license)
                        <code class="single-line"><span class="js-copy-link" data-clipboard-target=".js-copy-key-{{ $license->id }}" aria-hidden="true" data-tooltip="true" data-placement="top" title="{{ trans('general.copy_to_clipboard') }}"><span class="js-copy-key-{{ $license->id }}">{{ $license->serial }}</span></span></code>
                      @else
                        ------------
                      @endcan
                    </td>
                    <td>
                      @can('viewKeys', $license)
                        {{ $license->license_name }}
                      @else
                        ------------
                      @endcan
                    </td>
                      <td>
                      @can('viewKeys', $license)
                         {{$license->license_email}}
                      @else
                          ------------
                     @endcan
                     </td>

                    <td>{!!  ($license->category) ? $license->category->present()->formattedNameLink : trans('general.deleted') !!}</td>
                  </tr>
                @endforeach
                </tbody>
              </table>
          </div>

          <div class="tab-pane" id="accessories">
              <table
                      data-toolbar="#userAccessoryToolbar"
                      data-cookie-id-table="userAccessoryTable"
                      data-id-table="userAccessoryTable"
                      id="userAccessoryTable"
                      data-side-pagination="client"
                      data-show-footer="true"
                      data-show-refresh="false"
                      data-sort-order="asc"
                      data-sort-name="name"
                      class="table table-striped snipe-table table-hover"
                      data-export-options='{
                    "fileName": "export-accessory-{{ str_slug($user->username) }}-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","delete","download","icon"]
                    }'>

                <caption id="userAccessoryToolbar" class="tableCaption">
                  {{ trans('general.accessories') }}
                </caption>


                <thead>
                <tr>
                  <th scope="col" class="col-md-5">{{ trans('general.name') }}</th>
                  @can('self.view_purchase_cost')
                    <th scope="col" class="col-md-6" data-footer-formatter="sumFormatter" data-fieldname="purchase_cost">{{ trans('general.purchase_cost') }}</th>
                  @endcan
                </tr>
                </thead>
                <tbody>
                @foreach ($user->accessories as $accessory)
                  <tr>
                    <td>{{ $accessory->name }}</td>
                    @can('self.view_purchase_cost')
                      <td>
                        {!! Helper::formatCurrencyOutput($accessory->lastOrderDefaults()['unit_cost'] ?? null) !!}
                      </td>
                    @endcan

                  </tr>
                @endforeach
                </tbody>
              </table>
          </div><!-- /accessories-tab -->

          <div class="tab-pane" id="consumables">
              <table
                      data-toolbar="#userConsumableToolbar"
                      data-cookie-id-table="userConsumableTable"
                      data-id-table="userConsumableTable"
                      id="userConsumableTable"
                      data-side-pagination="client"
                      data-show-footer="true"
                      data-show-refresh="false"
                      data-sort-order="asc"
                      data-sort-name="name"
                      class="table table-striped snipe-table table-hover"
                      data-export-options='{
                    "fileName": "export-consumable-{{ str_slug($user->username) }}-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","delete","download","icon"]
                    }'>

                <caption id="userConsumableToolbar" class="tableCaption">
                  {{ trans('general.consumables') }}
                </caption>

                <thead>
                <tr>
                  <th scope="col" class="col-md-3">{{ trans('general.name') }}</th>
                  @can('self.view_purchase_cost')
                    <th scope="col" class="col-md-2" data-footer-formatter="sumFormatter" data-fieldname="purchase_cost">{{ trans('general.purchase_cost') }}</th>
                  @endcan
                  <th scope="col" class="col-md-2">{{ trans('general.date') }}</th>
                  <th scope="col" class="col-md-5">{{ trans('general.notes') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($user->consumables as $consumable)
                  <tr>
                    <td>{{ $consumable->name }}</td>
                    @can('self.view_purchase_cost')
                      <td>
                        {!! Helper::formatCurrencyOutput($consumable->lastOrderDefaults()['unit_cost'] ?? null) !!}
                      </td>
                    @endcan
                    <td>{{ Helper::getFormattedDateObject($consumable->pivot->created_at, 'datetime',  false) }}</td>
                    <td>{{ $consumable->pivot->note }}</td>
                  </tr>
                @endforeach
                </tbody>
              </table>
          </div><!-- /consumables-tab -->
          <div class="tab-pane" id="eulas">
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
                    data-url="{{ route('api.self.eulas', ['user_id' => e(request('user_id'))]) }}"
                    data-export-options='{
                    "fileName": "export-eula-{{ str_slug($user->username) }}-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","delete","purchasecost", "icon"]
                    }'>

              <caption id="userEulaToolbar" class="tableCaption">
                {{ trans('general.eula_long') }}
              </caption>

              <thead>
              <tr>
                <th scope="col" data-visible="true" data-field="icon" style="width: 40px;" class="hidden-xs" data-formatter="iconFormatter">{{ trans('admin/hardware/table.icon') }}</th>
                <th scope="col" data-visible="true" data-field="item.name">{{ trans('general.item') }}</th>
                <th scope="col" data-visible="true" data-field="created_at" data-sortable="true" data-formatter="dateDisplayFormatter">{{ trans('general.accepted_date') }}</th>
                <th scope="col" data-field="note">{{ trans('general.notes') }}</th>
                <th scope="col" data-field="url" data-formatter="downloadFormatter">{{ trans('general.download') }}</th>
              </tr>
              </thead>
            </table>
          </div><!-- /eulas-tab -->
        </div><!-- /.tab-content -->
      </div><!-- nav-tabs-custom -->
    </div>
  </div>







@stop

@section('moar_scripts')
  @include ('partials.bootstrap-table')
@stop
