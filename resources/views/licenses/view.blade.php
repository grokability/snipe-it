@extends('layouts/default')

{{-- Page title --}}
@section('title')
  {{ trans('admin/licenses/general.view') }}
  - {{ $license->name }}
  @parent
@stop

@section('header_right')
    <x-button.info-panel-toggle/>
@endsection

{{-- Page content --}}
@section('content')
    <x-container columns="2">
        <x-page-column class="col-md-9 main-panel">
            <x-tabs>
                <x-slot:tabnav>

                    <x-tabs.nav-item
                            name="seats"
                            icon_type="checkedout"
                            label="{{ trans('general.assigned') }}"
                            count="{{ $license->assignedCount()->count() }}"
                    />

                    @can('checkout', $license)
                    <x-tabs.nav-item
                            name="available"
                            icon_type="available"
                            label="{{ trans('general.available') }}"
                            count="{{ $license->availCount()->count() }}"
                    />
                    @endcan

                    <x-tabs.files-tab :item="$license" count="{{ $license->uploads()->count() }}"/>
                    <x-tabs.history-tab count="{{ $license->history()->count() }}" :model="$license"/>
                    <x-tabs.upload-tab :item="$license"/>
                </x-slot:tabnav>

                <x-slot:tabpanes>

                    <x-tabs.pane name="seats">
                        <x-slot:table_header>
                            {{ trans('general.assigned') }}
                        </x-slot:table_header>

                        <x-table
                            fixed_right_number="1"
                            fixed_number="1"
                            api_url="{{ route('api.licenses.seats.index', [$license->id, 'status' => 'assigned']) }}"
                            :presenter="\App\Presenters\LicensePresenter::dataTableLayoutSeats()"
                            export_filename="export-{{ str_slug($license->name) }}-assigned-{{ date('Y-m-d') }}"
                        />

                    </x-tabs.pane>


                    @can('checkout', $license)
                    <x-tabs.pane name="available">
                        <x-slot:table_header>
                            {{ trans('general.available') }}
                        </x-slot:table_header>

                        <x-table
                            show_search="false"
                            api_url="{{ route('api.licenses.seats.index', [$license->id, 'status' => 'available']) }}"
                            :presenter="\App\Presenters\LicensePresenter::dataTableLayoutSeats()"
                            export_filename="export-{{ str_slug($license->name) }}-available-{{ date('Y-m-d') }}"
                        />

                    </x-tabs.pane>
                    @endcan


                    <!-- start history tab pane -->
                    <x-tabs.pane name="history">
                        <x-table.history :model="$license" :route="route('api.licenses.history', $license)"/>
                    </x-tabs.pane>
                    <!-- end history tab pane -->


                    <!-- start files tab pane -->
                    <x-tabs.pane name="files">
                        <x-table.files object_type="licenses" :object="$license" />
                    </x-tabs.pane>
                    <!-- end files tab pane -->

                </x-slot:tabpanes>
            </x-tabs>
        </x-page-column>

        <x-page-column class="col-md-3">
            <x-box class="side-box expanded">
                <x-info-panel :infoPanelObj="$license" img_path="{{ app('licenses_upload_url') }}">


                    <x-slot:buttons>
                        <x-button.edit :item="$license" :route="route('licenses.edit', $license->id)"/>
                        <x-button.clone :item="$license" :route="route('clone/license', $license->id)"/>
                        <x-button.checkout permission="checkout" :item="$license" :route="route('licenses.checkout', $license->id)" />

                        @can('checkout', $license)

                            @if (($license->availCount()->count() > 0) && (!$license->isInactive()))

                                <a href="#" class="btn bg-maroon btn-sm hidden-print" data-toggle="modal" data-tooltip="true" title="{{ trans('admin/licenses/general.bulk.checkout_all.enabled_tooltip') }}" data-target="#checkoutFromAllModal">
                                    <x-icon type="checkout-all" class="fa-fw"/>
                                </a>

                            @else
                                <span data-tooltip="true" title="{{ ($license->availCount()->count() == 0) ? trans('admin/licenses/general.bulk.checkout_all.disabled_tooltip') : trans('admin/licenses/message.checkout.license_is_inactive') }}" class="btn bg-maroon btn-sm hidden-print disabled" title="{{ trans('general.checkout') }}">
                                      <x-icon type="checkout-all" class="fa-fw"/>
                                  </span>
                            @endif
                        @endcan


                        @can('checkin', $license)

                            @if (($license->seats - $license->availCount()->count()) <= 0 )
                                <span data-tooltip="true" title=" {{ trans('admin/licenses/general.bulk.checkin_all.disabled_tooltip') }}">
                                        <a href="#" class="btn btn-primary bg-purple btn-sm hidden-print disabled"><x-icon type="checkin-all" class="fa-fw"/></a>
                                    </span>
                            @else
                                <a href="#" class="btn bg-purple btn-sm hidden-print" data-toggle="modal" data-tooltip="true" data-target="#checkinFromAllModal" data-content="{{ trans('general.sure_to_delete') }} title=" {{ trans('admin/licenses/general.bulk.checkin_all.button') }} data-title=" {{ trans('admin/licenses/general.bulk.checkin_all.button') }}">
                                    <x-icon type="checkin-all" class="fa-fw"/>
                                </a>
                            @endif
                        @endcan


                        <x-button.delete :item="$license" />


                    </x-slot:buttons>


                    <x-slot:before_list>




                    </x-slot:before_list>
                </x-info-panel>
            </x-box>

        </x-page-column>
    </x-container>

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

@endsection

@section('moar_scripts')
    @can('files', $license)
        @include ('modals.upload-file', ['item_type' => 'licenses', 'item_id' => $license->id])
    @endcan

    @include ('partials.bootstrap-table')
@endsection
