@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.bulk_edit') }}
    @parent
@stop

{{-- Page content --}}
@section('content')
    <x-container class="col-md-8 col-md-offset-2">
        <x-form :route="route('accessories.bulkedit.store')">
            <x-box>
                <x-slot:header>
                    {{ trans_choice('general.bulk_edit_warn', count($accessories), ['count' => count($accessories), 'item' => trans('general.accessory'), 'items' => trans('general.accessories')]) }}
                </x-slot:header>

                <x-callout type="info" icon="tip" live="polite">
                    {{ trans('general.bulk_edit_help', ['items' => trans('general.accessories')]) }}
                </x-callout>

                <table class="table table-striped" style="margin-bottom: 25px; border-bottom: 1px solid light-dark(#eeeeee, #656464);">
                    <thead>
                        <tr>
                            <th>{{ trans('general.id') }}</th>
                            <th>{{ trans('general.name') }}</th>
                            <th>{{ trans('general.category') }}</th>
                            <th>{{ trans('general.location') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($accessories as $accessory)
                            <tr>
                                <td>{{ $accessory->id }}</td>
                                <td>{{ $accessory->name }}</td>
                                <td>{{ $accessory->category?->name }}</td>
                                <td>{{ $accessory->location?->display_name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @foreach ($ids as $id)
                    <input type="hidden" name="ids[]" value="{{ $id }}">
                @endforeach

                <x-input.company-select
                    :label="trans('general.company')"
                    name="company_id"
                    :selected="old('company_id', '')"
                />

                <x-input.category-select
                    :label="trans('general.category')"
                    name="category_id"
                    :selected="old('category_id', '')"
                    categoryType="accessory"
                />

                <x-input.manufacturer-select
                    :label="trans('general.manufacturer')"
                    name="manufacturer_id"
                    :selected="old('manufacturer_id', '')"
                />

                <x-input.location-select
                    :label="trans('general.location')"
                    name="location_id"
                    :selected="old('location_id', '')"
                />

                <x-input.supplier-select
                    :label="trans('general.default_supplier')"
                    name="default_supplier_id"
                    :selected="old('default_supplier_id', '')"
                />

                <x-input.minimum-quantity />

                <x-form.radio-row
                    :label="trans('admin/hardware/general.requestable')"
                    name="requestable"
                    :selected="old('requestable', '')"
                    :options="[
                        '' => trans('general.do_not_change'),
                        '1' => trans('general.yes'),
                        '0' => trans('general.no'),
                    ]"
                />

                <x-form.radio-row
                    :label="trans('general.archived')"
                    name="archived"
                    :selected="old('archived', '')"
                    :options="[
                        '' => trans('general.do_not_change'),
                        '1' => trans('general.yes'),
                        '0' => trans('general.no'),
                    ]"
                />

                <x-slot:customfooter>
                    <div class="box-footer text-right">
                        <a class="btn btn-link pull-left" href="{{ URL::previous() }}">{{ trans('button.cancel') }}</a>
                        <button type="submit" class="btn btn-success" id="submit-button">
                            <x-icon type="checkmark" /> {{ trans('general.save') }}
                        </button>
                    </div>
                </x-slot:customfooter>
            </x-box>
        </x-form>
    </x-container>
@stop
