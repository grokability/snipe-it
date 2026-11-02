@props([
    'infoPanelObj' => null,
    'img_path' => null,
    'qr_code_url' => null,
    'snipeSettings' => \App\Models\Setting::getSettings()
])

<!-- start side info-box -->
<div class="box-header with-border" style="padding-top: 0;">

    @if (isset($buttons))
        <div class="info-panel-actions hidden-print">
            {{ $buttons }}
        </div>
    @endif

</div>

<div class="box-body box-profile">

    @if (($img_path) && ($infoPanelObj->getImageUrl($img_path)))

        {{--<x-info-panel.image />--}}

        <div class="text-center">

            @if (method_exists($infoPanelObj, 'isSuperUser'))

                @if (($infoPanelObj->isSuperUser()))
                    <x-icon type="superadmin" class="fa-2x text-danger"/>
                    <div class="text-danger" style="font-weight: bold">
                        {{ strtolower(trans('general.superuser')) }}
                    </div>

                @elseif ($infoPanelObj->isAdmin())
                    <x-icon type="admin" class="fa-2x text-warning"/>
                    <div class="text-warning" style="font-weight: bold">
                        {{ strtolower(trans('general.admin_user')) }}
                    </div>
                @endif

            @endif

                <a href="{{ $infoPanelObj->getImageUrl($img_path) }}" data-toggle="lightbox" data-type="image">
                    <img src="{{ $infoPanelObj->getImageUrl($img_path) }}" class="img-responsive img-thumbnail" alt="{{ $infoPanelObj->name }}" style="max-width: min(100%, 300px); max-height: 300px; margin-bottom: 10px;">
            </a>
        </div>
        <br>
    @endif


    @if ($infoPanelObj->present()->displayAddress)
            <x-copy-to-clipboard class="pull-right" copy_what="address">
                <div style="word-break: break-word; padding: 0; margin: 0">
                    {!! nl2br($infoPanelObj->present()->displayAddress) !!}
                </div>
            </x-copy-to-clipboard>
            <br>
    @endif


    @if (isset($before_list))
        {{ $before_list }}
    @endif

    <ul class="list-group list-group-unbordered">


        {{ $slot }}

        @if ($infoPanelObj->notes)
            <x-info-element icon_type="notes" title="{{ trans('general.notes') }}">
                <x-copy-to-clipboard class="pull-right" copy_what="notes">{!! (new \Parsedown)->setSafeMode(true)->setBreaksEnabled(true)->line($infoPanelObj->notes) !!}</x-copy-to-clipboard>
            </x-info-element>
        @endif

        @if ($infoPanelObj->serial)
            @if(($infoPanelObj::class != "App\Models\License") || (Gate::allows('viewKeys', $infoPanelObj)))
                <x-info-element icon_type="number" title="{{ trans('general.serial_number') }}">
                    <x-copy-to-clipboard class="pull-right" copy_what="license_key">
                        <code>{{ $infoPanelObj->serial }}</code>
                    </x-copy-to-clipboard>
                </x-info-element>
            @endif
        @endif


        @if ($infoPanelObj->license_name)
            <x-info-element icon_type="contact-card" title="{{ trans('admin/licenses/form.to_name') }}">
                {{ trans('admin/licenses/form.to_name') }}
                {{ $infoPanelObj->license_name }}
            </x-info-element>
        @endif

        @if ($infoPanelObj->license_email)
            <x-info-element icon_type="email" title="{{ trans('admin/licenses/form.to_email') }}">
                {{ trans('admin/licenses/form.to_email') }}
                <x-info-element.email>
                    {{ $infoPanelObj->license_email }}
                </x-info-element.email>
            </x-info-element>
        @endif

        @if ($infoPanelObj->termination_date)
            <x-info-element icon_type="terminates" title="{{ trans('admin/licenses/form.termination_date') }}">
                {{ Helper::getFormattedDateObject($infoPanelObj->termination_date, 'date', false) }}
            </x-info-element>
        @endif

        @if ($infoPanelObj->expiration_date)
            <x-info-element icon_type="expiration" title="{{ trans('general.expires') }}">
                {{ Helper::getFormattedDateObject($infoPanelObj->expiration_date, 'date', false) }}
            </x-info-element>
        @endif

        @if ($infoPanelObj->model)
            <x-info-element icon_type="model" title="{{ trans('general.asset_model') }}">
                <x-copy-to-clipboard copy_what="asset_model" class="pull-right">
                    {!!  $infoPanelObj->model->present()->formattedNameLink !!}
                </x-copy-to-clipboard>
            </x-info-element>

            <!-- This is an asset, so look into the model for the model number -->
            <x-info-element icon_type="number" title="{{ trans('general.model_no') }}">
                {{ trans('general.model_no') }}
                <x-copy-to-clipboard copy_what="model_number" class="pull-right">
                    {{ $infoPanelObj->model->model_number }}
                </x-copy-to-clipboard>
            </x-info-element>

            <x-info-panel.mfg-fields :model="$infoPanelObj->model" />

        @elseif ($infoPanelObj->model_number)
            <!-- This is not an asset (with a model) so just grab it off the object -->
            <x-info-element icon_type="number" title="{{ trans('general.model_no') }}">
                {{ trans('general.model_no') }}
                <x-copy-to-clipboard copy_what="model_number" class="pull-right">
                    {{ $infoPanelObj->model_number }}
                </x-copy-to-clipboard>
            </x-info-element>
        @endif

        @if ($infoPanelObj instanceof \App\Models\AssetModel)
            <x-info-panel.mfg-fields :model="$infoPanelObj" />
        @endif

        @if ($infoPanelObj->item_no)
            <x-info-element icon_type="number" title="{{ trans('admin/consumables/general.item_no') }}">
                {{ trans('admin/consumables/general.item_no') }}
                <x-copy-to-clipboard copy_what="item_no" class="pull-right">
                {{ $infoPanelObj->item_no }}
                </x-copy-to-clipboard>
            </x-info-element>
        @endif

        @if ($infoPanelObj->min_amt)
            <x-info-element>
                <x-icon type="min-qty" class="fa-fw" title="{{ trans('general.min_amt') }}" />
                {{ trans('general.min_amt') }} {{ $infoPanelObj->min_amt }}
            </x-info-element>
        @endif

        @if (method_exists($infoPanelObj, 'numCheckedOut'))
            <x-info-element icon_type="checkedout" title="{{ trans('general.checked_out') }}">
                {{ (int) $infoPanelObj->numCheckedOut() }}
                {{ trans('general.checked_out') }}
            </x-info-element>
        @endif

        @if (method_exists($infoPanelObj, 'numRemaining'))
            <x-info-element icon_type="available" class="{{ ($infoPanelObj->numRemaining()) <= $infoPanelObj->min_amt ? 'text-danger' : 'text-success' }}" title="{{ trans('general.remaining') }}">
                 {{ $infoPanelObj->numRemaining() }}
                {{ trans('general.remaining') }}
            </x-info-element>
        @endif

        {{-- Bulk-fulfill entry point. Requestable::bulkFulfillmentLink()
             gates on: (a) type is one of the five bulk-eligible ones,
             (b) caller can checkout the type, (c) >=2 open requests,
             (d) stock available. Returns null when any gate fails so
             the info-panel row is skipped without a Blade-side check. --}}
        @if (method_exists($infoPanelObj, 'bulkFulfillmentLink') && $bulkFulfillmentLink = $infoPanelObj->bulkFulfillmentLink())
            <x-info-element icon_type="fulfill_multiple" title="{{ trans('admin/hardware/general.fulfill_multiple') }}">
                <a href="{{ $bulkFulfillmentLink['url'] }}">
                    {{ trans_choice('admin/hardware/general.open_requests_count', $bulkFulfillmentLink['count'], ['count' => $bulkFulfillmentLink['count']]) }}
                </a>
            </x-info-element>
        @endif

        @php
            // Assets own purchase_cost as a canonical column on the parent
            // row and Accessories / Consumables / Components moved that
            // concept off the parent onto per-transaction OrderItem lines,
            // so read the parent's own value first and let the
            // Order-derived fallback fill in for the post-Orders inventory
            // types where the parent no longer carries a purchase_cost
            // column. See GH #19572.
            $lastOrderInfoPanel = method_exists($infoPanelObj, 'lastOrderDefaults')
                ? $infoPanelObj->lastOrderDefaults()
                : null;
            $unitCost = $infoPanelObj->purchase_cost ?? $lastOrderInfoPanel['unit_cost'] ?? null;
            $unitCostCurrency = $lastOrderInfoPanel['currency']
                ?? ($infoPanelObj->location->currency ?? null)
                ?: $snipeSettings->default_currency;
        @endphp
        @if ($unitCost !== null && $unitCost !== '')
            <x-info-element>
                <x-icon type="cost" class="fa-fw" title="{{ trans('general.unit_cost') }}" />
                {{ trans('general.unit_cost') }}
                {{ $unitCostCurrency }}
                <x-copy-to-clipboard copy_what="purchase_cost" class="pull-right">
                    {{ Helper::formatCurrencyOutput($unitCost) }}
                </x-copy-to-clipboard>
            </x-info-element>
        @endif

        {{-- Total cost is independent of the Last Unit Cost row. --}}
        {{-- Historical acquisitions may have carried prices even if --}}
        {{-- the most recent one didn't, so the total_cost row shows --}}
        {{-- whenever totalCostSumByCurrency has anything to sum. --}}
        @if (isset($infoPanelObj->qty) && method_exists($infoPanelObj, 'totalCostSumByCurrency'))
            @php $totalsByCurrency = $infoPanelObj->totalCostSumByCurrency(); @endphp
            @if (count($totalsByCurrency) === 1)
                @php $currency = array_key_first($totalsByCurrency); @endphp
                <x-info-element>
                    <x-icon type="cost" class="fa-fw" title="{{ trans('general.total_cost') }}"/>
                    {{ trans('general.total_cost') }}
                    {{ $currency !== '' ? $currency : $snipeSettings->default_currency }}
                    <x-copy-to-clipboard copy_what="total_cost" class="pull-right">
                        {{ Helper::formatCurrencyOutput($totalsByCurrency[$currency]) }}
                    </x-copy-to-clipboard>
                </x-info-element>
            @elseif (count($totalsByCurrency) > 1)
                <x-info-element>
                    <x-icon type="cost" class="fa-fw" title="{{ trans('general.total_cost') }}"/>
                    {{ trans('general.total_cost') }}
                    {{ count($totalsByCurrency) }} {{ trans('general.currencies') }}
                    <a class="pull-right js-copy-link" style="font-size: 16px; margin-right: 3px;" type="button" data-toggle="collapse" data-target="#totalCostBreakdown" aria-expanded="false" aria-controls="totalCostBreakdown">
                        <x-icon type="plus" class="fa-fw"/>
                    </a>
                </x-info-element>
                <span class="collapse" id="totalCostBreakdown">
                    <x-info-element class="subitem well well-sm">
                        @foreach ($totalsByCurrency as $currency => $amount)
                            <div>
                                {{ $currency !== '' ? $currency : $snipeSettings->default_currency }}
                                {{ Helper::formatCurrencyOutput($amount) }}
                            </div>
                        @endforeach
                    </x-info-element>
                </span>
            @endif
        @endif

        {{-- Skip on Asset instances. An asset is 1:1 with a --}}
        {{-- transaction, so "ordered N times" is always 1 and --}}
        {{-- the Orders table IS the record of the acquisition. --}}
        {{-- Applies to Accessory/Consumable/Component (multi-order --}}
        {{-- inventory) and to AssetModel (asset TYPE, aggregating --}}
        {{-- distinct orders across all its Asset instances). --}}
        @if (method_exists($infoPanelObj, 'ordersCount') && ! ($infoPanelObj instanceof \App\Models\Asset))
            @php $ordersCount = $infoPanelObj->ordersCount(); @endphp
            @if ($ordersCount > 0)
                <x-info-element>
                    <x-icon type="order" class="fa-fw" title="{{ trans('general.total_orders') }}"/>
                    {{ trans('general.total_orders') }}
                    <span class="pull-right">{{ $ordersCount }}</span>
                </x-info-element>
            @endif
        @endif

        {{-- Accessory/Consumable/Component override getOrderNumberAttribute
             to return null so this guard skips — a single order_number
             on many-batches inventory is misleading; per-adjustment order
             numbers live on QuantityAdjust action_log entries. Assets
             are unaffected; a single order_number per unit is real. --}}
        @if ($infoPanelObj->order_number)
            <x-info-element icon_type="order" title="{{ trans('general.order_number') }}">
                <x-copy-to-clipboard copy_what="order_number" class="pull-right">
                    @if ($infoPanelObj::class == "App\Models\Asset")
                        <a href="{{ route('hardware.index', ['order_number' => $infoPanelObj->order_number]) }}">{{ $infoPanelObj->order_number }}</a>
                    @else
                        {{ $infoPanelObj->order_number }}
                    @endif
                </x-copy-to-clipboard>
            </x-info-element>
        @endif

        @if ($infoPanelObj->purchase_order)
            <x-info-element icon_type="purchase_order" title="{{ trans('admin/licenses/form.purchase_order') }}">
                <x-copy-to-clipboard copy_what="purchase_order" class="pull-right">
                {{ $infoPanelObj->purchase_order }}
                </x-copy-to-clipboard>
            </x-info-element>
        @endif


        @if ($infoPanelObj->companies)
            @if ($infoPanelObj->companies->count() > 1)
                <x-info-element icon_type="company" title="{{ trans('general.companies') }}">
                    {{ trans('general.companies') }}
                    <x-info-element class="subitem">
                        <x-copy-to-clipboard class="pull-right" copy_what="companies">
                            @foreach ($infoPanelObj->companies as $company)
                                {!!  $company->present()->formattedNameLink !!}<br>
                            @endforeach
                        </x-copy-to-clipboard>
                    </x-info-element>
                </x-info-element>
            @elseif ($infoPanelObj->companies->isNotEmpty())
                <x-info-element icon_type="company" icon_color="{{ $infoPanelObj->companies->first()->tag_color }}" title="{{ trans('general.company') }}">
                    <x-copy-to-clipboard class="pull-right" copy_what="company">
                        {!!  $infoPanelObj->companies->first()->present()->nameUrl !!}
                    </x-copy-to-clipboard>
                </x-info-element>
            @endif

        @elseif ($infoPanelObj->company)
            <x-info-element icon_type="company" icon_color="{{ $infoPanelObj->company->tag_color }}" title="{{ trans('general.company') }}">
                <x-copy-to-clipboard class="pull-right" copy_what="company">
                    {!!  $infoPanelObj->company->present()->nameUrl !!}
                </x-copy-to-clipboard>
            </x-info-element>
        @endif

        @if ($infoPanelObj->department)
            <x-info-element icon_type="department" icon_color="{{ $infoPanelObj->department->tag_color }}" title="{{ trans('general.department') }}">
                <x-copy-to-clipboard class="pull-right" copy_what="department">
                    {!!  $infoPanelObj->department->present()->nameUrl !!}
                </x-copy-to-clipboard>
            </x-info-element>
        @endif

        @if ($infoPanelObj->category)
            <x-info-element icon_type="category" icon_color="{{ $infoPanelObj->category->tag_color }}" title="{{ trans('general.category') }}">
                <x-copy-to-clipboard class="pull-right" copy_what="category">{!!  $infoPanelObj->category->present()->nameUrl !!}</x-copy-to-clipboard>
            </x-info-element>
        @endif


        @if ($infoPanelObj->category_type)
            <x-info-element icon_type="{{ $infoPanelObj->category_type }}" title="{{ trans('general.type') }}">
                <x-copy-to-clipboard class="pull-right" copy_what="category_type">{{ $infoPanelObj->category_type }}</x-copy-to-clipboard>
            </x-info-element>
        @endif


        @if ($infoPanelObj->location)
            <x-info-element icon_type="location" icon_color="{{ $infoPanelObj->location->tag_color }}" title="{{ trans('general.location') }}">
                <x-copy-to-clipboard class="pull-right" copy_what="location">{!!  $infoPanelObj->location->present()->nameUrl !!}</x-copy-to-clipboard>
            </x-info-element>
        @endif


        @if ($infoPanelObj->manager)
            <x-info-element icon_type="manager" title="{{ trans('admin/users/table.manager') }}">
                <x-copy-to-clipboard class="pull-right" copy_what="manager">{!!  $infoPanelObj->manager->present()->nameUrl !!}</x-copy-to-clipboard>
            </x-info-element>
        @endif


        @if ($infoPanelObj->fieldset)
            <x-info-element icon_type="fieldset" title="{{ trans('admin/custom_fields/general.fieldset_name') }}">
                {!!  $infoPanelObj->fieldset->present()->nameUrl !!}
            </x-info-element>
        @endif



        <x-info-panel.supplier :infoPanelObj="$infoPanelObj"/>
        <x-info-panel.manufacturer :infoPanelObj="$infoPanelObj" :manufacturer="($infoPanelObj->manufacturer ?? $infoPanelObj->model?->manufacturer)"/>

        @if (($infoPanelObj instanceof \App\Models\Location) && (isset($infoPanelObj->parent)) && ($infoPanelObj->parent))
            @php
                $locationAncestors = [];
                $ancestorCursor = $infoPanelObj->parent;

                while ($ancestorCursor) {
                    array_unshift($locationAncestors, $ancestorCursor);
                    $ancestorCursor = $ancestorCursor->parent;
                }
            @endphp
            <x-info-element icon_type="parent" title="{{ trans('admin/locations/table.parent') }}">
                @foreach ($locationAncestors as $ancestor)
                    <a href="{{ route('locations.show', $ancestor->id) }}">{{ $ancestor->display_name }}</a>@if (! $loop->last) &rsaquo; @endif
                @endforeach
            </x-info-element>
        @endif

        @if ($infoPanelObj->depreciation && $infoPanelObj->purchase_date)
            <x-info-element icon_type="depreciation" title="{{ trans('general.depreciation') }}">
                {!!  $infoPanelObj->depreciation->present()->nameUrl !!}
                ({{ trans_choice('general.months_plural', $infoPanelObj->depreciation->months) }})
            </x-info-element>

            <x-info-element icon_type="depreciation-calendar" class="{{ $infoPanelObj->depreciationProgressPercent() > 90 ? 'text-danger' : '' }}" title="{{ trans('admin/hardware/form.fully_depreciated') }}">
                {{ trans('admin/hardware/form.fully_depreciated') }}
                {{ Helper::getFormattedDateObject($infoPanelObj->depreciated_date(), 'date', false) }}
                -
                <span class="text-muted">{{ Carbon::parse($infoPanelObj->depreciated_date())->diffForHumans(['parts' => 2]) }}</span>
                @if (method_exists($infoPanelObj, 'depreciationProgressPercent'))
                    <span class="text-muted">
                        ({{ (int) round($infoPanelObj->depreciationProgressPercent()) }}%)
                    </span>
                @endif
            </x-info-element>
        @endif


        @if ($infoPanelObj->eol)
            <x-info-element icon_type="eol" title="{{ trans('general.device_eol') }}">
                {{ $infoPanelObj->eol .' '.trans('general.months') }}
            </x-info-element>
        @endif

        @if ($infoPanelObj->asset_eol_date)
            <x-info-element icon_type="depreciation-calendar" title="{{ trans('general.device_eol') }}">
                {{ trans('general.device_eol') }}
                {{ Helper::getFormattedDateObject($infoPanelObj->asset_eol_date, 'date', false) }}
                -
                <span class="text-muted">{{ Carbon::parse($infoPanelObj->asset_eol_date)->diffForHumans(['parts' => 2]) }}</span>

            </x-info-element>
        @endif


        @if ($infoPanelObj->email)
            <x-info-element icon_type="email" title="{{ trans('general.email') }}">
                <x-copy-to-clipboard class="pull-right" copy_what="email">
                    <x-info-element.email title="{{ trans('general.email') }}">
                        {{ $infoPanelObj->email }}
                    </x-info-element.email>
                </x-copy-to-clipboard>
            </x-info-element>
        @endif

        @if (isset($infoPanelObj->support_email) && $infoPanelObj->support_email)
            <x-info-element icon_type="email" title="{{ trans('admin/manufacturers/table.support_email') }}">
                <x-copy-to-clipboard class="pull-right" copy_what="support_email">
                    <x-info-element.email>
                        {{ $infoPanelObj->support_email }}
                    </x-info-element.email>
                </x-copy-to-clipboard>
            </x-info-element>
        @endif


        @if ($infoPanelObj->phone)
            <x-info-element icon_type="phone" title="{{ trans('general.phone') }}">
                <x-copy-to-clipboard class="pull-right" copy_what="phone">
                    <x-info-element.phone>
                    {{ $infoPanelObj->phone }}
                    </x-info-element.phone>
                </x-copy-to-clipboard>
            </x-info-element>
        @endif

        @if (isset($infoPanelObj->support_phone) && $infoPanelObj->support_phone)
            <x-info-element icon_type="phone" title="{{ trans('admin/manufacturers/table.support_phone') }}">
                <x-copy-to-clipboard class="pull-right" copy_what="support_phone">
                    <x-info-element.phone>
                        {{ $infoPanelObj->support_phone }}
                    </x-info-element.phone>
                </x-copy-to-clipboard>
            </x-info-element>
        @endif

        @if ($infoPanelObj->mobile)
            <x-info-element icon_type="mobile" title="{{ trans('admin/users/table.mobile') }}">
                <x-copy-to-clipboard class="pull-right" copy_what="mobile">
                    <x-info-element.phone>
                        {{ $infoPanelObj->mobile }}
                    </x-info-element.phone>
                </x-copy-to-clipboard>
            </x-info-element>
        @endif

        @if ($infoPanelObj->fax)
            <x-info-element icon_type="fax" title="{{ trans('general.fax') }}">
                <x-info-element.phone>
                    {{ $infoPanelObj->fax }}
                </x-info-element.phone>
            </x-info-element>
        @endif

        @if ($infoPanelObj->url)
            <x-info-element icon_type="external-link" title="{{ trans('general.url') }}">
                <x-info-element.url>
                    {{ $infoPanelObj->url }}
                </x-info-element.url>
            </x-info-element>
        @endif

        @if (isset($infoPanelObj->support_url) && $infoPanelObj->support_url)
            <x-info-element icon_type="external-link" title="{{ trans('admin/manufacturers/table.support_url') }}">
                <x-info-element.url>
                    {{ $infoPanelObj->support_url }}
                </x-info-element.url>
            </x-info-element>
        @endif

        @if (isset($infoPanelObj->warranty_lookup_url) && $infoPanelObj->warranty_lookup_url)
            <x-info-element icon_type="external-link" title="{{ trans('admin/manufacturers/table.warranty_lookup_url') }}">
                <x-info-element.url>
                    {{ $infoPanelObj->warranty_lookup_url }}
                </x-info-element.url>
            </x-info-element>
        @endif

        @if (isset($infoPanelObj->website) && $infoPanelObj->website)
            <x-info-element icon_type="external-link" title="{{ trans('general.website') }}">
                <x-info-element.url>
                    {{ $infoPanelObj->website }}
                </x-info-element.url>
            </x-info-element>
        @endif


        @if (($infoPanelObj->present()->displayAddress) && (config('services.google.maps_api_key')))

                <x-info-element class="hidden-print">
                    <div class="text-center">
                        <img src="https://maps.googleapis.com/maps/api/staticmap?markers={{ urlencode($infoPanelObj->address.','.$infoPanelObj->city.' '.$infoPanelObj->state.' '.$infoPanelObj->country.' '.$infoPanelObj->zip) }}&size=500x300&maptype=roadmap&key={{ config('services.google.maps_api_key') }}" class="img-thumbnail img-responsive" style="width: 100%" alt="Map">
                    </div>
                </x-info-element>
        @endif

        @if ((($infoPanelObj->address!='') && ($infoPanelObj->city!='')) || ($infoPanelObj->state!='') || ($infoPanelObj->country!=''))
            <x-info-element>
                <a class="btn btn-sm btn-theme" href="https://maps.google.com/?q={{ urlencode($infoPanelObj->address.','. $infoPanelObj->city.','.$infoPanelObj->state.','.$infoPanelObj->country.','.$infoPanelObj->zip) }}" target="_blank">
                    <x-icon type="google" class="hidden-print"/>
                    <x-icon type="external-link"/>
                </a>

                <a class="btn btn-sm btn-theme"  href="https://maps.apple.com/?q={{ urlencode($infoPanelObj->address.','. $infoPanelObj->city.','.$infoPanelObj->state.','.$infoPanelObj->country.','.$infoPanelObj->zip) }}" target="_blank">
                    <x-icon type="apple" class="hidden-print"/>
                    <x-icon type="external-link"/>
                </a>
            </x-info-element>
        @endif


        @if ($infoPanelObj->months)
            <x-info-element icon_type="eol" title="{{ trans('general.depreciation') }}">
                {{ $infoPanelObj->months }}
                {{ trans('general.months') }}
            </x-info-element>
        @endif


        @if ($infoPanelObj->depreciation_type)
            <x-info-element icon="fa-solid fa-arrow-trend-down fa-fw" title="{{ trans('general.depreciation_type') }}">
                @if ($infoPanelObj->depreciation_type == 'amount')
                    {{ trans('general.depreciation_options.amount') }}
                @elseif ($infoPanelObj->depreciation_type == 'percent')
                    {{ trans('general.depreciation_options.amount') }}
                @endif
            </x-info-element>
        @endif



        @php
            // Same canonical-parent-first rule as unit cost above. Assets
            // keep purchase_date on the parent as the source of truth and
            // the AssetObserver doesn't sync it back to Order because
            // multiple assets can share one Order row (dedupe by
            // order_number + supplier + company), so on that Order row
            // purchase_date is a write-once snapshot from the first
            // sibling asset's create. Reading the parent's canonical
            // column keeps the info-panel in step with the edit form.
            // Post-Orders inventory types (Accessory / Consumable /
            // Component) have no parent purchase_date column, so their
            // Order-derived fallback still wins. See GH #19572.
            $displayPurchaseDate = $infoPanelObj->purchase_date
                ?: ($lastOrderInfoPanel['purchase_date'] ?? null);
        @endphp
        @if ($displayPurchaseDate)
            <x-info-element>
                <x-icon type="calendar" class="fa-fw" title="{{ trans('general.purchase_date') }}" />
                {{ trans('general.purchased_plain') }}
                {{ Helper::getFormattedDateObject($displayPurchaseDate, 'date', false) }} -
                <span class="text-muted">{{ Carbon::parse($displayPurchaseDate)->diffForHumans(['parts' => 2]) }}</span>
            </x-info-element>
        @endif


        @if (isset($infoPanelObj->maintained))
            <x-info-element title="{{ trans('general.maintained') }}">
            @if ($infoPanelObj->maintained == 1)
                <x-icon type="checkmark" class="fa-fw text-success" />
                {{ trans('admin/licenses/form.maintained') }}
            @else
                <x-icon type="x" class="fa-fw text-danger" />
                {{ trans('admin/licenses/form.maintained') }}
            @endif
            </x-info-element>
        @endif

        @if (isset($infoPanelObj->reassignable))
            <x-info-element title="{{ trans('admin/licenses/form.reassignable') }}">
            @if ($infoPanelObj->reassignable == 1)
                <x-icon type="checkmark" class="fa-fw text-success" />
                {{ trans('admin/licenses/form.reassignable') }}
            @else
                <x-icon type="x" class="text-danger" />
                {{ trans('admin/licenses/form.reassignable') }}
            @endif
            </x-info-element>
        @endif

        @if (isset($infoPanelObj->byod))
            <x-info-element title="{{ trans('general.byod') }}">
                @if ($infoPanelObj->byod == 1)
                <x-icon type="checkmark" class="fa-fw text-success" />
            @else
                <x-icon type="x" class="fa-fw text-danger" />
            @endif
                {{ trans('general.byod') }}
            </x-info-element>
        @endif

        @if (isset($infoPanelObj->requestable))
            <x-info-element title="{{ trans('general.requestable') }}">
                @if ($infoPanelObj->requestable == 1)
                    <x-icon type="checkmark" class="fa-fw text-success"/>
                @else
                    <x-icon type="x" class="fa-fw text-danger"/>
                @endif
                {{ trans('admin/hardware/general.requestable') }}
            </x-info-element>
        @endif

        @if (isset($infoPanelObj->use_default_eula))
            <x-info-element>
                @if ($infoPanelObj->eula_text=='')
                    <x-icon type="checkmark" class="fa-fw text-success" title="{{ trans('general.yes') }}" />
                    {{ trans('admin/settings/general.default_eula_text') }}
                @else
                    <x-icon type="checkmark" class="fa-fw text-success" title="{{ trans('general.yes') }}" />
                    {{ trans('admin/categories/general.eula_text') }}
                @endif

            </x-info-element>
        @endif

        @if (isset($infoPanelObj->require_acceptance))
            <x-info-element>
                @if ($infoPanelObj->require_acceptance == 1)
                    <x-icon type="checkmark" class="fa-fw text-success" title="{{ trans('general.yes') }}" />
                @else
                    <x-icon type="x" class="fa-fw text-danger" title="{{ trans('general.no') }}" />
                @endif
                    {{ trans('admin/categories/general.require_acceptance') }}
            </x-info-element>
        @endif



        @if (isset($infoPanelObj->alert_on_response))
            <x-info-element>
                @if ($infoPanelObj->alert_on_response)
                    <x-icon type="checkmark" class="fa-fw text-success"  title="{{ trans('general.yes') }}"/>
                @else
                    <x-icon type="x" class="fa-fw text-danger"  title="{{ trans('general.no') }}"/>
                @endif
                    {{ trans('admin/categories/general.email_to_initiator') }}
            </x-info-element>
        @endif


        @if ($infoPanelObj->tag_color)
            <x-info-element>
                <x-icon type="square" class="fa-fw" style="color: {{ $infoPanelObj->tag_color }}" title="{{ trans('general.tag_color') }}"/>
                {{ $infoPanelObj->tag_color }}
            </x-info-element>
        @endif

        @if ($infoPanelObj->adminuser)
            <x-info-element title="{{ trans('general.created_by') }}">
                <span class="text-muted">
                    <x-icon type="user" class="fa-fw" title="{{ trans('general.created_by') }}" />
                        {{ trans('general.created_by') }}
                    {!!  $infoPanelObj->adminuser->present()->formattedNameLink !!}

                </span>
            </x-info-element>
        @endif


        @if ($infoPanelObj->created_at)
            <x-info-element>
                <span class="text-muted">
                    <x-icon type="calendar" class="fa-fw" title="{{ trans('general.created_at') }}" />
                    {{ trans('general.created_plain') }}
                    {{ Helper::getFormattedDateObject($infoPanelObj->created_at, 'datetime', false) }}
                </span>
            </x-info-element>
        @endif

        @if ($infoPanelObj->updated_at)
            <x-info-element>
                <span class="text-muted">
                    <x-icon type="calendar" class="fa-fw" title="{{ trans('general.updated_at') }}" />
                    {{ trans('general.updated_plain') }}
                    {{ Helper::getFormattedDateObject($infoPanelObj->updated_at, 'datetime', false) }}
                </span>
            </x-info-element>
        @endif

        @if ($infoPanelObj->deleted_at)
            <x-info-element>
                <span class="text-muted">
                    <x-icon type="deleted-date" class="fa-fw" title="{{ trans('general.deleted_at') }}" />
                    {{ trans('general.deleted_plain') }}
                    {{ Helper::getFormattedDateObject($infoPanelObj->deleted_at, 'datetime', false) }}
                </span>
            </x-info-element>
        @endif


    </ul>
        @if (isset($after_list))
            {{ $after_list }}
        @endif

        @if ($qr_code_url && $snipeSettings->isQrEnabled())
            <div class="col-md-12 text-center asset-qr-img" style="padding-top: 15px;">
                <img src="{{ $qr_code_url }}" class="img-thumbnail" style="height: 150px; width: 150px; margin-right: 10px;" alt="QR code for {{ $infoPanelObj->name }}">
            </div>
        @endif

</div>

<!-- end side info-box -->

