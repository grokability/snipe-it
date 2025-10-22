@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.hello_name', array('name' => $user->display_name)) }}
@parent
@stop

{{-- Account page content --}}
@section('content')

@if ($acceptanceQuantity = \App\Models\CheckoutAcceptance::forUser(Auth::user())->pending()->sum('qty'))
  <div class="row">
    <div class="col-md-12">
      <div class="alert alert alert-warning fade in">
        <i class="fas fa-exclamation-triangle faa-pulse animated"></i>

        <strong>
          <a href="{{ route('account.accept') }}" style="color: white;">
            {{ trans_choice('general.unaccepted_profile_warning', $acceptanceQuantity, ['count' => $acceptanceQuantity]) }}
          </a>
          </strong>
      </div>
    </div>
  </div>
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
            <a href="#details" data-toggle="tab">
              <span class="hidden-lg hidden-md" aria-hidden="true">
              <i class="fas fa-info-circle fa-2x"></i>
              </span>
              <span class="hidden-xs hidden-sm">
                {{ trans('admin/users/general.info') }}
              </span>
            </a>
          </li>

          <li>
            <a href="#assets" data-toggle="tab">
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
            <a href="#licenses" data-toggle="tab">
            <span class="hidden-lg hidden-md">
            <i class="far fa-save fa-2x"></i>
            </span>
              <span class="hidden-xs hidden-sm">{{ trans('general.licenses') }}
                {!! ($user->licenses->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($user->licenses->count()).'</span>' : '' !!}
            </span>
            </a>
          </li>

          <li>
            <a href="#accessories" data-toggle="tab">
            <span class="hidden-lg hidden-md">
            <x-icon type="accessories" class="fa-2x" />
            </span>
              <span class="hidden-xs hidden-sm">{{ trans('general.accessories') }}
                {!! ($user->accessories->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($user->accessories->count()).'</span>' : '' !!}
            </span>
            </a>
          </li>

          <li>
            <a href="#consumables" data-toggle="tab">
            <span class="hidden-lg hidden-md" aria-hidden="true">
                <x-icon type="consumables" class="fa-2x" />
              </span>
              <span class="hidden-xs hidden-sm">{{ trans('general.consumables') }}
                {!! ($user->consumables->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($user->consumables->count()).'</span>' : '' !!}
            </span>
            </a>
          </li>

          <li>
            <a href="#eulas" data-toggle="tab">
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
              <div class="col-md-3 col-xs-12 col-sm-push-9">

                <div class="col-md-12 text-center">
                  <img src="{{ $user->present()->gravatar() }}"  class=" img-thumbnail hidden-print" style="margin-bottom: 20px;" alt="{{ $user->display_name }}" alt="User avatar">
                </div>
                @can('self.profile')
                  <div class="col-md-12">
                    <a href="{{ route('profile') }}" style="width: 100%;" class="btn btn-sm btn-warning btn-social btn-block hidden-print">
                      <x-icon type="edit" />
                      {{ trans('general.editprofile') }}
                    </a>
                  </div>
                @endcan

                @if ($user->ldap_import!='1')
                <div class="col-md-12" style="padding-top: 5px;">
                  <a href="{{ route('account.password.index') }}" style="width: 100%;" class="btn btn-sm btn-primary btn-social btn-block hidden-print" target="_blank" rel="noopener">
                    <x-icon type="password" class="fa-fw" />
                    {{ trans('general.changepassword') }}
                  </a>
                </div>
                @endif

                @can('self.api')
                <div class="col-md-12" style="padding-top: 5px;">
                  <a href="{{ route('user.api') }}" style="width: 100%;" class="btn btn-sm btn-primary btn-social btn-block hidden-print" target="_blank" rel="noopener">
                    <x-icon type="api-key" class="fa-fw" />
                    {{ trans('general.manage_api_keys') }}
                  </a>
                </div>
                @endcan


                  <div class="col-md-12" style="padding-top: 5px;">
                    <a href="{{ route('profile.print') }}" style="width: 100%;" class="btn btn-sm btn-primary btn-social btn-block hidden-print" target="_blank" rel="noopener">
                      <x-icon type="print" class="fa-fw" />
                      {{ trans('admin/users/general.print_assigned') }}
                    </a>
                  </div>


                  <div class="col-md-12" style="padding-top: 5px;">
                    @if (!empty($user->email))
                      <form action="{{ route('profile.email_assets') }}" method="POST">
                        {{ csrf_field() }}
                        <button style="width: 100%;" class="btn btn-sm btn-primary btn-social btn-block hidden-print" rel="noopener">
                          <x-icon type="email" class="fa-fw" />
                          {{ trans('admin/users/general.email_assigned') }}
                        </button>
                      </form>
                    @else
                      <button style="width: 100%;" class="btn btn-sm btn-primary btn-social btn-block hidden-print disabled" rel="noopener" disabled title="{{ trans('admin/users/message.user_has_no_email') }}">
                        <x-icon type="email" class="fa-fw" />
                        {{ trans('admin/users/general.email_assigned') }}
                      </button>
                    @endif
                  </div>

                <br><br>
              </div>

              <!-- End button column -->

              <div class="col-md-9 col-xs-12 col-sm-pull-3">

                <div class="row-new-striped">

                  <div class="row">
                    <!-- name -->

                    <div class="col-md-3 col-sm-2">
                      {{ trans('admin/users/table.name') }}
                    </div>
                    <div class="col-md-9 col-sm-2">
                      {{ $user->display_name }}
                    </div>

                  </div>



                  <!-- company -->
                  @if (!is_null($user->company))
                    <div class="row">

                      <div class="col-md-3">
                        {{ trans('general.company') }}
                      </div>
                      <div class="col-md-9">
                        {{ $user->company->name }}
                      </div>

                    </div>

                  @endif

                  <!-- username -->
                  <div class="row">

                    <div class="col-md-3">
                      {{ trans('admin/users/table.username') }}
                    </div>
                    <div class="col-md-9">

                      @if ($user->isSuperUser())
                        <label class="label label-danger"><i class="fas fa-crown" title="superuser"></i></label>&nbsp;
                      @elseif ($user->hasAccess('admin'))
                        <label class="label label-warning"><i class="fas fa-crown" title="admin"></i></label>&nbsp;
                      @endif
                      {{ $user->username }}

                    </div>

                  </div>

                  <!-- address -->
                  @if (($user->address) || ($user->city) || ($user->state) || ($user->country))
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.address') }}
                      </div>
                      <div class="col-md-9">

                        @if ($user->address)
                          {{ $user->address }} <br>
                        @endif
                        @if ($user->city)
                          {{ $user->city }}
                        @endif
                        @if ($user->state)
                          {{ $user->state }}
                        @endif
                        @if ($user->country)
                          {{ $user->country }}
                        @endif
                        @if ($user->zip)
                          {{ $user->zip }}
                        @endif

                      </div>
                    </div>
                  @endif

                  @if ($user->jobtitle)
                    <!-- jobtitle -->
                    <div class="row">

                      <div class="col-md-3">
                        {{ trans('admin/users/table.job') }}
                      </div>
                      <div class="col-md-9">
                        {{ $user->jobtitle }}
                      </div>

                    </div>
                  @endif

                  @if ($user->employee_num)
                    <!-- employee_num -->
                    <div class="row">

                      <div class="col-md-3">
                        {{ trans('admin/users/table.employee_num') }}
                      </div>
                      <div class="col-md-9">
                        {{ $user->employee_num }}
                      </div>

                    </div>
                  @endif

                  @if ($user->manager)
                    <!-- manager -->
                    <div class="row">

                      <div class="col-md-3">
                        {{ trans('admin/users/table.manager') }}
                      </div>
                      <div class="col-md-9">
                        <x-full-user-name :user="$user->manager" />
                      </div>

                    </div>

                  @endif


                  @if ($user->email)
                    <!-- email -->
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('admin/users/table.email') }}
                      </div>
                      <div class="col-md-9">
                        <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                      </div>
                    </div>
                  @endif

                  @if ($user->website)
                    <!-- website -->
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.website') }}
                      </div>
                      <div class="col-md-9">
                        <a href="{{ $user->website }}" target="_blank">{{ $user->website }}</a>
                      </div>
                    </div>
                  @endif

                  @if ($user->phone)
                    <!-- phone -->
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('admin/users/table.phone') }}
                      </div>
                      <div class="col-md-9">
                        <a href="tel:{{ $user->phone }}">{{ $user->phone }}</a>
                      </div>
                    </div>
                  @endif

                  @if ($user->userloc)
                    <!-- location -->
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('admin/users/table.location') }}
                      </div>
                      <div class="col-md-9">
                        {{ link_to_route('locations.show', $user->userloc->name, [$user->userloc->id]) }}
                      </div>
                    </div>
                  @endif

                  <!-- last login -->
                  <div class="row">
                    <div class="col-md-3">
                      {{ trans('general.last_login') }}
                    </div>
                    <div class="col-md-9">
                      {{ \App\Helpers\Helper::getFormattedDateObject($user->last_login, 'datetime', false) }}
                    </div>
                  </div>


                  @if ($user->department)
                    <!-- empty -->
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.department') }}
                      </div>
                      <div class="col-md-9">
                          {{ $user->department->name }}
                      </div>
                    </div>
                  @endif

                  @if ($user->created_at)
                    <!-- created at -->
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.created_at') }}
                      </div>
                      <div class="col-md-9">
                        {{ \App\Helpers\Helper::getFormattedDateObject($user->created_at, 'datetime')['formatted']}}
                      </div>
                    </div>
                  @endif

                </div> <!--/end striped container-->
              </div> <!-- end col-md-9 -->



            </div> <!--/.row-->
          </div><!-- /.tab-pane -->

          <div class="tab-pane" id="assets">
            <!-- checked out assets table -->

            <table
                  data-cookie-id-table="userAssignedAssets"
                  data-toolbar="#userAssetToolbar"
                  data-id-table="userAssets"
                  data-side-pagination="client"
                  data-show-footer="true"
                  data-sort-order="asc"
                  id="userAssets"
                  class="table table-striped snipe-table"
                  data-export-options='{
                  "fileName": "my-assets-{{ date('Y-m-d') }}",
                  "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                  }'>

                    <caption id="userAssetToolbar" class="tableCaption">
                      {{ trans('general.assets') }}
                    </caption>

                    <thead>
                    <tr>
                      <th class="col-md-1">
                        #
                      </th>
                      <th class="col-md-1">
                        {{ trans('general.image') }}
                      </th>
                      <th class="col-md-2" data-switchable="true" data-visible="true">
                        {{ trans('general.category') }}
                      </th>
                      <th class="col-md-2" data-switchable="true" data-visible="true">
                        {{ trans('admin/hardware/table.asset_tag') }}
                      </th>
                      <th class="col-md-2" data-switchable="true" data-visible="false">
                        {{ trans('general.name') }}
                      </th>
                      <th class="col-md-2" data-switchable="true" data-visible="false">
                        {{ trans('general.status') }}
                      </th>
                      <th class="col-md-2" data-switchable="true" data-visible="true">
                        {{ trans('admin/hardware/table.asset_model') }}
                      </th>
                      <th class="col-md-2" data-switchable="true" data-visible="false">
                        {{ trans('general.model_no') }}
                      </th>
                      <th class="col-md-3" data-switchable="true" data-visible="true">
                        {{ trans('admin/hardware/table.serial') }}
                      </th>
                      <th class="col-md-2" data-switchable="true" data-visible="false">
                        {{ trans('admin/hardware/form.default_location') }}
                      </th>
                      <th class="col-md-2" data-switchable="true" data-visible="false">
                        {{ trans('general.location') }}
                      </th>
                      <th class="col-md-2" data-switchable="true" data-visible="true">
                        {{ trans('admin/hardware/form.expected_checkin') }}
                      </th>
                      @can('self.view_purchase_cost')
                        <th class="col-md-6" data-footer-formatter="sumFormatter" data-fieldname="purchase_cost">
                          {{ trans('general.purchase_cost') }}
                        </th>
                      @endcan
                      <th class="col-md-2" data-switchable="true" data-visible="true">
                        {{ trans('admin/hardware/form.eol_date') }}
                      </th>
                      <th class="col-md-2" data-switchable="true" data-visible="false">
                        {{ trans('general.last_audit') }}
                      </th>
                      <th class="col-md-2" data-switchable="true" data-visible="false">
                        {{ trans('general.next_audit_date') }}
                      </th>
                      @foreach ($field_array as $db_column => $field_name)
                        <th class="col-md-1" data-switchable="true" data-visible="true">{{ $field_name }}</th>
                      @endforeach

                    </tr>

                    </thead>
                    <tbody>
                    @php
                      $counter = 1
                    @endphp
                    @foreach ($user->assets as $asset)
                      <tr>
                        <td>{{ $counter }}</td>
                        <td>
                          @if (($asset->image) && ($asset->image!=''))
                            <img src="{{ Storage::disk('public')->url(app('assets_upload_path').e($asset->image)) }}" style="max-height: 30px; width: auto" class="img-responsive" alt="">
                          @elseif (($asset->model) && ($asset->model->image!=''))
                            <img src="{{ Storage::disk('public')->url(app('models_upload_path').e($asset->model->image)) }}" style="max-height: 30px; width: auto" class="img-responsive" alt="">
                          @endif
                        </td>
                        <td>
                          @if (($asset->model) && ($asset->model->category))
                          {{ $asset->model->category->name }}
                          @endif
                        </td>
                        <td>
                          {{ $asset->asset_tag }}
                        </td>
                        <td>
                          {{ $asset->name }}
                        </td>
                        <td>
                          <x-icon type="circle-solid" class="text-blue" />
                          {{ $asset->assetstatus->name }}
                          <label class="label label-default">{{ trans('general.deployed') }}</label>
                        </td>
                        <td>
                            {{ $asset->model->name }}
                        </td>
                        <td>
                          {{ $asset->model->model_number }}
                        </td>
                        <td>
                          {{ $asset->serial }}
                        </td>
                        <td>
                          {{ ($asset->defaultLoc) ? $asset->defaultLoc->name : '' }}
                        </td>
                        <td>
                          {{ ($asset->location) ? $asset->location->name : '' }}
                        </td>
                        <td>
                          {{ ($asset->expected_checkin) ? $asset->expected_checkin_formatted_date : '' }}
                        </td>
                        @can('self.view_purchase_cost')
                        <td>
                          {!! Helper::formatCurrencyOutput($asset->purchase_cost) !!}
                        </td>
                        @endcan

                        <td>
                          {{ ($asset->asset_eol_date != '') ? Helper::getFormattedDateObject($asset->asset_eol_date, 'date', false) : null }}
                        </td>

                        <td>
                          {{ Helper::getFormattedDateObject($asset->last_audit_date, 'datetime', false) }}
                        </td>
                        <td>
                          {{ Helper::getFormattedDateObject($asset->next_audit_date, 'date', false) }}
                        </td>

                        @foreach ($field_array as $db_column => $field_value)
                          <td>
                            {{ $asset->{$db_column} }}
                          </td>
                        @endforeach

                      </tr>

                      @php
                        $counter++
                      @endphp
                    @endforeach
                    </tbody>
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
                  <th class="col-md-2">{{ trans('general.name') }}</th>
                  <th class="col-md-4">{{ trans('admin/licenses/form.license_key') }}</th>
                  <th class="col-md-2">{{ trans('admin/licenses/form.to_name') }}</th>
                  <th class="col-md-2">{{ trans('admin/licenses/form.to_email') }}</th>
                  <th class="col-md-2">{{ trans('general.category') }}</th>

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
                    @can('viewKeys', $license)
                    <td>{{$license->license_email}}</td>
                    @else
                      ------------
                    @endcan
                    <td>{{ ($license->category) ? $license->category->name : trans('general.deleted') }}</td>
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
                  <th class="col-md-5">{{ trans('general.name') }}</th>
                  @can('self.view_purchase_cost')
                    <th class="col-md-6" data-footer-formatter="sumFormatter" data-fieldname="purchase_cost">{{ trans('general.purchase_cost') }}</th>
                  @endcan
                  <th class="col-md-1 hidden-print">{{ trans('general.action') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($user->accessories as $accessory)
                  <tr>
                    <td>{{ $accessory->name }}</td>
                    @can('self.view_purchase_cost')
                      <td>
                        {!! Helper::formatCurrencyOutput($accessory->purchase_cost) !!}
                      </td>
                    @endcan
                    <td class="hidden-print">
                      @can('checkin', $accessory)
                        <a href="{{ route('accessories.checkin.show', array('accessoryID'=> $accessory->pivot->id, 'backto'=>'user')) }}" class="btn btn-primary btn-sm hidden-print">{{ trans('general.checkin') }}</a>
                      @endcan
                    </td>
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
                  <th class="col-md-3">{{ trans('general.name') }}</th>
                  @can('self.view_purchase_cost')
                    <th class="col-md-2" data-footer-formatter="sumFormatter" data-fieldname="purchase_cost">{{ trans('general.purchase_cost') }}</th>
                  @endcan
                  <th class="col-md-2">{{ trans('general.date') }}</th>
                  <th class="col-md-5">{{ trans('general.notes') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($user->consumables as $consumable)
                  <tr>
                    <td>{{ $consumable->name }}</td>
                    @can('self.view_purchase_cost')
                      <td>
                        {!! Helper::formatCurrencyOutput($consumable->purchase_cost) !!}
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
                    data-url="{{ route('api.self.eulas') }}"
                    data-export-options='{
                    "fileName": "export-eula-{{ str_slug($user->username) }}-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","delete","purchasecost", "icon"]
                    }'>

              <caption id="userEulaToolbar" class="tableCaption">
                {{ trans('general.eula_long') }}
              </caption>

              <thead>
              <tr>
                <th data-visible="true" data-field="icon" style="width: 40px;" class="hidden-xs" data-formatter="iconFormatter">{{ trans('admin/hardware/table.icon') }}</th>
                <th data-visible="true" data-field="item.name">{{ trans('general.item') }}</th>
                <th data-visible="true" data-field="created_at" data-sortable="true" data-formatter="dateDisplayFormatter">{{ trans('general.accepted_date') }}</th>
                <th data-field="note">{{ trans('general.notes') }}</th>
                <th data-field="url" data-formatter="downloadFormatter">{{ trans('general.download') }}</th>
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
