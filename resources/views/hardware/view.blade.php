@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/hardware/general.view') }} {{ $asset->asset_tag }}
    @parent
@stop

{{-- Page content --}}
@section('content')


    <div class="row">

        @if (!$asset->model)
            <div class="col-md-12">
                <div class="callout callout-danger">
                    <p><strong>{{ trans('admin/models/message.no_association') }}</strong> {{ trans('admin/models/message.no_association_fix') }}</p>
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
                    <x-icon type="warning" />
                    {{ trans('general.asset_deleted_warning') }}
                </div>
            </div>
        @endif

        <div class="col-md-12">
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
                        <a href="#software" data-toggle="tab">
                          <span class="hidden-lg hidden-md">
                           <x-icon type="licenses" class="fa-2x" />
                          </span>
                            <span class="hidden-xs hidden-sm">{{ trans('general.licenses') }}
                                {!! ($asset->licenses->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($asset->licenses->count()).'</span>' : '' !!}
                          </span>
                        </a>
                    </li>

                    <li>
                        <a href="#components" data-toggle="tab">
                          <span class="hidden-lg hidden-md">
                            <x-icon type="components" class="fa-2x" />
                          </span>
                            <span class="hidden-xs hidden-sm">{{ trans('general.components') }}
                                {!! ($asset->components->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($asset->components->count()).'</span>' : '' !!}
                          </span>
                        </a>
                    </li>

                    <li>
                        <a href="#assets" data-toggle="tab">
                          <span class="hidden-lg hidden-md">
                            <x-icon type="assets" class="fa-2x" />
                          </span>
                            <span class="hidden-xs hidden-sm">
                                {{ trans('general.assets') }}
                                {!! ($asset->assignedAssets()->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($asset->assignedAssets()->count()).'</span>' : '' !!}

                          </span>
                        </a>
                    </li>

                    @if ($asset->assignedAccessories->count() > 0)
                        <li>
                            <a href="#accessories_assigned" data-toggle="tab" data-tooltip="true">

                                <span class="hidden-lg hidden-md">
                                    <i class="fas fa-keyboard fa-2x"></i>
                                </span>
                                <span class="hidden-xs hidden-sm">
                                    {{ trans('general.accessories_assigned') }}
                                    {!! ($asset->assignedAccessories()->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($asset->assignedAccessories()->count()).'</span>' : '' !!}

                                </span>
                            </a>
                        </li>
                    @endif


                    @if ($asset->audits->count() > 0)
                    <li>
                        <a href="#audits" data-toggle="tab" data-tooltip="true">

                            <span class="hidden-lg hidden-md">
                                <i class="fas fa-clipboard-check fa-2x"></i>
                            </span>
                            <span class="hidden-xs hidden-sm">
                                {{ trans('general.audits') }}
                                {!! ($asset->audits()->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($asset->audits()->count()).'</span>' : '' !!}

                            </span>
                        </a>
                    </li>
                    @endif

                    <li>
                        <a href="#history" data-toggle="tab">
                          <span class="hidden-lg hidden-md">
                              <x-icon type="history" class="fa-2x "/>
                          </span>
                            <span class="hidden-xs hidden-sm">{{ trans('general.history') }}
                          </span>
                        </a>
                    </li>

                    <li>
                        <a href="#maintenances" data-toggle="tab">
                          <span class="hidden-lg hidden-md">
                              <x-icon type="maintenances" class="fa-2x" />
                          </span>
                            <span class="hidden-xs hidden-sm">{{ trans('general.maintenances') }}
                                {!! ($asset->maintenances()->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($asset->maintenances()->count()).'</span>' : '' !!}
                          </span>
                        </a>
                    </li>

                    <li>
                        <a href="#files" data-toggle="tab">
                          <span class="hidden-lg hidden-md">
                            <x-icon type="files" class="fa-2x" />
                          </span>
                            <span class="hidden-xs hidden-sm">{{ trans('general.files') }}
                                {!! ($asset->uploads->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($asset->uploads->count()).'</span>' : '' !!}
                          </span>
                        </a>
                    </li>

                    @can('view', $asset->model)
                    <li>
                        <a href="#modelfiles" data-toggle="tab">
                          <span class="hidden-lg hidden-md">
                              <x-icon type="more-files" class="fa-2x" />
                          </span>
                            <span class="hidden-xs hidden-sm">
                            {{ trans('general.additional_files') }}
                                {!! ($asset->model) && ($asset->model->uploads->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($asset->model->uploads->count()).'</span>' : '' !!}
                          </span>
                        </a>
                    </li>
                    @endcan


                    @can('update', \App\Models\Asset::class)
                        <li class="pull-right">
                            <a href="#" data-toggle="modal" data-target="#uploadFileModal">
                                <span class="hidden-lg hidden-xl hidden-md">
                                    <x-icon type="paperclip" class="fa-2x" />
                                </span>
                                <span class="hidden-xs hidden-sm">
                                    <x-icon type="paperclip" />
                                    {{ trans('button.upload') }}
                                </span>
                            </a>
                        </li>
                    @endcan

                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade in active" id="details">
                    <div class="row">

                        <div class="info-stack-container">
                            <!-- Start button column -->
                            <div class="col-md-3 col-xs-12 col-sm-push-9 info-stack">

                                <div class="col-md-12 text-center">
                                    @if (($asset->image) || (($asset->model) && ($asset->model->image!='')))
                                        <div class="text-center col-md-12" style="padding-bottom: 15px;">
                                            <a href="{{ ($asset->getImageUrl()) ? $asset->getImageUrl() : null }}" data-toggle="lightbox" data-type="image">
                                                <img src="{{ ($asset->getImageUrl()) ? $asset->getImageUrl() : null }}" class="assetimg img-responsive" alt="{{ $asset->getDisplayNameAttribute() }}">
                                            </a>
                                        </div>
                                    @else
                                        <!-- generic image goes here -->
                                    @endif
                                </div>


                                @if ($asset->deleted_at=='')
                                    @can('update', $asset)
                                        <div class="col-md-12 hidden-print" style="padding-top: 5px;">
                                            <a href="{{ route('hardware.edit', $asset) }}" class="btn btn-sm btn-warning btn-social btn-block hidden-print">
                                                <x-icon type="edit" />
                                                {{ trans('admin/hardware/general.edit') }}
                                            </a>
                                        </div>
                                    @endcan


                                @if (($asset->assetstatus) && ($asset->assetstatus->deployable=='1'))
                                    @if (($asset->assigned_to != '') && ($asset->deleted_at==''))
                                        @can('checkin', $asset)
                                            <div class="col-md-12 hidden-print" style="padding-top: 5px;">
                                                    <span class="tooltip-wrapper"{!! (!$asset->model ? ' data-tooltip="true" title="'.trans('admin/hardware/general.model_invalid_fix').'"' : '') !!}>
                                                        <a role="button" href="{{ route('hardware.checkin.create', $asset->id) }}" class="btn btn-sm btn-primary bg-purple btn-social btn-block hidden-print{{ (!$asset->model ? ' disabled' : '') }}">
                                                            <x-icon type="checkin" />
                                                            {{ trans('admin/hardware/general.checkin') }}
                                                        </a>
                                                    </span>
                                            </div>
                                        @endcan
                                    @elseif (($asset->assigned_to == '') && ($asset->deleted_at==''))
                                        @can('checkout', $asset)
                                            <div class="col-md-12 hidden-print" style="padding-top: 5px;">
                                                    <span class="tooltip-wrapper"{!! (!$asset->model ? ' data-tooltip="true" title="'.trans('admin/hardware/general.model_invalid_fix').'"' : '') !!}>
                                                        <a href="{{ route('hardware.checkout.create', $asset->id)  }}" class="btn btn-sm bg-maroon btn-social btn-block hidden-print{{ (!$asset->model ? ' disabled' : '') }}">
                                                             <x-icon type="checkout" />
                                                            {{ trans('admin/hardware/general.checkout') }}
                                                    </a>
                                                    </span>
                                            </div>
                                        @endcan
                                    @endif
                                @endif

                                        <!-- Add notes -->
                                        @can('update', \App\Models\Asset::class)
                                            <div class="col-md-12 hidden-print" style="padding-top: 5px;">
                                                <a href="#" style="width: 100%" data-toggle="modal" data-target="#createNoteModal" class="btn btn-sm btn-primary btn-block btn-social hidden-print">
                                                    <x-icon type="note" />
                                                    {{ trans('general.add_note') }}
                                                </a>
                                                @include ('modals.add-note', ['type' => 'asset', 'id' => $asset->id])
                                            </div>
                                        @endcan




                                    @can('audit', \App\Models\Asset::class)
                                        <div class="col-md-12 hidden-print" style="padding-top: 5px;">
                                        <span class="tooltip-wrapper"{!! (!$asset->model ? ' data-tooltip="true" title="'.trans('admin/hardware/general.model_invalid_fix').'"' : '') !!}>
                                            <a href="{{ route('asset.audit.create', $asset->id)  }}" class="btn btn-sm btn-primary btn-block btn-social hidden-print{{ (!$asset->model ? ' disabled' : '') }}">
                                                 <x-icon type="audit" />
                                             {{ trans('general.audit') }}
                                            </a>
                                        </span>
                                        </div>
                                    @endcan
                                @endif

                                @can('create', $asset)
                                    <div class="col-md-12 hidden-print" style="padding-top: 5px;">
                                        <a href="{{ route('clone/hardware', $asset->id) }}" class="btn btn-sm btn-info btn-block btn-social hidden-print">
                                            <x-icon type="clone" />
                                            {{ trans('admin/hardware/general.clone') }}
                                        </a>
                                    </div>
                                @endcan

                                <div class="col-md-12 hidden-print" style="padding-top: 5px;">
                                    <form
                                        method="POST"
                                        action="{{ route('hardware/bulkedit') }}"
                                        accept-charset="UTF-8"
                                        class="form-inline"
                                        target="_blank"
                                        id="bulkForm"
                                    >
                                    @csrf
                                    <input type="hidden" name="bulk_actions" value="labels" />
                                    <input type="hidden" name="ids[{{$asset->id}}]" value="{{ $asset->id }}" />
                                    <button class="btn btn-block btn-social btn-sm btn-default" id="bulkEdit"{{ (!$asset->model ? ' disabled' : '') }}{!! (!$asset->model ? ' data-tooltip="true" title="'.trans('admin/hardware/general.model_invalid').'"' : '') !!}>
                                        <x-icon type="assets" />
                                        {{ trans_choice('button.generate_labels', 1) }}</button>
                                    </form>
                                </div>

                                @can('delete', $asset)
                                    <div class="col-md-12 hidden-print" style="padding-top: 30px; padding-bottom: 30px;">

                                        @if ($asset->deleted_at=='')
                                            <button class="btn btn-sm btn-block btn-danger btn-social delete-asset" data-toggle="modal" data-title="{{ trans('general.delete') }}" data-content="{{ trans('general.sure_to_delete_var', ['item' => $asset->asset_tag]) }}" data-target="#dataConfirmModal">

                                                <x-icon type="delete" />
                                                @if ($asset->assignedTo)
                                                    {{ trans('general.checkin_and_delete') }}
                                                @else
                                                    {{ trans('general.delete') }}
                                                @endif
                                            </button>
                                            <span class="sr-only">{{ trans('general.delete') }}</span>
                                        @else
                                            <form method="POST" action="{{ route('restore/hardware', [$asset]) }}">
                                                @csrf
                                                <button class="btn btn-sm btn-block btn-warning btn-social delete-asset">
                                                    <x-icon type="restore" />
                                                    {{ trans('general.restore') }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endcan

                                @if (($asset->assignedTo) && ($asset->deleted_at==''))
                                    <div class="col-md-12" style="text-align: left">
                                        <h2>
                                            {{ trans('admin/hardware/form.checkedout_to') }}
                                            <x-icon type="long-arrow-right" />
                                        </h2>

                                        <ul class="list-unstyled" style="line-height: 25px; font-size: 14px">

                                            @if (($asset->checkedOutToUser()) && ($asset->assignedTo->present()->gravatar()))
                                                <li>
                                                    <img src="{{ $asset->assignedTo->present()->gravatar() }}" class="user-image-inline hidden-print" alt="{{ $asset->assignedTo->display_name }}">
                                                    {!! $asset->assignedTo->present()->nameUrl() !!}
                                                </li>
                                            @else
                                                <li>
                                                    <x-icon type="{{ $asset->assignedType() }}" class="fa-fw" />
                                                    {!! $asset->assignedTo->present()->nameUrl() !!}
                                                </li>
                                            @endif


                                            @if ((isset($asset->assignedTo->employee_num)) && ($asset->assignedTo->employee_num!=''))
                                                <li>
                                                    <x-icon type="employee_num" class="fa-fw"/>
                                                    {{ $asset->assignedTo->employee_num }}
                                                </li>
                                            @endif
                                            @if ((isset($asset->assignedTo->email)) && ($asset->assignedTo->email!=''))
                                                <li>
                                                    <x-icon type="email" class="fa-fw" />
                                                    <a href="mailto:{{ $asset->assignedTo->email }}">{{ $asset->assignedTo->email }}</a>
                                                </li>
                                            @endif

                                            @if ((isset($asset->assignedTo)) && ($asset->assignedTo->phone!=''))
                                                <li>
                                                    <x-icon type="phone" class="fa-fw" />
                                                    <a href="tel:{{ $asset->assignedTo->phone }}">{{ $asset->assignedTo->phone }}</a>
                                                </li>
                                            @endif

                                            @if((isset($asset->assignedTo)) && ($asset->assignedTo->department))
                                                <li>
                                                    <x-icon type="department" class="fa-fw" />
                                                    {{ $asset->assignedTo->department->name}}</li>
                                            @endif

                                            @if (isset($asset->location))
                                                <li>
                                                    <x-icon type="locations" class="fa-fw" />
                                                     {{ $asset->location->parent?->name }}
                                                        @if ($asset->location->parent)
                                                            <i class="fas fa-long-arrow-alt-right" aria-hidden="true"></i>
                                                        @endif
                                                        {{ $asset->location->name }}</li>
                                                <li>{{ $asset->location->address }}
                                                    @if ($asset->location->address2!='')
                                                        {{ $asset->location->address2 }}
                                                    @endif
                                                </li>

                                                <li>{{ $asset->location->city }}
                                                    @if (($asset->location->city!='') && ($asset->location->state!=''))
                                                        ,
                                                    @endif
                                                    {{ $asset->location->state }} {{ $asset->location->zip }}
                                                </li>
                                            @endif
                                            <li>
                                                <x-icon type="calendar" class="fa-fw" />
                                                {{ trans('admin/hardware/form.checkout_date') }}: {{ Helper::getFormattedDateObject($asset->last_checkout, 'date', false) }}
                                            </li>
                                            @if (isset($asset->expected_checkin))
                                                <li>
                                                    <x-icon type="calendar" class="fa-fw" />
                                                    {{ trans('general.expected_checkin') }}: {{ Helper::getFormattedDateObject($asset->expected_checkin, 'date', false) }}
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                @endif
                                @if (($snipeSettings->qr_code=='1') || $snipeSettings->label2_2d_type!='none')
                                    <div class="col-md-12 text-center" style="padding-top: 15px;">
                                        <img src="{{ config('app.url') }}/hardware/{{ $asset->id }}/qr_code" class="img-thumbnail" style="height: 150px; width: 150px; margin-right: 10px;" alt="QR code for {{ $asset->getDisplayNameAttribute() }}">
                                    </div>
                                @endif
                                <br><br>
                            </div>




                            <!-- End button column -->

                            <div class="col-md-9 col-xs-12 col-sm-pull-3 info-stack">

                                <div class="row-new-striped">

                                    @if ($asset->asset_tag)
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>{{ trans('admin/hardware/form.tag') }}</strong>
                                            </div>
                                            <div class="col-md-9">
                                                <span class="js-copy-assettag">{{ $asset->asset_tag  }}</span>

                                                <i class="fa-regular fa-clipboard js-copy-link hidden-print" data-clipboard-target=".js-copy-assettag" aria-hidden="true" data-tooltip="true" data-placement="top" title="{{ trans('general.copy_to_clipboard') }}">
                                                    <span class="sr-only">{{ trans('general.copy_to_clipboard') }}</span>
                                                </i>
                                            </div>
                                        </div>
                                    @endif


                                    @if ($asset->deleted_at!='')
                                        <div class="row">
                                            <div class="col-md-3">
                                                <span class="text-danger"><strong>{{ trans('general.deleted') }}</strong></span>
                                            </div>
                                            <div class="col-md-9">
                                                {{ \App\Helpers\Helper::getFormattedDateObject($asset->deleted_at, 'date', false) }}

                                            </div>
                                        </div>
                                    @endif



                                    @if ($asset->assetstatus)

                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>{{ trans('general.status') }}</strong>
                                            </div>
                                            <div class="col-md-9">
                                                @if (($asset->assignedTo) && ($asset->deleted_at==''))
                                                    <x-icon type="circle-solid" class="text-blue" />
                                                    {{ $asset->assetstatus->name }}
                                                    <label class="label label-default">{{ trans('general.deployed') }}</label>


                                                    <x-icon type="long-arrow-right" />
                                                    <x-icon type="{{ $asset->assignedType() }}" class="fa-fw" />
                                                    {!!  $asset->assignedTo->present()->nameUrl() !!}
                                                @else
                                                    @if (($asset->assetstatus) && ($asset->assetstatus->deployable=='1'))
                                                        <x-icon type="circle-solid" class="text-green" />
                                                    @elseif (($asset->assetstatus) && ($asset->assetstatus->pending=='1'))
                                                        <x-icon type="circle-solid" class="text-orange" />
                                                    @else
                                                        <x-icon type="x" class="text-red" />
                                                    @endif
                                                    <a href="{{ route('statuslabels.show', $asset->assetstatus->id) }}">
                                                        {{ $asset->assetstatus->name }}</a>
                                                    <label class="label label-default">{{ $asset->present()->statusMeta }}</label>

                                                @endif
                                            </div>
                                        </div>
                                    @endif


                                    @if ($asset->company)
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>{{ trans('general.company') }}</strong>
                                            </div>
                                            <div class="col-md-9">
                                                <a href="{{ url('/companies/' . $asset->company->id) }}">{{ $asset->company->name }}</a>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($asset->name)
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>{{ trans('admin/hardware/form.name') }}</strong>
                                            </div>
                                            <div class="col-md-9">
                                                {{ $asset->name }}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($asset->serial)
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>{{ trans('admin/hardware/form.serial') }}</strong>
                                            </div>
                                            <div class="col-md-9">
                                                <span class="js-copy-serial">{{ $asset->serial  }}</span>

                                                <i class="fa-regular fa-clipboard js-copy-link hidden-print" data-clipboard-target=".js-copy-serial" aria-hidden="true" data-tooltip="true" data-placement="top" title="{{ trans('general.copy_to_clipboard') }}">
                                                    <span class="sr-only">{{ trans('general.copy_to_clipboard') }}</span>
                                                </i>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($asset->last_checkout!='')
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>
                                                    {{ trans('admin/hardware/table.checkout_date') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-9">
                                                {{ Helper::getFormattedDateObject($asset->last_checkout, 'datetime', false) }}
                                            </div>
                                        </div>
                                    @endif

                                    @if ((isset($audit_log)) && ($audit_log->created_at))
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>
                                                    {{ trans('general.last_audit') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-9">
                                                {!! $asset->checkInvalidNextAuditDate() ? '<i class="fas fa-exclamation-triangle text-orange" aria-hidden="true"></i>' : '' !!}
                                                {{ Helper::getFormattedDateObject($audit_log->created_at, 'datetime', false) }}
                                                @if ($audit_log->user)
                                                    (by {{ link_to_route('users.show', $audit_log->user->display_name, [$audit_log->user->id]) }})
                                                @endif

                                            </div>
                                        </div>
                                    @endif

                                    @if ($asset->next_audit_date)
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>
                                                    {{ trans('general.next_audit_date') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-9">
                                                {!! $asset->checkInvalidNextAuditDate() ? '<i class="fas fa-exclamation-triangle text-orange" aria-hidden="true"></i>' : '' !!}
                                                {{ Helper::getFormattedDateObject($asset->next_audit_date, 'date', false) }}
                                            </div>
                                        </div>
                                    @endif

                                    @if (($asset->model) && ($asset->model->manufacturer))
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>
                                                    {{ trans('admin/hardware/form.manufacturer') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-9">
                                                <ul class="list-unstyled">
                                                    @can('view', \App\Models\Manufacturer::class)

                                                        <li>
                                                            <a href="{{ route('manufacturers.show', $asset->model->manufacturer->id) }}">
                                                                {{ $asset->model->manufacturer->name }}
                                                            </a>
                                                        </li>

                                                    @else
                                                        <li> {{ $asset->model->manufacturer->name }}</li>
                                                    @endcan

                                                    @if (($asset->model) && ($asset->model->manufacturer) &&  ($asset->model->manufacturer->url!=''))
                                                        <li>
                                                            <x-icon type="globe-us" />
                                                            <a href="{{ $asset->present()->dynamicUrl($asset->model->manufacturer->url) }}" target="_blank">
                                                                {{ $asset->present()->dynamicUrl($asset->model->manufacturer->url) }}
                                                                <x-icon type="external-link" />
                                                            </a>
                                                        </li>
                                                    @endif

                                                    @if (($asset->model) && ($asset->model->manufacturer) &&  ($asset->model->manufacturer->support_url!=''))
                                                        <li>
                                                            <x-icon type="more-info" />
                                                            <a href="{{ $asset->present()->dynamicUrl($asset->model->manufacturer->support_url) }}" target="_blank">
                                                                {{ $asset->present()->dynamicUrl($asset->model->manufacturer->support_url) }}
                                                                <x-icon type="external-link" />
                                                            </a>
                                                        </li>
                                                    @endif

                                                    @if (($asset->model) && ($asset->model->manufacturer) &&  ($asset->model->manufacturer->warranty_lookup_url!=''))
                                                        <li>
                                                            <x-icon type="maintenances" />
                                                            <a href="{{ $asset->present()->dynamicUrl($asset->model->manufacturer->warranty_lookup_url) }}" target="_blank">
                                                                {{ $asset->present()->dynamicUrl($asset->model->manufacturer->warranty_lookup_url) }}

                                                                <x-icon type="external-link" />
                                                                    <span class="sr-only">{{ trans('admin/hardware/general.mfg_warranty_lookup', ['manufacturer' => $asset->model->manufacturer->name]) }}</span></i>
                                                            </a>
                                                        </li>
                                                    @endif

                                                    @if (($asset->model) && ($asset->model->manufacturer->support_phone))
                                                        <li>
                                                            <x-icon type="phone" />
                                                            <a href="tel:{{ $asset->model->manufacturer->support_phone }}">
                                                                {{ $asset->model->manufacturer->support_phone }}
                                                            </a>
                                                        </li>
                                                    @endif

                                                    @if (($asset->model) && ($asset->model->manufacturer->support_email))
                                                        <li>
                                                            <x-icon type="email" />
                                                            <a href="mailto:{{ $asset->model->manufacturer->support_email }}">
                                                                {{ $asset->model->manufacturer->support_email }}
                                                            </a>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>
                                                {{ trans('general.category') }}
                                            </strong>
                                        </div>
                                        <div class="col-md-9">
                                            @if (($asset->model) && ($asset->model->category))

                                                @can('view', \App\Models\Category::class)

                                                    <a href="{{ route('categories.show', $asset->model->category->id) }}">
                                                        {{ $asset->model->category->name }}
                                                    </a>
                                                @else
                                                    {{ $asset->model->category->name }}
                                                @endcan
                                            @else
                                                Invalid category
                                            @endif
                                        </div>
                                    </div>

                                    @if ($asset->model)
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>
                                                    {{ trans('admin/hardware/form.model') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-9">
                                                @if ($asset->model)

                                                    @can('view', \App\Models\AssetModel::class)
                                                        <a href="{{ route('models.show', $asset->model->id) }}">
                                                            {{ $asset->model->name }}
                                                        </a>
                                                    @else
                                                        {{ $asset->model->name }}
                                                    @endcan

                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>
                                                {{ trans('admin/models/table.modelnumber') }}
                                            </strong>
                                        </div>
                                        <div class="col-md-9">
                                            {{ ($asset->model) ? $asset->model->model_number : ''}}
                                        </div>
                                    </div>

                                    <!-- byod -->
                                    <div class="row byod">
                                        <div class="col-md-3">
                                            <strong>{{ trans('general.byod') }}</strong>
                                        </div>
                                        <div class="col-md-9">
                                            {!! ($asset->byod=='1') ? '<i class="fas fa-check text-success" aria-hidden="true"></i> '.trans('general.yes') : '<i class="fas fa-times text-danger" aria-hidden="true"></i> '.trans('general.no')  !!}
                                        </div>
                                    </div>

                                    <!-- requestable -->
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>{{ trans('admin/hardware/general.requestable') }}</strong>
                                        </div>
                                        <div class="col-md-9">
                                            {!! ($asset->requestable=='1') ? '<i class="fas fa-check text-success" aria-hidden="true"></i> '.trans('general.yes') : '<i class="fas fa-times text-danger" aria-hidden="true"></i> '.trans('general.no')  !!}
                                        </div>
                                    </div>

                                    @if (($asset->model) && ($asset->model->fieldset))
                                        @foreach($asset->model->fieldset->fields as $field)
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>
                                                        {{ $field->name }}
                                                    </strong>
                                                </div>
                                                <div class="col-md-9{{ (($field->format=='URL') && ($asset->{$field->db_column_name()}!='')) ? ' ellipsis': '' }}">
                                                    @if (!empty($asset->{$field->db_column_name()}))
                                                        {{-- Hidden span used as copy target --}}
                                                        {{-- It's tempting to break out the HTML into separate lines for this, but it results in extra spaces being added onto the end of the coipied value --}}
                                                        @if (($field->field_encrypted=='1') && (Gate::allows('assets.view.encrypted_custom_fields')))
                                                            <span class="js-copy-{{ $field->id }} visually-hidden hidden-print" style="font-size: 0px;">{{ ($field->isFieldDecryptable($asset->{$field->db_column_name()}) ? Helper::gracefulDecrypt($field, $asset->{$field->db_column_name()}) : $asset->{$field->db_column_name()}) }}</span>
                                                        @elseif (($field->field_encrypted=='1') && (Gate::denies('assets.view.encrypted_custom_fields')))
                                                            <span class="js-copy-{{ $field->id }} visually-hidden hidden-print" style="font-size: 0px;">{{ strtoupper(trans('admin/custom_fields/general.encrypted')) }}</span>
                                                        @else
                                                            <span class="js-copy-{{ $field->id }} visually-hidden hidden-print" style="font-size: 0px;">{{ $asset->{$field->db_column_name()} }}</span>
                                                        @endif

                                                            {{-- Clipboard icon --}}
                                                            <i class="fa-regular fa-clipboard js-copy-link hidden-print"
                                                               data-clipboard-target=".js-copy-{{ $field->id }}"
                                                               aria-hidden="true"
                                                               data-tooltip="true"
                                                               data-placement="top"
                                                               title="{{ trans('general.copy_to_clipboard') }}">
                                                                <span class="sr-only">{{ trans('general.copy_to_clipboard') }}</span>
                                                            </i>
                                                        @endif
                                                        @if (($field->field_encrypted=='1') && ($asset->{$field->db_column_name()}!='') && (Gate::allows('assets.view.encrypted_custom_fields')))
                                                            <i class="fas fa-lock" data-tooltip="true" data-placement="top" title="{{ trans('admin/custom_fields/general.value_encrypted') }}" onclick="showHideEncValue(this)" id="text-{{ $field->id }}"></i>
                                                        @endif

                                                        @if ($field->isFieldDecryptable($asset->{$field->db_column_name()} ))
                                                            @can('assets.view.encrypted_custom_fields')
                                                                @php
                                                                    $fieldSize = strlen(Helper::gracefulDecrypt($field, $asset->{$field->db_column_name()}))
                                                                @endphp
                                                                @if ($fieldSize > 0)
                                                                    <span id="text-{{ $field->id }}-to-hide">***********</span>
                                                                        @if (($field->format=='URL') && ($asset->{$field->db_column_name()}!=''))
                                                                            <span class="js-copy-{{ $field->id }} hidden-print"
                                                                                  id="text-{{ $field->id }}-to-show"
                                                                                  style="font-size: 0px;">
                                                                                <a href="{{ Helper::gracefulDecrypt($field, $asset->{$field->db_column_name()}) }}"
                                                                                        target="_new">{{ Helper::gracefulDecrypt($field, $asset->{$field->db_column_name()}) }}</a>
                                                                            </span>
                                                                        @elseif (($field->format=='DATE') && ($asset->{$field->db_column_name()}!=''))
                                                                            <span class="js-copy-{{ $field->id }} hidden-print"
                                                                                  id="text-{{ $field->id }}-to-show"
                                                                                  style="font-size: 0px;">{{ \App\Helpers\Helper::gracefulDecrypt($field, \App\Helpers\Helper::getFormattedDateObject($asset->{$field->db_column_name()}, 'date', false)) }}</span>
                                                                        @else
                                                                            <span class="js-copy-{{ $field->id }} hidden-print"
                                                                                  id="text-{{ $field->id }}-to-show"
                                                                                  style="font-size: 0px;">{{ Helper::gracefulDecrypt($field, $asset->{$field->db_column_name()}) }}</span>
                                                                        @endif
                                                                @endif
                                                            @else
                                                                {{ strtoupper(trans('admin/custom_fields/general.encrypted')) }}
                                                            @endcan

                                                        @else
                                                            @if (($field->format=='BOOLEAN') && ($asset->{$field->db_column_name()}!=''))
                                                                {!! ($asset->{$field->db_column_name()} == 1) ? "<span class='fas fa-check-circle' style='color:green' />" : "<span class='fas fa-times-circle' style='color:red' />" !!}
                                                            @elseif (($field->format=='URL') && ($asset->{$field->db_column_name()}!=''))
                                                                <a href="{{ $asset->{$field->db_column_name()} }}" target="_new">{{ $asset->{$field->db_column_name()} }}</a>
                                                            @elseif (($field->format=='DATE') && ($asset->{$field->db_column_name()}!=''))
                                                                {{ \App\Helpers\Helper::getFormattedDateObject($asset->{$field->db_column_name()}, 'date', false) }}
                                                            @else
                                                                {!! nl2br(e($asset->{$field->db_column_name()})) !!}
                                                            @endif

                                                        @endif

                                                        @if ($asset->{$field->db_column_name()}=='')
                                                            &nbsp;
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif


                                        @if ($asset->purchase_date)
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>
                                                        {{ trans('admin/hardware/form.date') }}
                                                    </strong>
                                                </div>
                                                <div class="col-md-9">
                                                    {{ Helper::getFormattedDateObject($asset->purchase_date, 'date', false) }}
                                                    -
                                                    {{ Carbon::parse($asset->purchase_date)->diffForHumans(['parts' => 3]) }}

                                                </div>
                                            </div>
                                        @endif

                                        @if ($asset->purchase_cost)
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>
                                                        {{ trans('admin/hardware/form.cost') }}
                                                    </strong>
                                                </div>
                                                <div class="col-md-9">
                                                    @if (($asset->id) && ($asset->location))
                                                        {{ $asset->location->currency }}
                                                    @elseif (($asset->id) && ($asset->location))
                                                        {{ $asset->location->currency }}
                                                    @else
                                                        {{ $snipeSettings->default_currency }}
                                                    @endif
                                                    {{ Helper::formatCurrencyOutput($asset->purchase_cost)}}

                                                </div>
                                            </div>
                                        @endif
                                        @if(($asset->components->count() > 0) && ($asset->purchase_cost))
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>
                                                        {{ trans('admin/hardware/table.components_cost') }}
                                                    </strong>
                                                </div>
                                                <div class="col-md-9">
                                                    @if (($asset->id) && ($asset->location))
                                                        {{ $asset->location->currency }}
                                                    @elseif (($asset->id) && ($asset->location))
                                                        {{ $asset->location->currency }}
                                                    @else
                                                        {{ $snipeSettings->default_currency }}
                                                    @endif
                                                    {{Helper::formatCurrencyOutput($asset->getComponentCost())}}
                                                </div>
                                            </div>
                                        @endif
                                        @if (($asset->model) && ($asset->depreciation) && ($asset->purchase_date))
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>
                                                        {{ trans('admin/hardware/table.current_value') }}
                                                    </strong>
                                                </div>
                                                <div class="col-md-9">
                                                    @if (($asset->id) && ($asset->location))
                                                        {{ $asset->location->currency }}
                                                    @elseif (($asset->id) && ($asset->location))
                                                        {{ $asset->location->currency }}
                                                    @else
                                                        {{ $snipeSettings->default_currency }}
                                                    @endif
                                                    {{ Helper::formatCurrencyOutput($asset->getDepreciatedValue() )}}


                                                </div>
                                            </div>
                                        @endif
                                        @if ($asset->order_number)
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>
                                                        {{ trans('general.order_number') }}
                                                    </strong>
                                                </div>
                                                <div class="col-md-9">
                                                    <a href="{{ route('hardware.index', ['order_number' => $asset->order_number]) }}">{{ $asset->order_number }}</a>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($asset->supplier)
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>
                                                        {{ trans('general.supplier') }}
                                                    </strong>
                                                </div>
                                                <div class="col-md-9">
                                                    @can ('superuser')
                                                        <a href="{{ route('suppliers.show', $asset->supplier_id) }}">
                                                            {{ $asset->supplier->name }}
                                                        </a>
                                                    @else
                                                        {{ $asset->supplier->name }}
                                                    @endcan
                                                </div>
                                            </div>
                                        @endif


                                        @if ($asset->warranty_months)
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>
                                                        {{ trans('admin/hardware/form.warranty') }}
                                                    </strong>
                                                </div>
                                                <div class="col-md-9">
                                                    {{ trans_choice('general.months_plural', $asset->warranty_months) }}
                                                    @if (($asset->model) && ($asset->model->manufacturer) && ($asset->model->manufacturer->warranty_lookup_url!=''))
                                                        <a href="{{ $asset->present()->dynamicUrl($asset->model->manufacturer->warranty_lookup_url) }}" target="_blank">
                                                            <x-icon type="external-link" />
                                                            <span class="sr-only">{{ trans('admin/hardware/general.mfg_warranty_lookup', ['manufacturer' => $asset->model->manufacturer->name]) }}</span></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>
                                                        {{ trans('admin/hardware/form.warranty_expires') }}


                                                    </strong>
                                                </div>
                                                <div class="col-md-9">
                                                    @if ($asset->purchase_date)
                                                        {{ Helper::getFormattedDateObject($asset->present()->warranty_expires(), 'date', false) }}
                                                        -
                                                        {{ Carbon::parse($asset->present()->warranty_expires())->diffForHumans(['parts' => 3]) }}

                                                        @if ($asset->purchase_date)
                                                            {!! $asset->present()->warranty_expires() < date("Y-m-d") ? '<i class="fas fa-exclamation-triangle text-orange" aria-hidden="true"></i>' : '' !!}
                                                        @endif
                                                    @else
                                                        {{ trans('general.na_no_purchase_date') }}
                                                    @endif
                                                </div>
                                            </div>

                                        @endif

                                        @if (($asset->model) && ($asset->depreciation))
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>
                                                        {{ trans('general.depreciation') }}
                                                    </strong>
                                                </div>
                                                <div class="col-md-9">
                                                    {{ $asset->depreciation->name }}
                                                    ({{ trans_choice('general.months_plural', $asset->depreciation->months) }})
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>
                                                        {{ trans('admin/hardware/form.fully_depreciated') }}
                                                    </strong>
                                                </div>
                                                <div class="col-md-9">
                                                    @if ($asset->purchase_date)
                                                        {{ Helper::getFormattedDateObject($asset->depreciated_date()->format('Y-m-d'), 'date', false) }}
                                                        -
                                                        {{ Carbon::parse($asset->depreciated_date())->diffForHumans(['parts' => 3]) }}
                                                    @else
                                                        {{ trans('general.na_no_purchase_date') }}
                                                    @endif

                                                </div>
                                            </div>
                                        @endif

                                        @if (($asset->asset_eol_date) && ($asset->purchase_date))
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>
                                                        {{ trans('admin/hardware/form.eol_rate') }}
                                                    </strong>
                                                </div>
                                                <div class="col-md-9">
                                                    {{ (int) Carbon::parse($asset->asset_eol_date)->diffInMonths($asset->purchase_date, true) }}
                                                    {{ trans('admin/hardware/form.months') }}

                                                </div>
                                            </div>
                                        @endif
                                        @if ($asset->asset_eol_date)
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>
                                                        {{ trans('admin/hardware/form.eol_date') }}
                                                        @if ($asset->purchase_date)
                                                            {!! $asset->asset_eol_date < date("Y-m-d") ? '<i class="fas fa-exclamation-triangle text-orange" aria-hidden="true"></i>' : '' !!}
                                                        @endif
                                                    </strong>
                                                </div>
                                                <div class="col-md-9">
                                                    @if ($asset->asset_eol_date)
                                                        {{ Helper::getFormattedDateObject($asset->asset_eol_date, 'date', false) }}
                                                        -
                                                        {{ Carbon::parse($asset->asset_eol_date)->locale(app()->getLocale())->diffForHumans(['parts' => 3]) }}
                                                    @else
                                                        {{ trans('general.na_no_purchase_date') }}
                                                    @endif
                                                    @if ($asset->eol_explicit =='1')
                                                            <span data-tooltip="true"
                                                                    data-placement="top"
                                                                    data-title="Explicit EOL"
                                                                    title="Explicit EOL">
                                                                    <x-icon type="warning" class="text-primary" />
                                                            </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif


                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>
                                                    {{ trans('admin/hardware/form.notes') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-9">
                                                {!! nl2br(Helper::parseEscapedMarkedownInline($asset->notes)) !!}
                                            </div>
                                        </div>

                                        @if ($asset->location)
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>
                                                        {{ trans('general.location') }}
                                                    </strong>
                                                </div>
                                                <div class="col-md-9">
                                                    @can('superuser')
                                                        <a href="{{ route('locations.show', ['location' => $asset->location->id]) }}">
                                                            {{ $asset->location->name }}
                                                        </a>
                                                    @else
                                                        {{ $asset->location->name }}
                                                    @endcan
                                                </div>
                                            </div>
                                        @endif

                                        @if ($asset->defaultLoc)
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>
                                                        {{ trans('admin/hardware/form.default_location') }}
                                                    </strong>
                                                </div>
                                                <div class="col-md-9">
                                                    @can('superuser')
                                                        <a href="{{ route('locations.show', ['location' => $asset->defaultLoc->id]) }}">
                                                            {{ $asset->defaultLoc->name }}
                                                        </a>
                                                    @else
                                                        {{ $asset->defaultLoc->name }}
                                                    @endcan
                                                </div>
                                            </div>
                                        @endif

                                        @if ($asset->created_at!='')
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>
                                                        {{ trans('general.created_at') }}
                                                    </strong>
                                                </div>
                                                <div class="col-md-9">
                                                    {{ Helper::getFormattedDateObject($asset->created_at, 'datetime', false) }}
                                                </div>
                                            </div>
                                        @endif

                                        @if ($asset->updated_at!='')
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>
                                                        {{ trans('general.updated_at') }}
                                                    </strong>
                                                </div>
                                                <div class="col-md-9">
                                                    {{ Helper::getFormattedDateObject($asset->updated_at, 'datetime', false) }}
                                                </div>
                                            </div>
                                        @endif

                                        @if ($asset->expected_checkin!='')
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>
                                                        {{ trans('general.expected_checkin') }}
                                                    </strong>
                                                </div>
                                                <div class="col-md-9">
                                                    {{ Helper::getFormattedDateObject($asset->expected_checkin, 'date', false) }}
                                                </div>
                                            </div>
                                        @endif

                                        @if ($asset->last_checkin!='')
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>
                                                        {{ trans('admin/hardware/table.last_checkin_date') }}
                                                    </strong>
                                                </div>
                                                <div class="col-md-9">
                                                    {{ Helper::getFormattedDateObject($asset->last_checkin, 'datetime', false) }}
                                                </div>
                                            </div>
                                        @endif



                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>
                                                    {{ trans('general.checkouts_count') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-9">
                                                {{ ($asset->checkouts) ? (int) $asset->checkouts->count() : '0' }}
                                            </div>
                                        </div>


                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>
                                                    {{ trans('general.checkins_count') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-9">
                                                {{ ($asset->checkins) ? (int) $asset->checkins->count() : '0' }}
                                            </div>
                                        </div>


                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>
                                                    {{ trans('general.user_requests_count') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-9">
                                                {{ ($asset->userRequests) ? (int) $asset->userRequests->count() : '0' }}
                                            </div>
                                        </div>

                                    </div> <!--/end striped container-->
                                </div> <!-- end col-md-9 -->
                            </div><!-- end info-stack-container -->
                            </div> <!--/.row-->
                        </div><!-- /.tab-pane -->

                        <div class="tab-pane fade" id="software">
                            <div class="row{{($asset->licenses->count() > 0 ) ? '' : ' hidden-print'}}">
                                <div class="col-md-12">
                                    <!-- Licenses assets table -->
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th>{{ trans('general.name') }}</th>
                                                <th><span class="line"></span>{{ trans('admin/licenses/form.license_key') }}</th>
                                                <th><span class="line"></span>{{ trans('admin/licenses/form.expiration') }}</th>
                                                <th><span class="line"></span>{{ trans('table.actions') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($asset->licenseseats as $seat)
                                                @if ($seat->license)
                                                    <tr>
                                                        <td><a href="{{ route('licenses.show', $seat->license->id) }}">{{ $seat->license->name }}</a></td>
                                                        <td>
                                                            @can('viewKeys', $seat->license)
                                                                <code class="single-line"><span class="js-copy-link" data-clipboard-target=".js-copy-key-{{ $seat->id }}" aria-hidden="true" data-tooltip="true" data-placement="top" title="{{ trans('general.copy_to_clipboard') }}"><span class="js-copy-key-{{ $seat->id }}">{{ $seat->license->serial }}</span></span></code>
                                                            @else
                                                                ------------
                                                            @endcan
                                                        </td>
                                                        <td>
                                                            {{ Helper::getFormattedDateObject($seat->license->expiration_date, 'date', false) }}
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('licenses.checkin', $seat->id) }}" class="btn btn-sm bg-purple hidden-print" data-tooltip="true">{{ trans('general.checkin') }}</a>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                            </tbody>
                                        </table>
                                </div><!-- /col -->
                            </div> <!-- row -->
                        </div> <!-- /.tab-pane software -->

                        <div class="tab-pane fade" id="components">
                            <!-- checked out assets table -->
                            <div class="row{{($asset->components->count() > 0 ) ? '' : ' hidden-print'}}">
                                <div class="col-md-12">

                                        <table class="table table-striped">
                                            <thead>
                                            <th>{{ trans('general.name') }}</th>
                                            <th>{{ trans('general.qty') }}</th>
                                            <th>{{ trans('general.purchase_cost') }}</th>
                                            <th>{{trans('admin/hardware/form.serial')}}</th>
                                            <th>{{trans('general.checkin')}}</th>
                                            <th></th>
                                            </thead>
                                            <tbody>
                                                <?php $totalCost = 0; ?>
                                            @foreach ($asset->components as $component)


                                                @if (is_null($component->deleted_at))
                                                    <tr>
                                                        <td>
                                                            <a href="{{ route('components.show', $component->id) }}">{{ $component->name }}</a>
                                                        </td>
                                                        <td>{{ $component->pivot->assigned_qty }}</td>
                                                        <td>
                                                            @if ($component->purchase_cost!='')
                                                                {{ trans('general.cost_each', ['amount' => Helper::formatCurrencyOutput($component->purchase_cost)])  }}
                                                            @endif
                                                        </td>
                                                        <td>{{ $component->serial }}</td>
                                                        <td>
                                                            <a href="{{ route('components.checkin.show', $component->pivot->id) }}" class="btn btn-sm bg-purple hidden-print" data-tooltip="true">{{ trans('general.checkin') }}</a>
                                                        </td>

                                                            <?php $totalCost = $totalCost + ($component->purchase_cost *$component->pivot->assigned_qty) ?>
                                                    </tr>
                                                @endif
                                            @endforeach
                                            </tbody>

                                            <tfoot>
                                            <tr>
                                                <td colspan="2">
                                                </td>
                                                <td>{{ $totalCost }}</td>
                                            </tr>
                                            </tfoot>
                                        </table>
                                </div>
                            </div>
                        </div> <!-- /.tab-pane components -->


                        <div class="tab-pane fade" id="assets">
                            <div class="row{{($asset->assignedAssets->count() > 0 ) ? '' : ' hidden-print'}}">
                                <div class="col-md-12">

                                    @include('partials.asset-bulk-actions')

                                        <!-- checked out assets table -->
                                        <div class="table-responsive">

                                            <table
                                                    data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                                                    data-show-columns-search="true"
                                                    data-cookie-id-table="assetsTable"
                                                    data-id-table="assetsTable"
                                                    data-side-pagination="server"
                                                    data-sort-order="asc"
                                                    data-toolbar="#assetsBulkEditToolbar"
                                                    data-bulk-button-id="#bulkAssetEditButton"
                                                    data-bulk-form-id="#assetsBulkForm"
                                                    id="assetsListingTable"
                                                    class="table table-striped snipe-table"
                                                    data-url="{{route('api.assets.index',['assigned_to' => $asset->id, 'assigned_type' => 'App\Models\Asset']) }}"
                                                    data-export-options='{
                                  "fileName": "export-assets-{{ str_slug($asset->name) }}-assets-{{ date('Y-m-d') }}",
                                  "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                                  }'>

                                            </table>
                                        </div>


                                </div><!-- /col -->
                            </div> <!-- row -->
                        </div> <!-- /.tab-pane software -->


                    <div class="tab-pane" id="accessories_assigned">


                        <div class="table table-responsive">

                            <h2 class="box-title" style="float:left">
                                {{ trans('general.accessories_assigned') }}
                            </h2>

                            <table
                                    data-columns="{{ \App\Presenters\AssetPresenter::assignedAccessoriesDataTableLayout() }}"
                                    data-cookie-id-table="accessoriesAssignedListingTable"
                                    data-id-table="accessoriesAssignedListingTable"
                                    data-side-pagination="server"
                                    data-sort-order="asc"
                                    id="accessoriesAssignedListingTable"
                                    class="table table-striped snipe-table"
                                    data-url="{{ route('api.assets.assigned_accessories', ['asset' => $asset]) }}"
                                    data-export-options='{
                                  "fileName": "export-locations-{{ str_slug($asset->name) }}-accessories-{{ date('Y-m-d') }}",
                                  "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                                  }'>
                            </table>

                        </div><!-- /.table-responsive -->
                    </div><!-- /.tab-pane -->


                        <div class="tab-pane fade" id="maintenances">
                            <div class="row{{($asset->maintenances->count() > 0 ) ? '' : ' hidden-print'}}">
                                <div class="col-md-12">

                                    <!-- Asset Maintenance table -->
                                    <table
                                            data-columns="{{ \App\Presenters\MaintenancesPresenter::dataTableLayout() }}"
                                            class="table table-striped snipe-table"
                                            id="MaintenancesTable"
                                            data-buttons="maintenanceButtons"
                                            data-id-table="MaintenancesTable"
                                            data-side-pagination="server"
                                            data-toolbar="#maintenance-toolbar"
                                            data-export-options='{
                                               "fileName": "export-{{ $asset->asset_tag }}-maintenances",
                                               "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                                             }'
                                            data-url="{{ route('api.maintenances.index', array('asset_id' => $asset->id)) }}"
                                            data-cookie-id-table="MaintenancesTable"
                                            data-cookie="true">
                                    </table>
                                </div> <!-- /.col-md-12 -->
                            </div> <!-- /.row -->
                        </div> <!-- /.tab-pane maintenances -->


                    <div class="tab-pane fade" id="audits">
                        <!-- checked out assets table -->
                        <div class="row">
                            <div class="col-md-12">
                                <table
                                        class="table table-striped snipe-table"
                                        id="assetAuditHistory"
                                        data-id-table="assetAuditHistory"
                                        data-side-pagination="server"
                                        data-sort-order="desc"
                                        data-sort-name="created_at"
                                        data-export-options='{
                                             "fileName": "export-asset-{{  $asset->id }}-audits",
                                             "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                                           }'
                                        data-url="{{ route('api.activity.index', ['item_id' => $asset->id, 'item_type' => 'asset', 'action_type' => 'audit']) }}"
                                        data-cookie-id-table="assetHistory"
                                        data-cookie="true">
                                    <thead>
                                    <tr>
                                        <th data-visible="true" data-field="icon" style="width: 40px;" class="hidden-xs" data-formatter="iconFormatter">{{ trans('admin/hardware/table.icon') }}</th>
                                        <th data-visible="true" data-field="created_at" data-sortable="true" data-formatter="dateDisplayFormatter">{{ trans('general.date') }}</th>
                                        <th data-visible="true" data-field="admin" data-formatter="usersLinkObjFormatter">{{ trans('general.created_by') }}</th>
                                        <th class="col-sm-2" data-field="file" data-sortable="true" data-visible="false" data-formatter="fileUploadNameFormatter">{{ trans('general.file_name') }}</th>
                                        <th data-field="note">{{ trans('general.notes') }}</th>
                                        <th data-visible="false" data-field="file" data-visible="false"  data-formatter="fileDownloadButtonsFormatter">{{ trans('general.download') }}</th>
                                        <th data-field="log_meta" data-visible="true" data-formatter="changeLogFormatter">{{ trans('admin/hardware/table.changed')}}</th>
                                        <th data-field="remote_ip" data-visible="false" data-sortable="true">{{ trans('admin/settings/general.login_ip') }}</th>
                                        <th data-field="user_agent" data-visible="false" data-sortable="true">{{ trans('admin/settings/general.login_user_agent') }}</th>
                                        <th data-field="action_source" data-visible="false" data-sortable="true">{{ trans('general.action_source') }}</th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div> <!-- /.row -->
                    </div> <!-- /.tab-pane history -->


                    <div class="tab-pane fade" id="history">
                            <!-- checked out assets table -->
                            <div class="row">
                                <div class="col-md-12">
                                    <table
                                            data-columns="{{ \App\Presenters\HistoryPresenter::dataTableLayout() }}"
                                            class="table table-striped snipe-table"
                                            id="assetHistory"
                                            data-id-table="assetHistory"
                                            data-side-pagination="server"
                                            data-sort-order="desc"
                                            data-sort-name="created_at"
                                            data-export-options='{
                                                 "fileName": "export-asset-{{  $asset->id }}-history",
                                                 "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                                               }'
                                            data-url="{{ route('api.activity.index', ['item_id' => $asset->id, 'item_type' => 'asset']) }}"
                                            data-cookie-id-table="assetHistory"
                                            data-cookie="true">
                                    </table>
                                </div>
                            </div> <!-- /.row -->
                        </div> <!-- /.tab-pane history -->

                        <div class="tab-pane fade" id="files">
                            <div class="row{{ ($asset->uploads->count() > 0 ) ? '' : ' hidden-print' }}">
                                <div class="col-md-12">
                                    <x-filestable object_type="assets" :object="$asset" />
                                </div> <!-- /.col-md-12 -->
                            </div> <!-- /.row -->
                        </div> <!-- /.tab-pane files -->

                        @if ($asset->model)
                            @can('view', $asset->model)
                                <div class="tab-pane fade" id="modelfiles">
                                    <div class="row{{ (($asset->model) && ($asset->model->uploads->count() > 0)) ? '' : ' hidden-print' }}">
                                        <div class="col-md-12">
                                            <x-filestable object_type="models" :object="$asset->model" />
                                        </div> <!-- /.col-md-12 -->
                                    </div> <!-- /.row -->
                                </div> <!-- /.tab-pane files -->
                            @endcan
                        @endif
                </div><!-- /.tab-content -->
            </div><!-- nav-tabs-custom -->
        </div>

        @can('update', \App\Models\Asset::class)
            @include ('modals.upload-file', ['item_type' => 'asset', 'item_id' => $asset->id])
        @endcan
    @stop
                @section('moar_scripts')
        @include ('partials.bootstrap-table')

    @stop
