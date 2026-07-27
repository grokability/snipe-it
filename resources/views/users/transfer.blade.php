@extends('layouts/default')

@section('title')
    {{ trans('admin/users/general.transfer.title') }}
    @parent
@stop

@section('header_right')
    <x-button.info-panel-toggle hide-on-xs/>
@endsection

@section('content')

    <x-container columns="2">
        <x-page-column class="col-md-7">

            <x-form
                id="transfer_form"
                :route="route('users.transfer.store', $sourceUser)"
            >

                <x-box header="{{ trans('admin/users/general.transfer.heading', ['name' => $sourceUser->display_name]) }}">

                    <p>{{ trans('admin/users/general.transfer.intro') }}</p>

                    <x-input.user-select
                        :label="trans('admin/users/general.transfer.target_user')"
                        name="target_user_id"
                        :selected="old('target_user_id')"
                        :excludeId="$sourceUser->id"
                        :required="true"
                    />

                    @if ($assets->isNotEmpty())
                        <x-form.row
                            :label="trans('admin/users/general.transfer.assets')"
                            name="asset_ids"
                            input_div_class="col-md-9"
                        >
                            <x-slot:input>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th class="col-md-1">
                                                <input
                                                    type="checkbox"
                                                    aria-label="{{ trans('general.select_all') }}"
                                                    data-toggle="check-all"
                                                    checked
                                                />
                                            </th>
                                            <th>{{ trans('general.asset_tag') }}</th>
                                            <th>{{ trans('general.name') }}</th>
                                            <th>{{ trans('general.category') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($assets as $asset)
                                            <tr>
                                                <td>
                                                    <input
                                                        type="checkbox"
                                                        name="asset_ids[]"
                                                        value="{{ $asset->id }}"
                                                        aria-label="{{ $asset->asset_tag }}"
                                                        checked
                                                    />
                                                </td>
                                                <td>{{ $asset->asset_tag }}</td>
                                                <td>{{ $asset->name ?: ($asset->model->name ?? '') }}</td>
                                                <td>{{ $asset->model?->category?->name }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </x-slot:input>
                        </x-form.row>
                    @endif

                    @if ($accessoryCheckouts->isNotEmpty())
                        <x-form.row
                            :label="trans('admin/users/general.transfer.accessories')"
                            name="accessory_checkout_ids"
                            input_div_class="col-md-9"
                        >
                            <x-slot:input>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th class="col-md-1">
                                                <input
                                                    type="checkbox"
                                                    aria-label="{{ trans('general.select_all') }}"
                                                    data-toggle="check-all"
                                                    checked
                                                />
                                            </th>
                                            <th>{{ trans('general.name') }}</th>
                                            <th>{{ trans('general.category') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($accessoryCheckouts as $checkout)
                                            <tr>
                                                <td>
                                                    <input
                                                        type="checkbox"
                                                        name="accessory_checkout_ids[]"
                                                        value="{{ $checkout->id }}"
                                                        aria-label="{{ $checkout->accessory?->name }}"
                                                        checked
                                                    />
                                                </td>
                                                <td>{{ $checkout->accessory?->name }}</td>
                                                <td>{{ $checkout->accessory?->category?->name }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </x-slot:input>
                        </x-form.row>
                    @endif

                    @if ($licenseSeats->isNotEmpty())
                        <x-form.row
                            :label="trans('admin/users/general.transfer.licenses')"
                            name="license_seat_ids"
                            input_div_class="col-md-9"
                        >
                            <x-slot:input>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th class="col-md-1">
                                                <input
                                                    type="checkbox"
                                                    aria-label="{{ trans('general.select_all') }}"
                                                    data-toggle="check-all"
                                                    checked
                                                />
                                            </th>
                                            <th>{{ trans('general.name') }}</th>
                                            <th>{{ trans('general.category') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($licenseSeats as $seat)
                                            <tr @class(['text-muted' => ! $seat->license?->reassignable])>
                                                <td>
                                                    <input
                                                        type="checkbox"
                                                        name="license_seat_ids[]"
                                                        value="{{ $seat->id }}"
                                                        aria-label="{{ $seat->license?->name }}"
                                                        @checked($seat->license?->reassignable)
                                                        @disabled(! $seat->license?->reassignable)
                                                    />
                                                </td>
                                                <td>
                                                    {{ $seat->license?->name }}
                                                    @if ($seat->license && ! $seat->license->reassignable)
                                                        <span class="label label-default">{{ trans('admin/users/general.transfer.non_reassignable') }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $seat->license?->category?->name }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </x-slot:input>
                        </x-form.row>
                    @endif

                    <x-form.row
                        :label="trans('admin/users/general.transfer.note')"
                        name="note"
                        :help_text="trans('admin/users/general.transfer.note_help')"
                    >
                        <x-slot:input>
                            <textarea
                                id="note"
                                name="note"
                                class="form-control"
                                rows="3"
                                maxlength="1000"
                                aria-describedby="note-help"
                                required
                            >{{ old('note') }}</textarea>
                        </x-slot:input>
                    </x-form.row>

                </x-box>

                <x-slot:footer>
                    <a href="{{ route('users.show', $sourceUser) }}" class="btn btn-link">{{ trans('button.cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon type="checkout" class="fa-fw" />
                        {{ trans('admin/users/general.transfer.submit') }}
                    </button>
                </x-slot:footer>

            </x-form>

        </x-page-column>

        <x-page-column class="col-md-5">
            <livewire:checkout-target-panel type="assets" defaultTargetType="user" />
            <livewire:checkout-target-panel type="accessories" defaultTargetType="user" />
            <livewire:checkout-target-panel type="licenses" defaultTargetType="user" />
        </x-page-column>
    </x-container>

@stop
