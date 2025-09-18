@extends('layouts/default')

{{-- Page title --}}
@section('title')
  {{ trans('admin/licenses/general.view') }}
  - {{ $license->name }}
  @parent
@stop

{{-- Page content --}}
@section('content')
  <div class="row">
    <div class="col-md-9">

      <!-- Custom Tabs -->
      <div class="nav-tabs-custom">

        <ul class="nav nav-tabs hidden-print">

          <li class="active">
            <a href="#details" data-toggle="tab">
            <span class="hidden-lg hidden-md">
            <x-icon type="info-circle" class="fa-2x" />
            </span>
              <span class="hidden-xs hidden-sm">{{ trans('admin/users/general.info') }}</span>
            </a>
          </li>

          <li>
            <a href="#seats" data-toggle="tab">
            <span class="hidden-lg hidden-md">
              <x-icon type="seats" class="fa-2x" />
              </span>
              <span class="hidden-xs hidden-sm">{{ trans('general.assigned') }}</span>
              <span class="badge badge-secondary">{{ number_format($license->assignedCount()->count()) }}</span>

            </a>
          </li>
          <li>
            <a href="#available-seats" data-toggle="tab">
            <span class="hidden-lg hidden-md">
              <x-icon type="seats" class="fa-2x" />
              </span>
              <span class="hidden-xs hidden-sm">{{ trans('general.available') }}</span>
              <span class="badge badge-secondary">{{ number_format($license->availCount()->count()) }}</span>

            </a>
          </li>

          @can('licenses.files', $license)
            <li>
              <a href="#files" data-toggle="tab">
            <span class="hidden-lg hidden-md">
            <x-icon type="files" class="fa-2x" />
            </span>
                <span class="hidden-xs hidden-sm">{{ trans('general.file_uploads') }}
                  {!! ($license->uploads->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($license->uploads->count()).'</span>' : '' !!}
            </span>
              </a>
            </li>
          @endcan

          <li>
            <a href="#history" data-toggle="tab">
            <span class="hidden-lg hidden-md">
            <x-icon type="history" class="fa-2x" />
            </span>
              <span class="hidden-xs hidden-sm">{{ trans('general.history') }}</span>
            </a>
          </li>

          @can('update', \App\Models\License::class)
            <li class="pull-right"><a href="#" data-toggle="modal" data-target="#uploadFileModal">
                <x-icon type="paperclip" /> {{ trans('button.upload') }}</a>
            </li>
          @endcan
        </ul>

        <div class="tab-content">

          <div class="tab-pane active" id="details">
            <div class="row">
              <div class="col-md-12">
                <div class="container row-new-striped">

                  @if (!is_null($license->company))
                    <div class="row">
                      <div class="col-md-3">
                        <strong>{{ trans('general.company') }}</strong>
                      </div>
                      <div class="col-md-9">
                        <a href="{{ route('companies.show', $license->company->id) }}">{{ $license->company->name }}</a>
                      </div>
                    </div>
                  @endif

                  @if ($license->manufacturer)
                    <div class="row">
                      <div class="col-md-3">
                        <strong>{{ trans('admin/hardware/form.manufacturer') }}</strong>
                      </div>
                      <div class="col-md-9">
                        @can('view', \App\Models\Manufacturer::class)
                          <a href="{{ route('manufacturers.show', $license->manufacturer->id) }}">
                            {{ $license->manufacturer->name }}
                          </a>
                        @else
                          {{ $license->manufacturer->name }}
                        @endcan

                        @if ($license->manufacturer->url)
                          <br><x-icon type="globe-us" /> <a href="{{ $license->manufacturer->url }}" rel="noopener">{{ $license->manufacturer->url }}</a>
                        @endif

                        @if ($license->manufacturer->support_url)
                          <br><x-icon type="more-info" />
                          <a href="{{ $license->manufacturer->support_url }}"  rel="noopener">{{ $license->manufacturer->support_url }}</a>
                        @endif

                        @if ($license->manufacturer->support_phone)
                          <br><x-icon type="phone" />
                          <a href="tel:{{ $license->manufacturer->support_phone }}">{{ $license->manufacturer->support_phone }}</a>
                        @endif

                        @if ($license->manufacturer->support_email)
                          <br><x-icon type="email" /> <a href="mailto:{{ $license->manufacturer->support_email }}">{{ $license->manufacturer->support_email }}</a>
                        @endif
                      </div>
                    </div>
                  @endif


                  @if (!is_null($license->serial))
                    <div class="row">
                      <div class="col-md-3">
                        <strong>{{ trans('admin/licenses/form.license_key') }}</strong>
                        <i class="fa-regular fa-clipboard js-copy-link" data-clipboard-target=".js-copy" aria-hidden="true" data-tooltip="true" data-placement="top" title="{{ trans('general.copy_to_clipboard') }}">
                          <span class="sr-only">{{ trans('general.copy_to_clipboard') }}</span>
                        </i>
                      </div>
                      <div class="col-md-9">
                        @can('viewKeys', $license)
                          <code><span class="js-copy">{!! nl2br(e($license->serial)) !!}</span></code>
                        @else
                          ------------
                        @endcan
                      </div>
                    </div>
                  @endif


                  @if ($license->category)
                    <div class="row">
                      <div class="col-md-3">
                        <strong>{{ trans('general.category') }}</strong>
                      </div>
                      <div class="col-md-9">
                        <a href="{{ route('categories.show', $license->category->id) }}">{{ $license->category->name }}</a>
                      </div>
                    </div>
                  @endif


                  @if ($license->license_name!='')
                    <div class="row">
                      <div class="col-md-3">
                        <strong>{{ trans('admin/licenses/form.to_name') }}</strong>
                      </div>
                      <div class="col-md-9">
                        {{ $license->license_name }}
                      </div>
                    </div>
                  @endif

                  @if ($license->license_email!='')
                    <div class="row">
                      <div class="col-md-3">
                        <strong>
                          {{ trans('admin/licenses/form.to_email') }}
                        </strong>
                      </div>
                      <div class="col-md-9">
                        {{ $license->license_email }}
                      </div>
                    </div>
                  @endif


                  @if ($license->supplier)

                    <div class="row">
                      <div class="col-md-3">
                        <strong>{{ trans('general.supplier') }}</strong>
                      </div>
                      <div class="col-md-9">
                        @if ($license->supplier->deleted_at=='')
                          @can('view', \App\Models\Supplier::class)
                            <a href="{{ route('suppliers.show', $license->supplier->id) }}">
                              {{ $license->supplier->name }}
                            </a>
                          @else
                            {{ $license->supplier->name }}
                          @endcan

                          @if ($license->supplier->url)
                            <br><x-icon type="globe-us" /> <a href="{{ $license->supplier->url }}" rel="noopener">{{ $license->supplier->url }}</a>
                          @endif

                          @if ($license->supplier->phone)
                            <br><x-icon type="phone" />
                            <a href="tel:{{ $license->supplier->phone }}">{{ $license->supplier->phone }}</a>
                          @endif

                          @if ($license->supplier->email)
                            <br><x-icon type="email" /> <a href="mailto:{{ $license->supplier->email }}">{{ $license->supplier->email }}</a>
                          @endif

                          @if ($license->supplier->address)
                            <br>{{ $license->supplier->address }}
                          @endif
                          @if ($license->supplier->address2)
                            <br>{{ $license->supplier->address2 }}
                          @endif
                          @if ($license->supplier->city)
                            <br>{{ $license->supplier->city }},
                          @endif
                          @if ($license->supplier->state)
                            {{ $license->supplier->state }}
                          @endif
                          @if ($license->supplier->country)
                            {{ $license->supplier->country }}
                          @endif
                          @if ($license->supplier->zip)
                            {{ $license->supplier->zip }}
                          @endif
                        @else
                          {{ trans('general.deleted') }}
                        @endif
                      </div>
                    </div>
                  @endif


                  @if (isset($license->expiration_date))
                    <div class="row">
                      <div class="col-md-3">
                        <strong>
                          {{ trans('admin/licenses/form.expiration') }}
                        </strong>
                      </div>
                      <div class="col-md-9">
                        @if ($license->isExpired())
                          <span class="text-danger">
                           <x-icon type="warning" class="text-warning" />
                          </span>
                        @endif
                        {{ Helper::getFormattedDateObject($license->expiration_date, 'date', false) }}
                      </div>
                    </div>
                  @endif

                  @if ($license->termination_date)
                    <div class="row">
                      <div class="col-md-3">
                        <strong>
                          {{ trans('admin/licenses/form.termination_date') }}
                        </strong>
                      </div>
                      <div class="col-md-9">
                        @if ($license->isTerminated())
                          <span class="text-danger">
                           <x-icon type="warning" class="text-warning" />
                          </span>
                        @endif

                        {{ Helper::getFormattedDateObject($license->termination_date, 'date', false) }}
                      </div>
                    </div>
                  @endif


                  @if ($license->depreciation)
                    <div class="row">
                      <div class="col-md-3">
                        <strong>
                          {{ trans('admin/hardware/form.depreciation') }}
                        </strong>
                      </div>
                      <div class="col-md-9">
                        {{ $license->depreciation->name }}
                        ({{ $license->depreciation->months }}
                        {{ trans('admin/hardware/form.months') }}
                        )
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-3">
                        <strong>
                          {{ trans('admin/hardware/form.depreciates_on') }}
                        </strong>
                      </div>
                      <div class="col-md-9">
                        {{ Helper::getFormattedDateObject($license->depreciated_date(), 'date', false) }}
                      </div>
                    </div>


                    <div class="row">
                      <div class="col-md-3">
                        <strong>
                          {{ trans('admin/hardware/form.fully_depreciated') }}
                        </strong>
                      </div>
                      <div class="col-md-9">
                        @if ($license->time_until_depreciated())
                          @if ($license->time_until_depreciated()->y > 0)
                            {{ $license->time_until_depreciated()->y }}
                            {{ trans('admin/hardware/form.years') }},
                          @endif
                          {{ $license->time_until_depreciated()->m }}
                          {{ trans('admin/hardware/form.months') }}
                        @endif
                      </div>
                    </div>
                  @endif

                  @if ($license->purchase_order)
                    <div class="row">
                      <div class="col-md-3">
                        <strong>
                          {{ trans('admin/licenses/form.purchase_order') }}
                        </strong>
                      </div>
                      <div class="col-md-9">
                        {{ $license->purchase_order }}
                      </div>
                    </div>
                  @endif


                  @if (isset($license->purchase_date))
                    <div class="row">
                      <div class="col-md-3">
                        <strong>{{ trans('general.purchase_date') }}</strong>
                      </div>
                      <div class="col-md-9">
                        {{ Helper::getFormattedDateObject($license->purchase_date, 'date', false) }}

                      </div>
                    </div>
                  @endif

                  @if ($license->purchase_cost > 0)
                    <div class="row">
                      <div class="col-md-3">
                        <strong>
                          {{ trans('general.purchase_cost') }}
                        </strong>
                      </div>
                      <div class="col-md-9">
                        {{ $snipeSettings->default_currency }}
                        {{ Helper::formatCurrencyOutput($license->purchase_cost) }}
                      </div>
                    </div>
                  @endif

                  @if ($license->order_number)
                    <div class="row">
                      <div class="col-md-3">
                        <strong>
                          {{ trans('general.order_number') }}
                        </strong>
                      </div>
                      <div class="col-md-9">
                        {{ $license->order_number }}
                      </div>
                    </div>
                  @endif

                  <div class="row">
                    <div class="col-md-3">
                      <strong>
                        {{ trans('admin/licenses/form.maintained') }}
                      </strong>
                    </div>
                    <div class="col-md-9">
                      {!! $license->maintained ? '<i class="fas fa-check fa-fw text-success" aria-hidden="true"></i> '.trans('general.yes') : '<i class="fas fa-times fa-fw text-danger" aria-hidden="true"></i> '.trans('general.no') !!}
                    </div>
                  </div>

                  @if (($license->seats) && ($license->seats) > 0)
                    <div class="row">
                      <div class="col-md-3">
                        <strong>
                          {{ trans('admin/licenses/form.seats') }}
                        </strong>
                      </div>
                      <div class="col-md-9">

                        @if ($license->remaincount()  <= ($license->min_amt - \App\Models\Setting::getSettings()->alert_threshold))
                          <span data-tooltip="true" title="{{ trans('admin/licenses/general.below_threshold', ['remaining_count' => $license->remaincount(), 'min_amt' => $license->min_amt]) }}"><i class="fas fa-exclamation-triangle text-danger" aria-hidden="true"></i>
                        <span class="sr-only">{{ trans('general.warning') }}</span>
                        </span>
                        @endif

                        {{ $license->seats }}
                        @if ($license->remaincount()  <= ($license->min_amt - \App\Models\Setting::getSettings()->alert_threshold))

                        @endif

                      </div>
                    </div>
                  @endif


                  <div class="row">
                    <div class="col-md-3">
                      <strong>
                        {{ trans('admin/licenses/form.reassignable') }}
                      </strong>
                    </div>
                    <div class="col-md-9">
                      {!! $license->reassignable ? '<i class="fas fa-check fa-fw text-success" aria-hidden="true"></i> '.trans('general.yes') : '<i class="fas fa-times fa-fw text-danger" aria-hidden="true"></i> '.trans('general.no') !!}
                    </div>
                  </div>


                  @if ($license->notes)
                    <div class="row">
                      <div class="col-md-3">
                        <strong>
                          {{ trans('general.notes') }}
                        </strong>
                      </div>
                      <div class="col-md-9">
                        {!! nl2br(Helper::parseEscapedMarkedownInline($license->notes)) !!}
                      </div>
                    </div>
                  @endif

                </div> <!-- end row-striped -->
              </div>


            </div>
          </div> <!-- end tab-pane -->



          <div class="tab-pane" id="seats">
            <div class="row">
              <div class="col-md-12">

                <div class="table-responsive">

                  <table
                          data-columns="{{ \App\Presenters\LicensePresenter::dataTableLayoutSeats() }}"
                          data-cookie-id-table="seatsTable"
                          data-id-table="seatsTable"
                          id="seatsTable"
                          data-search="false"
                          data-side-pagination="server"
                          data-sort-order="asc"
                          data-sort-name="name"
                          class="table table-striped snipe-table"
                          data-url="{{ route('api.licenses.seats.index', [$license->id, 'status' => 'assigned']) }}"
                          data-export-options='{
                        "fileName": "export-seats-{{ str_slug($license->name) }}-{{ date('Y-m-d') }}",
                        "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                        }'>
                  </table>

                </div>

              </div>

            </div> <!--/.row-->
          </div> <!-- /.tab-pane -->

          <div class="tab-pane" id="available-seats">
            <div class="row">
              <div class="col-md-12">
                <div class="table-responsive">

                  <table
                          data-columns="{{ \App\Presenters\LicensePresenter::dataTableLayoutSeats() }}"
                          data-cookie-id-table="availableSeatsTable"
                          data-id-table="availableSeatsTable"
                          id="availableSeatsTable"
                          data-search="false"
                          data-side-pagination="server"
                          data-sort-order="asc"
                          data-sort-name="name"
                          class="table table-striped snipe-table"
                          data-url="{{ route('api.licenses.seats.index', [$license->id, 'status' => 'available']) }}"
                          data-export-options='{
                        "fileName": "export-available-seats-{{ str_slug($license->name) }}-{{ date('Y-m-d') }}",
                        "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                        }'>
                  </table>

                </div>

              </div>

            </div> <!--/.row-->
          </div> <!-- /.tab-pane -->

          @can('licenses.files', $license)
            <div class="tab-pane" id="files">
              <x-filestable object_type="licenses" :object="$license" />
            </div> <!-- /.tab-pane -->
          @endcan

          <div class="tab-pane" id="history">
            <div class="row">
              <div class="col-md-12">
                <div class="table-responsive">
                  <table
                          data-columns="{{ \App\Presenters\HistoryPresenter::dataTableLayout() }}"
                          class="table table-striped snipe-table"
                          data-cookie-id-table="licenseHistoryTable"
                          data-id-table="licenseHistoryTable"
                          id="licenseHistoryTable"
                          data-side-pagination="server"
                          data-sort-order="desc"
                          data-export-options='{
                       "fileName": "export-{{ str_slug($license->name) }}-history-{{ date('Y-m-d') }}",
                       "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                     }'
                          data-url="{{ route('api.activity.index', ['item_id' => $license->id, 'item_type' => 'license']) }}">
                  </table>
                </div>
              </div> <!-- /.col-md-12-->


            </div> <!-- /.row-->
          </div> <!-- /.tab-pane -->

        </div> <!-- /.tab-content -->

      </div> <!-- nav-tabs-custom -->
    </div>  <!-- /.col -->
    <div class="col-md-3">

      @can('update', $license)
        <a href="{{ route('licenses.edit', $license->id) }}" class="btn btn-warning btn-sm btn-social btn-block hidden-print" style="margin-bottom: 5px;">
          <x-icon type="edit" />
          {{ trans('admin/licenses/general.edit') }}
        </a>
        <a href="{{ route('clone/license', $license->id) }}" class="btn btn-info btn-block btn-sm btn-social hidden-print" style="margin-bottom: 5px;">
          <x-icon type="clone" />
          {{ trans('admin/licenses/general.clone') }}</a>
      @endcan

      @can('checkout', $license)

        @if (($license->availCount()->count() > 0) && (!$license->isInactive()))

          <a href="{{ route('licenses.checkout', $license->id) }}" class="btn bg-maroon btn-sm btn-social btn-block hidden-print" style="margin-bottom: 5px;">
            <x-icon type="checkout" />
            {{ trans('general.checkout') }}
          </a>

          <a href="#" class="btn bg-maroon btn-sm btn-social btn-block hidden-print" style="margin-bottom: 5px;" data-toggle="modal" data-tooltip="true" title="{{ trans('admin/licenses/general.bulk.checkout_all.enabled_tooltip') }}" data-target="#checkoutFromAllModal">
            <x-icon type="checkout" />
            {{ trans('admin/licenses/general.bulk.checkout_all.button') }}
          </a>

        @else
          <span data-tooltip="true" title="{{ ($license->availCount()->count() == 0) ? trans('admin/licenses/general.bulk.checkout_all.disabled_tooltip') : trans('admin/licenses/message.checkout.license_is_inactive') }}" class="btn bg-maroon btn-sm btn-social btn-block hidden-print disabled" style="margin-bottom: 5px;" data-tooltip="true" title="{{ trans('general.checkout') }}">
            <x-icon type="checkout" />
            {{ trans('general.checkout') }}
          </span>
          <span data-tooltip="true" title="{{ ($license->availCount()->count() == 0) ? trans('admin/licenses/general.bulk.checkout_all.disabled_tooltip') : trans('admin/licenses/message.checkout.license_is_inactive') }}" class="btn bg-maroon btn-sm btn-social btn-block hidden-print disabled" style="margin-bottom: 5px;" data-tooltip="true" title="{{ trans('general.checkout') }}">
              <x-icon type="checkout" />
              {{ trans('admin/licenses/general.bulk.checkout_all.button') }}
          </span>
        @endif
      @endcan

      @can('checkin', $license)

        @if (($license->seats - $license->availCount()->count()) <= 0 )
          <span data-tooltip="true" title=" {{ trans('admin/licenses/general.bulk.checkin_all.disabled_tooltip') }}">
            <a href="#"  class="btn btn-primary bg-purple btn-sm btn-social btn-block hidden-print disabled"  style="margin-bottom: 25px;">
              <x-icon type="checkin" />
             {{ trans('admin/licenses/general.bulk.checkin_all.button') }}
            </a>
        </span>
      @else
        <a href="#"  class="btn btn-primary bg-purple btn-sm btn-social btn-block hidden-print" style="margin-bottom: 25px;" data-toggle="modal" data-tooltip="true"  data-target="#checkinFromAllModal" data-content="{{ trans('general.sure_to_delete') }} data-title="{{  trans('general.delete') }}" onClick="return false;">
          <x-icon type="checkin" />
          {{ trans('admin/licenses/general.bulk.checkin_all.button') }}
        </a>
      @endif
    @endcan

      @can('delete', $license)

        @if ($license->availCount()->count() == $license->seats)
          <a class="btn btn-block btn-danger btn-sm btn-social delete-asset" data-icon="fa fa trash" data-toggle="modal" data-title="{{ trans('general.delete') }}" data-content="{{ trans('general.delete_confirm', ['item' => $license->name]) }}" data-target="#dataConfirmModal" onClick="return false;">
            <x-icon type="delete" />
            {{ trans('general.delete') }}
          </a>
        @else
          <span data-tooltip="true" title=" {{ trans('admin/licenses/general.delete_disabled') }}">
            <a href="#" class="btn btn-block btn-danger btn-sm btn-social delete-asset disabled" onClick="return false;">
              <x-icon type="delete" />
              {{ trans('general.delete') }}
            </a>
          </span>
        @endif
      @endcan
    </div>

  </div> <!-- /.row -->


  @can('checkin', \App\Models\License::class)
    @include ('modals.confirm-action',
          [
              'modal_name' => 'checkinFromAllModal',
              'route' => route('licenses.bulkcheckin', $license->id),
              'title' => trans('general.modal_confirm_generic'),
              'body' => trans_choice('admin/licenses/general.bulk.checkin_all.modal', 2, ['checkedout_seats_count' => $checkedout_seats_count])
          ])
  @endcan

  @can('checkout', \App\Models\License::class)
    @include ('modals.confirm-action',
          [
              'modal_name' => 'checkoutFromAllModal',
              'route' => route('licenses.bulkcheckout', $license->id),
              'title' => trans('general.modal_confirm_generic'),
              'body' => trans_choice('admin/licenses/general.bulk.checkout_all.modal', 2, ['available_seats_count' => $available_seats_count])
          ])
  @endcan



  @can('update', \App\Models\License::class)
    @include ('modals.upload-file', ['item_type' => 'license', 'item_id' => $license->id])
  @endcan

@stop


@section('moar_scripts')
  @include ('partials.bootstrap-table')
@stop
