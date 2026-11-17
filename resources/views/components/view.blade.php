@extends('layouts/default')

{{-- Page title --}}
@section('title')
 {{ $component->name }}
 {{ trans('general.component') }}
@parent
@stop

@section('header_right')
    <x-button.info-panel-toggle/>
@endsection

@section('content')
<x-container columns="2">
        @if ($snipe_component->deleted_at != '')
            <x-page-column>
                <x-callout type="warning" icon="warning" live="assertive">
                    {{ trans('general.component_deleted_warning') }}
                </x-callout>
            </x-page-column>
        @endif

        <x-page-column class="col-md-9 main-panel">
            <x-tabs>
                <x-slot:tabnav>

                    <x-tabs.nav-item
                            name="assigned"
                            icon_type="checkedout"
                            label="{{ trans('general.assigned') }}"
                            count="{{ $snipe_component->numCheckedOut() }}"
                    />

                    <x-tabs.files-tab :item="$snipe_component" count="{{ $snipe_component->uploads()->count() }}"/>
                    <x-tabs.orders-tab count="{{ $snipe_component->orderItemsCount() }}"/>
                    <x-tabs.history-tab count="{{ $snipe_component->history()->count() }}" :model="$snipe_component"/>
                    @if ($snipe_component->deleted_at == '')
                        <x-tabs.upload-tab :item="$snipe_component"/>
                    @endif

                </x-slot:tabnav>

                <x-slot:tabpanes>

                    <x-tabs.pane name="assigned">

                        <x-slot:table_header>
                            {{ trans('general.assigned') }}
                        </x-slot:table_header>

                        @can('checkin', $snipe_component)
                            <x-slot:bulkactions>
                                <x-table.bulk-actions
                                    name="componentCheckedOut"
                                    :action_route="route('components.bulkcheckin.show')"
                                    model_name="components_asset"
                                    :actions="[
                                        'checkin' => ['label' => trans('general.checkin')],
                                    ]"
                                />
                            </x-slot:bulkactions>
                        @endcan

                        <x-table
                            name="componentCheckedOut" :presenter="\App\Presenters\ComponentPresenter::checkedOut()"
                            :api_url="route('api.components.assets', $snipe_component)"
                        />

                    </x-tabs.pane>

                    <!-- start files tab pane -->
                    <x-tabs.pane name="files">
                        <x-table.files object_type="components" :object="$snipe_component"/>
                    </x-tabs.pane>

                    <!-- start orders tab pane -->
                    <x-tabs.pane name="orders">
                        <x-table.orders name="componentOrders" :route="route('api.order-items.index', ['item_type' => \App\Models\Component::class, 'item_id' => $snipe_component->id])"/>
                    </x-tabs.pane>

                    <!-- start history tab pane -->
                    <x-tabs.pane name="history">
                        <x-table.history name="componentHistory" :model="$snipe_component" :route="route('api.components.history', $snipe_component)"/>
                    </x-tabs.pane>

                </x-slot:tabpanes>
            </x-tabs>
        </x-page-column>
        <x-page-column class="col-md-3">

            <x-box class="side-box expanded">
                <x-info-panel :infoPanelObj="$snipe_component" img_path="{{ app('components_upload_url') }}" :qr_code_url="route('qr_code/common', ['object_type' => 'components', 'id' => $snipe_component->id])">

                    <x-slot:buttons>
                        @if ($snipe_component->deleted_at != '')
                            {{-- Soft-deleted: keep every action visually
                                 present but non-interactive so admins see
                                 what's unavailable while viewing the
                                 restorable record. x-button.edit hides
                                 itself on deleted rows, so render disabled
                                 stand-ins inline. --}}
                            @can('update', $snipe_component)
                                <a href="#" class="btn btn-sm btn-warning hidden-print disabled" data-tooltip="true" data-placement="top" data-title="{{ trans('general.component_deleted_warning') }}">
                                    <x-icon type="edit" class="fa-fw" />
                                </a>
                            @endcan
                            {{-- Clone works on deleted items so admins
                                 can spin up a replacement row without
                                 restoring the original. Component gets
                                 the normal active clone affordance. --}}
                            <x-button.clone :item="$snipe_component" :route="route('components.clone.create', $snipe_component->id)"/>
                            @can('checkout', $snipe_component)
                                <button type="button" class="btn btn-sm bg-maroon hidden-print disabled" data-tooltip="true" data-placement="top" data-title="{{ trans('general.component_deleted_warning') }}" disabled>
                                    <x-icon type="checkout" class="fa-fw" />
                                </button>
                            @endcan
                            @can('update', $snipe_component)
                                <button type="button" class="btn btn-sm btn-primary adjust-quantity disabled" data-tooltip="true" data-placement="top" data-title="{{ trans('general.component_deleted_warning') }}" disabled>
                                    <x-icon type="plus-minus" class="fa-fw" />
                                    <span class="sr-only">{{ trans('general.adjust_quantity') }}</span>
                                </button>
                            @endcan
                            <x-button.restore :item="$snipe_component" :route="route('components.restore', ['componentID' => $snipe_component->id])" />
                        @else
                            <x-button.edit :item="$snipe_component" :route="route('components.edit', $snipe_component->id)"/>
                            <x-button.clone :item="$snipe_component" :route="route('components.clone.create', $snipe_component->id)"/>
                            <x-button.checkout :item="$snipe_component" :route="route('components.checkout.show', $snipe_component->id)" />
                            @can('update', $snipe_component)
                                @php
                                    $lastOrder = $snipe_component->lastOrderDefaults();
                                    $adjustDisabledReason = $snipe_component->archived
                                        ? trans('general.archived_item_label', ['item_type' => trans('general.component')])
                                        : null;
                                @endphp
                                <button type="button"
                                    class="btn btn-sm btn-primary adjust-quantity {{ $adjustDisabledReason ? 'disabled' : '' }}"
                                    data-tooltip="true"
                                    title="{{ $adjustDisabledReason ?? trans('general.adjust_quantity') }}"
                                    @disabled($adjustDisabledReason !== null)
                                    data-adjust-url="{{ route('components.adjust-quantity', $snipe_component) }}"
                                    data-item-name="{{ e($snipe_component->name) }}"
                                    data-available="{{ (int) $snipe_component->numRemaining() }}"
                                    @if ($lastOrder && $lastOrder['unit_cost'] !== null) data-last-unit-cost="{{ $lastOrder['unit_cost'] }}" @endif
                                    @if ($lastOrder && $lastOrder['currency'] !== null) data-last-currency="{{ e($lastOrder['currency']) }}" @endif
                                >
                                    <x-icon type="plus-minus" class="fa-fw" />
                                    <span class="sr-only">{{ trans('general.adjust_quantity') }}</span>
                                </button>
                            @endcan
                            <x-button.delete :item="$snipe_component" />
                        @endif
                    </x-slot:buttons>

                </x-info-panel>
            </x-box>
        </x-page-column>
    </x-container>

@endsection



@section('moar_scripts')
    @if ($snipe_component->deleted_at == '')
        @can('files', $snipe_component)
            <x-modals.upload-file item-type="components" :item-id="$snipe_component->id" />
        @endcan

        @can('update', $snipe_component)
            <x-modals.adjust-quantity />
        @endcan
    @endif

    @include ('partials.bootstrap-table', ['exportFile' => 'component' . $snipe_component->name . '-export', 'search' => false])
@endsection
