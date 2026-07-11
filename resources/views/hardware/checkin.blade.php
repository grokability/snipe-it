@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/hardware/general.checkin') }}
    @parent
@stop

{{-- Page content --}}
@section('content')
    <style>

        .input-group {
            padding-left: 0px !important;
        }
    </style>


    <div class="row"><!-- .row -->
        <!-- left column -->
        <div class="col-md-7 col-sm-11 col-xs-12 col-md-offset-2">
            <div class="box box-default"><!-- .box-default -->
                <div class="box-header with-border"><!-- .box-header -->
                    <h2 class="box-title">
                        {{ trans('admin/hardware/form.tag') }}
                        {{ $asset->asset_tag }}
                    </h2>
                </div><!-- /.box-header -->

                <div class="box-body"><!-- .box-body -->
                    <div class="col-md-12"><!-- .col-md-12 -->

                        @if ($backto == 'user')
                            <form class="form-horizontal" method="post"
                                  action="{{ route('hardware.checkin.store', array('assetId'=> $asset->id, 'backto'=>'user')) }}"
                                  autocomplete="off" enctype="multipart/form-data">
                                @else
                                    <form class="form-horizontal" method="post"
                                          action="{{ route('hardware.checkin.store', array('assetId'=> $asset->id)) }}"
                                          autocomplete="off" enctype="multipart/form-data">
                                        @endif
                                        {{csrf_field()}}

                                        @if ($asset->company)
                                            <!-- accessory name -->
                                            <div class="form-group">
                                                <label class="col-sm-3 control-label">{{ trans('general.company') }}</label>
                                                <div class="col-md-6">
                                                    <p class="form-control-static">{!! $asset->company->present()->formattedNameLink  !!}</p>
                                                </div>
                                            </div>
                                        @endif


                                        @if ($asset->model->category)
                                            <!-- category name -->
                                            <div class="form-group">
                                                <label class="col-sm-3 control-label">{{ trans('general.category') }}</label>
                                                <div class="col-md-6">
                                                    <p class="form-control-static">{!! $asset->model->category->present()->formattedNameLink  !!}</p>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- AssetModel name -->
                                        <div class="form-group">
                                            <label for="model" class="col-sm-3 control-label">
                                                {{ trans('admin/hardware/form.model') }}
                                            </label>
                                            <div class="col-md-8">

                                                <p class="form-control-static">
                                                    @if (($asset->model) && ($asset->model->name))
                                                        {{ $asset->model->name }}
                                                    @else
                                                        <span class="text-danger text-bold">
                                                          <x-icon type="warning" />
                                                          {{ trans('admin/hardware/general.model_invalid')}}
                                                        </span>
                                                        {{ trans('admin/hardware/general.model_invalid_fix')}}
                                                        <a href="{{ route('hardware.edit', $asset->id) }}">
                                                            <strong>{{ trans('admin/hardware/general.edit') }}</strong>
                                                        </a>
                                                    @endif
                                                </p>

                                            </div>
                                        </div>

                                        <!-- Asset Name -->
                                        <div class="form-group {{ $errors->has('name') ? 'error' : '' }}">
                                            <label for="name" class="col-sm-3 control-label">
                                                {{ trans('general.name') }}
                                            </label>
                                            <div class="col-md-8">
                                                <input class="form-control" type="text" name="name" aria-label="name"
                                                       id="name" value="{{ old('name', $asset->name) }}"/>
                                                {!! $errors->first('name', '<span class="alert-msg" role="alert" aria-live="assertive"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                            </div>
                                        </div>

                                        <!-- Status -->
                                        <div class="form-group {{ $errors->has('status_id') ? 'error' : '' }}">
                                            <label for="status_id" class="col-sm-3 control-label">
                                                {{ trans('admin/hardware/form.status') }}
                                            </label>
                                            <div class="col-md-8 required">
                                                <x-input.select
                                                    name="status_id"
                                                    id="modal-statuslabel_types"
                                                    :options="$statusLabel_list"
                                                    :selected="old('status_id')"
                                                    style="width: 100%"
                                                    aria-label="status_id"
                                                />
                                                {!! $errors->first('status_id', '<span class="alert-msg" role="alert" aria-live="assertive"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                            </div>
                                        </div>

                                        <div
                                            class="form-group"
                                            id="requestable-wrapper"
                                            @if (! $show_requestable_toggle) style="display: none;" @endif
                                        >
                                            <div class="col-md-9 col-md-offset-3">
                                                <label class="form-control" for="requestable">
                                                    <input
                                                        type="checkbox"
                                                        value="1"
                                                        name="requestable"
                                                        id="requestable"
                                                        @checked((bool) old('requestable', $asset->requestable))
                                                    />
                                                    {{ trans('admin/hardware/general.requestable') }}
                                                </label>
                                            </div>
                                        </div>

                                        <x-input.location-select
                                            :label="trans('general.location')"
                                            name="location_id"
                                            :help_text="($asset->defaultLoc) ? trans('general.checkin_to_diff_location', ['default_location' => $asset->defaultLoc->name]) : null"
                                            :selected="old('location_id')"
                                            :company_id="$asset->company_id"
                                        />

                                        <!-- Update actual location  -->
                                        <x-form.radio-row
                                            name="update_default_location"
                                            selected="1"
                                            :options="[
                                                '1' => trans('admin/hardware/form.asset_location'),
                                                '0' => trans('admin/hardware/form.asset_location_update_default_current'),
                                            ]"
                                        /> <!--/form-group-->

                                        <!-- Checkout/Checkin Date -->
                                        <div class="form-group{{ $errors->has('checkin_at') ? ' has-error' : '' }}">
                                            <label for="checkin_at" class="col-sm-3 col-xs-12 col-sm-12 control-label">
                                                {{ trans('admin/hardware/form.checkin_date') }}
                                            </label>

                                            <div class="col-md-8 col-xs-12 col-sm-12">
                                                <div class="input-group col-xl-5 col-lg-5 col-md-7 col-sm-9 col-xs-12 required">
                                                    <div class="input-group date" data-provide="datepicker"
                                                         data-date-format="yyyy-mm-dd" data-autoclose="true">
                                                        <input type="text" class="form-control"
                                                               placeholder="{{ trans('general.select_date') }}"
                                                               name="checkin_at" id="checkin_at"
                                                               value="{{ old('checkin_at', date('Y-m-d')) }}">
                                                        <span class="input-group-addon">
                                                            <x-icon type="calendar" />
                                                        </span>
                                                    </div>
                                                    {!! $errors->first('checkin_at', '<span class="alert-msg" role="alert" aria-live="assertive"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Note -->
                                        <div class="form-group {{ $errors->has('note') ? 'error' : '' }}">
                                            <label for="note" class="col-md-3 control-label">
                                                {{ trans('general.notes') }}
                                            </label>
                                            <div class="col-md-8">
                                                <textarea class="col-md-6 form-control" id="note" @required($snipeSettings->require_checkinout_notes)
                                                name="note">{{ old('note', $asset->note) }}</textarea>
                                                {!! $errors->first('note', '<span class="alert-msg" role="alert" aria-live="assertive"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                            </div>
                                        </div>


                                        <x-input.file-upload name="file" />

                                        <!-- Custom fields -->
                                        @include("models/custom_fields_form", [
                                                'model' => $asset->model,
                                                'show_custom_fields_type' => 'checkin'
                                        ])


                    </div> <!--/.box-body-->
                </div> <!--/.box-body-->

                <x-redirect_submit_options
                        index_route="hardware.index"
                        :button_label="trans('general.checkin')"
                        :disabled_select="!$asset->model"
                        :options="[
                                'index' => trans('admin/hardware/form.redirect_to_all', ['type' => trans('general.assets')]),
                                'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.asset')]),
                                'target' => $target_option,
                               ]"
                />
                </form>

            </div>
        </div>
    </div>

@stop

@section('moar_scripts')
    <script nonce="{{ csrf_token() }}">
        const initializeRequestableToggle = function () {
            const deployableStatusIds = @json($deployable_status_ids);
            const statusSelect = document.getElementById('modal-statuslabel_types')
                ?? document.querySelector('select[name="status_id"]');
            const requestableWrapper = document.getElementById('requestable-wrapper');

            if (!statusSelect || !requestableWrapper) {
                return;
            }

            // Preserve the checkbox state when hiding: the server only applies the
            // checkbox value when the selected status is deployable, so a hidden
            // wrapper never lets us accidentally clear the asset's requestable
            // flag. Users who bounce the status between deployable and non-
            // deployable keep their original tick when they land back on a
            // deployable status.
            const toggleRequestable = function () {
                const selectedStatusValue = statusSelect.value;
                const selectedStatusId = Number.parseInt(selectedStatusValue, 10);
                const isDeployable = selectedStatusValue !== ''
                    && Number.isInteger(selectedStatusId)
                    && deployableStatusIds.includes(selectedStatusId);

                requestableWrapper.style.display = isDeployable ? '' : 'none';
            };

            statusSelect.addEventListener('change', toggleRequestable);

            if (window.jQuery) {
                window.jQuery(statusSelect).on('select2:select select2:clear', toggleRequestable);
            }

            toggleRequestable();
        };

        // Per-user localStorage preference for the requestable default on
        // checkin. Namespaced by user id so a shared browser session doesn't
        // leak one user's habit into another user's default. Only takes over
        // when the field wasn't repopulated from a validation-error redirect
        // (old() beats the stored preference). On submit we save whatever the
        // user actually chose, so the preference tracks their real habit.
        const initializeRequestablePreference = function () {
            const storageKey = 'snipeit.checkin.requestable_default.' + @json(auth()->id() ?? 'guest');
            const hadOldInput = @json((bool) old('requestable', false)) || @json(session()->has('_old_input.requestable'));
            const checkbox = document.getElementById('requestable');
            const form = checkbox ? checkbox.closest('form') : null;

            if (!checkbox || !form) {
                return;
            }

            if (!hadOldInput) {
                let stored = null;
                try {
                    stored = window.localStorage.getItem(storageKey);
                } catch (e) {
                    // localStorage may be unavailable (private mode, disabled).
                }
                if (stored === '1' || stored === '0') {
                    checkbox.checked = stored === '1';
                }
            }

            form.addEventListener('submit', function () {
                try {
                    window.localStorage.setItem(storageKey, checkbox.checked ? '1' : '0');
                } catch (e) {
                    // Non-fatal: preference just won't persist this time.
                }
            });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeRequestableToggle);
            document.addEventListener('DOMContentLoaded', initializeRequestablePreference);
        } else {
            initializeRequestableToggle();
            initializeRequestablePreference();
        }
    </script>
@stop

