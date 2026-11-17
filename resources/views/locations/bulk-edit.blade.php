@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.bulk_edit') }}
    @parent
@stop

{{-- Page content --}}
@section('content')

    <x-container columns="2">

        <div class="col-md-12">
            <x-callout type="info" icon="tip" live="assertive">
                {{ trans_choice('admin/locations/message.bulkedit.warn', count($locations), ['count' => count($locations)]) }}
            </x-callout>
        </div>

        <x-page-column class="col-md-9">

            <x-form :route="route('locations.bulkedit.store')">

                <x-box>

                    @foreach ($locations as $location)
                        <input type="hidden" name="ids[]" value="{{ $location->id }}">
                    @endforeach

                    {{-- Bulk company reassignment is superuser-only when
                         FMCS is on. See BulkLocationsController::buildUpdateArray
                         for the rationale (amplified blast radius vs.
                         single-location edit). --}}
                    @if ($snipeSettings->full_multiple_companies_support == '1' && auth()->user()->isSuperUser())
                        <x-input.company-select
                            :label="trans('general.company')"
                            name="company_id"
                            :selected="old('company_id')"
                        />
                        <x-form.checkbox-row
                            name="null_company_id"
                            :label="trans_choice('general.set_to_null', count($locations), ['selection_count' => count($locations)])"
                        />
                    @endif

                    {{-- excludeIds keeps the selected locations out of
                         the parent picker so an operator can't attempt
                         to set one of the batch as the new parent - see snipeit.js --}}
                    <x-input.location-select
                        :label="trans('admin/locations/table.parent')"
                        name="parent_id"
                        :selected="old('parent_id')"
                        :excludeIds="$locations->pluck('id')->all()"
                    />
                    <x-form.checkbox-row
                        name="null_parent_id"
                        :label="trans_choice('general.set_to_null', count($locations), ['selection_count' => count($locations)])"
                    />

                    <x-input.user-select
                        :label="trans('admin/users/table.manager')"
                        name="manager_id"
                        :selected="old('manager_id')"
                    />
                    <x-form.checkbox-row
                        name="null_manager_id"
                        :label="trans_choice('general.set_to_null', count($locations), ['selection_count' => count($locations)])"
                    />

                    <x-form.row
                        :label="trans('admin/locations/table.currency')"
                        name="currency"
                        input_div_class="col-md-3"
                        help_class="col-md-7 col-md-offset-3"
                        :help_text="trans('general.currency_manually_entered_help')"
                    >
                        <x-slot:input>
                            <x-input.currency-select
                                name="currency"
                                :selected="old('currency')"
                            />
                        </x-slot:input>
                    </x-form.row>
                    <x-form.checkbox-row
                        name="null_currency"
                        :label="trans_choice('general.set_to_null', count($locations), ['selection_count' => count($locations)])"
                    />

                    <x-form.row
                        :label="trans('general.state')"
                        name="state"
                        input_div_class="col-md-4"
                    />
                    <x-form.checkbox-row
                        name="null_state"
                        :label="trans_choice('general.set_to_null', count($locations), ['selection_count' => count($locations)])"
                    />

                    <x-form.row
                        :label="trans('general.country')"
                        name="country"
                        input_div_class="col-md-4"
                    >
                        <x-slot:input>
                            <x-input.country-select
                                name="country"
                                :selected="old('country')"
                            />
                        </x-slot:input>
                    </x-form.row>
                    <x-form.checkbox-row
                        name="null_country"
                        :label="trans_choice('general.set_to_null', count($locations), ['selection_count' => count($locations)])"
                    />

                    <x-form.row
                        :label="trans('general.notes')"
                        name="notes"
                        type="textarea"
                        :rows="4"
                        :placeholder="trans('general.placeholders.notes')"
                    />
                    <x-form.checkbox-row
                        name="null_notes"
                        :label="trans_choice('general.set_to_null', count($locations), ['selection_count' => count($locations)])"
                    />

                    <x-slot:customfooter>
                        <div class="box-footer text-right">
                            <a class="btn btn-link pull-left" href="{{ URL::previous() }}">{{ trans('button.cancel') }}</a>
                            <button type="submit" class="btn btn-success" id="submit-button">
                                <x-icon type="checkmark"/> {{ trans('general.save') }}
                            </button>
                        </div>
                    </x-slot:customfooter>
                </x-box>
            </x-form>

        </x-page-column>

        <x-page-column class="col-md-3">

            <x-box>
                <x-slot:header>
                    {{ trans_choice('admin/locations/message.bulkedit.show_selected', count($locations), ['count' => count($locations)]) }}
                </x-slot:header>

                <ul class="list-unstyled" style="max-height: 70vh; overflow-y: auto; margin-bottom: 0;">
                    @foreach ($locations as $location)
                        <li style="padding: 4px 0; border-bottom: 1px solid #f0f0f0;">
                            @if ($location->tag_color)
                                <x-icon type="square" class="fa-fw" style="color: {{ $location->tag_color }};"/>
                            @endif
                            {{ $location->name }}
                        </li>
                    @endforeach
                </ul>
            </x-box>

        </x-page-column>

    </x-container>

@stop
