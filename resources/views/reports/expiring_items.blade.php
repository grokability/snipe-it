@extends('layouts/default')

@section('title')
    {{ trans('general.expiring_items_report') }}
    @parent
@stop

@section('content')
    <x-container>
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs hidden-print">
                <li class="active">
                    <a href="#assets" data-toggle="tab">
                        <span class="hidden-lg hidden-md">
                            <x-icon type="assets" class="fa-2x"/>
                        </span>

                        <span class="hidden-xs hidden-sm">
                            {{ trans('general.assets') }}

                            @if ($assets_count > 0)
                                <span class="badge badge-secondary">
                                    {{ number_format($assets_count) }}
                                </span>
                            @endif
                        </span>
                    </a>
                </li>

                <li>
                    <a href="#licenses" data-toggle="tab">
                        <span class="hidden-lg hidden-md">
                            <x-icon type="licenses" class="fa-2x"/>
                        </span>

                        <span class="hidden-xs hidden-sm">
                            {{ trans('general.licenses') }}

                            @if ($licenses_count > 0)
                                <span class="badge badge-secondary">
                                    {{ number_format($licenses_count) }}
                                </span>
                            @endif
                        </span>
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane active" id="assets">
                    <x-box name="assets">
                        <x-table.assets
                                name="expiring"
                                :route="route('api.expiring-assets')"
                                :presenter="\App\Presenters\ExpiringItemsPresenter::assetsDataTableLayout()"
                        />
                    </x-box>
                </div>

                <div class="tab-pane" id="licenses">
                    <x-box name="licenses">
                        <x-table.licenses
                                name="expiring"
                                :route="route('api.expiring-licenses')"
                                :presenter="\App\Presenters\ExpiringItemsPresenter::licensesDataTableLayout()"
                        />
                    </x-box>
                </div>
            </div>
        </div>
    </x-container>
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', ['search' => true, 'show-export' => false,])
@endsection