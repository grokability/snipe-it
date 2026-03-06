@props([
    'infoPanelObj' => null,
    'img_path' => null,
    'snipeSettings' => \App\Models\Setting::getSettings()
])

<!-- start side info-box -->
<div class="box-header with-border" style="padding-top: 0;">

    @if (isset($buttons))
        <div class="row" style="padding-left: 10px">
            {{ $buttons }}
        </div>
    @endif

</div>

<div class="box-body box-profile">

    @if (($infoPanelObj->image) && ($img_path))
            <a href="{{ Storage::disk('public')->url($img_path.e($infoPanelObj->image)) }}" data-toggle="lightbox" data-type="image">
                <img src="{{ Storage::disk('public')->url($img_path.e($infoPanelObj->image)) }}" class="profile-user-img img-responsive img-thumbnail" alt="{{ $infoPanelObj->name }}" style="margin-bottom: 10px;">
            </a>
        <br>
    @endif


    @if ($infoPanelObj->present()->displayAddress)
        {!! nl2br($infoPanelObj->present()->displayAddress) !!}
        <br><br>
    @endif


    @if (isset($before_list))
        {{ $before_list }}
    @endif

    <ul class="list-group list-group-unbordered">


        {{ $slot }}

        <x-info-element icon_type="notes" title="{{ trans('general.notes') }}">
            {!! nl2br(Helper::parseEscapedMarkedownInline($infoPanelObj->notes)) !!}
        </x-info-element>

        @if ($infoPanelObj->serial)
            @can('viewKeys', $infoPanelObj)
                <x-info-element icon_type="number">
                    <x-copy-to-clipboard class="pull-right" copy_what="license_key">
                        <code>{{ $infoPanelObj->serial }}</code>
                    </x-copy-to-clipboard>
                </x-info-element>
            @endcan
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

        @if ($infoPanelObj->model_number)
            <x-info-element icon_type="number" title="{{ trans('general.model_no') }}">
                {{ trans('general.model_no') }}
                <x-copy-to-clipboard copy_what="model_number" class="pull-right">
                {{ $infoPanelObj->model_number }}
                </x-copy-to-clipboard>
            </x-info-element>
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

        @if ($infoPanelObj->purchase_cost)
            <x-info-element>
                <x-icon type="cost" class="fa-fw" title="{{ trans('general.unit_cost') }}" />
                {{ trans('general.unit_cost') }}

                @if ((isset($infoPanelObj->location)) && ($infoPanelObj->location->currency!=''))
                    {{ $infoPanelObj->location->currency }}
                @else
                    {{ $snipeSettings->default_currency }}
                @endif

                <x-copy-to-clipboard copy_what="purchase_cost" class="pull-right">
                    {{ Helper::formatCurrencyOutput($infoPanelObj->purchase_cost) }}
                </x-copy-to-clipboard>
            </x-info-element>

            @if (isset($infoPanelObj->qty))
                <x-info-element>
                    <x-icon type="cost" class="fa-fw" title="{{ trans('general.total_cost') }}" />
                    {{ trans('general.total_cost') }}

                    @if ((isset($infoPanelObj->location)) && ($infoPanelObj->location->currency!=''))
                        {{ $infoPanelObj->location->currency }}
                    @else
                        {{ $snipeSettings->default_currency }}
                    @endif

                    {{ Helper::formatCurrencyOutput($infoPanelObj->totalCostSum()) }}
                </x-info-element>
            @endif

        @endif

        @if ($infoPanelObj->order_number)
            <x-info-element icon_type="order" title="{{ trans('general.order_number') }}">
                <x-copy-to-clipboard copy_what="order_number" class="pull-right">
                    {{ $infoPanelObj->order_number }}
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


        @if ($infoPanelObj->company)
            <x-info-element icon_type="company" icon_color="{{ $infoPanelObj->company->tag_color }}" title="{{ trans('general.company') }}">
                {!!  $infoPanelObj->company->present()->nameUrl !!}
            </x-info-element>
        @endif

        @if ($infoPanelObj->category)
            <x-info-element icon_type="category" icon_color="{{ $infoPanelObj->category->tag_color }}" title="{{ trans('general.category') }}">
                {!!  $infoPanelObj->category->present()->formattedNameLink !!}
            </x-info-element>
        @endif

        @if ($infoPanelObj->category_type)
            <x-info-element icon_type="{{ $infoPanelObj->category_type }}" title="{{ trans('general.type') }}">
                {{ $infoPanelObj->category_type }}
            </x-info-element>
        @endif



        @if ($infoPanelObj->location)
            <x-info-element icon_type="location" icon_color="{{ $infoPanelObj->location->tag_color }}" title="{{ trans('general.location') }}">
                {!!  $infoPanelObj->location->present()->nameUrl !!}
            </x-info-element>
        @endif


        @if ($infoPanelObj->manager)
            <x-info-element icon_type="manager" title="{{ trans('admin/users/table.manager') }}">
                {!!  $infoPanelObj->manager->present()->nameUrl !!}
            </x-info-element>
        @endif


        @if ($infoPanelObj->fieldset)
            <x-info-element icon_type="fieldset" title="{{ trans('admin/custom_fields/general.fieldset_name') }}">
                {!!  $infoPanelObj->fieldset->present()->nameUrl !!}
            </x-info-element>
        @endif

        @if ($infoPanelObj->manufacturer)
            <x-info-element icon_type="manufacturer" title="{{ trans('general.manufacturer') }}">
                <strong>{{ trans('general.manufacturer') }}</strong>
            </x-info-element>

            <x-info-element class="subitem">
                {!!  $infoPanelObj->manufacturer->present()->formattedNameLink !!}
            </x-info-element>

            <x-info-element icon_type="phone" class="subitem" title="{{ trans('general.phone') }}">
                <x-info-element.phone>
                    {{ $infoPanelObj->manufacturer->support_phone }}
                </x-info-element.phone>
            </x-info-element>

            <x-info-element icon_type="email" class="subitem" title="{{ trans('general.email') }}">
                <x-info-element.email>
                    {{ $infoPanelObj->manufacturer->support_email }}
                </x-info-element.email>
            </x-info-element>

            <x-info-element icon_type="external-link" class="subitem" title="{{ trans('general.url') }}">
                <x-info-element.url>
                    {{ $infoPanelObj->manufacturer->url }}
                </x-info-element.url>
            </x-info-element>

            <x-info-element icon_type="external-link" class="subitem" title="{{ trans('admin/manufacturers/table.support_url') }}">
                <x-info-element.url>
                    {{ $infoPanelObj->manufacturer->support_url }}
                </x-info-element.url>
            </x-info-element>
        @endif


        @if ($infoPanelObj->supplier)
            <x-info-element icon_type="supplier" title="{{ trans('general.supplier') }}">
                <strong>{{ trans('general.supplier') }}</strong>
            </x-info-element>

            <x-info-element class="subitem">
                {!!  $infoPanelObj->supplier->present()->formattedNameLink !!}
            </x-info-element>

            <x-info-element icon_type="contact-card" class="subitem" title="{{ trans('admin/suppliers/table.contact') }}">
                {{ $infoPanelObj->supplier->contact }}
            </x-info-element>

            @if ($infoPanelObj->supplier->present()->displayAddress)
                <x-info-element class="subitem">
                    {!! nl2br($infoPanelObj->supplier->present()->displayAddress) !!}
                </x-info-element>
            @endif

            <x-info-element icon_type="phone" class="subitem" title="{{ trans('general.phone') }}">
                <x-info-element.phone title="{{ trans('general.phone') }}">
                    {{ $infoPanelObj->supplier->phone }}
                </x-info-element.phone>
            </x-info-element>

            <x-info-element icon_type="email" class="subitem" title="{{ trans('general.email') }}">
                <x-info-element.email>
                    {{ $infoPanelObj->supplier->email }}
                </x-info-element.email>
            </x-info-element>

            <x-info-element icon_type="external-link" class="subitem" title="{{ trans('general.url') }}">
                <x-info-element.url>
                    {{ $infoPanelObj->supplier->url }}
                </x-info-element.url>
            </x-info-element>

        @endif



        @if ((isset($infoPanelObj->parent)) && $infoPanelObj->parent))
            <x-info-element icon_type="parent" title="{{ trans('admin/locations/table.parent') }}">
                {{ $infoPanelObj->parent->display_name }}
            </x-info-element>
        @endif

        @if ($infoPanelObj->depreciation && $infoPanelObj->purchase_date)
            <x-info-element icon_type="depreciation" title="{{ trans('general.depreciation') }}">
                {!!  $infoPanelObj->depreciation->present()->nameUrl !!}
                ({{ $infoPanelObj->depreciation->months.' '.trans('general.months')}})
            </x-info-element>

            <x-info-element icon_type="depreciation-calendar" title="{{ trans('general.depreciates') }}">
                {{ Helper::getFormattedDateObject($infoPanelObj->depreciated_date(), 'date', false) }}
            </x-info-element>
        @endif

        @if ($infoPanelObj->eol)
            <x-info-element icon_type="eol" title="{{ trans('general.eol') }}">
                {{ $infoPanelObj->eol .' '.trans('general.months') }}
            </x-info-element>
        @endif


        <x-info-element icon_type="email" title="{{ trans('general.email') }}">
            <x-info-element.email title="{{ trans('general.email') }}">
                {{ $infoPanelObj->email }}
            </x-info-element.email>
        </x-info-element>

        @if ($infoPanelObj->phone)
            <x-info-element icon_type="phone" title="{{ trans('general.phone') }}">
                <x-info-element.phone>
                    {{ $infoPanelObj->phone }}
                </x-info-element.phone>
            </x-info-element>
        @endif

        @if ($infoPanelObj->fax)
            <x-info-element icon_type="fax" title="{{ trans('general.fax') }}">
                <x-info-element.phone>
                    {{ $infoPanelObj->fax }}
                </x-info-element.phone>
            </x-info-element>
        @endif

        <x-info-element icon_type="external-link" title="{{ trans('general.url') }}">
            <x-info-element.url>
                {{ $infoPanelObj->url }}
            </x-info-element.url>
        </x-info-element>

        <x-info-element icon_type="external-link" title="{{ trans('admin/manufacturers/table.support_url') }}">
            <x-info-element.url>
                {{ $infoPanelObj->support_url }}
            </x-info-element.url>
        </x-info-element>


        @if (($infoPanelObj->present()->displayAddress) && (config('services.google.maps_api_key')))

                <x-info-element>
                    <div class="text-center">
                        <img src="https://maps.googleapis.com/maps/api/staticmap?markers={{ urlencode($infoPanelObj->address.','.$infoPanelObj->city.' '.$infoPanelObj->state.' '.$infoPanelObj->country.' '.$infoPanelObj->zip) }}&size=500x300&maptype=roadmap&key={{ config('services.google.maps_api_key') }}" class="img-thumbnail img-responsive" style="width: 100%" alt="Map">
                    </div>
                </x-info-element>
        @endif

        @if ((($infoPanelObj->address!='') && ($infoPanelObj->city!='')) || ($infoPanelObj->state!='') || ($infoPanelObj->country!=''))
            <x-info-element>
                <a class="btn btn-sm btn-theme" href="https://maps.google.com/?q={{ urlencode($infoPanelObj->address.','. $infoPanelObj->city.','.$infoPanelObj->state.','.$infoPanelObj->country.','.$infoPanelObj->zip) }}" target="_blank">
                    {!! trans('admin/locations/message.open_map', ['map_provider_icon' => '<i class="fa-brands fa-google hidden-print" aria-hidden="true"></i>']) !!}
                    <x-icon type="external-link"/>
                </a>

                <a class="btn btn-sm btn-theme"  href="https://maps.apple.com/?q={{ urlencode($infoPanelObj->address.','. $infoPanelObj->city.','.$infoPanelObj->state.','.$infoPanelObj->country.','.$infoPanelObj->zip) }}" target="_blank">
                    {!! trans('admin/locations/message.open_map', ['map_provider_icon' => '<i class="fa-brands fa-apple hidden-print" aria-hidden="true"></i>']) !!}
                    <x-icon type="external-link"/>
                </a>
            </x-info-element>
        @endif


        @if ($infoPanelObj->months)
            <x-info-element title="{{ trans('general.months') }}">
                {{ $infoPanelObj->months }}
                {{ trans('general.months') }}
            </x-info-element>
        @endif


        @if ($infoPanelObj->depreciation_type)
            <x-info-element title="{{ trans('general.depreciation_type') }}">
                @if ($infoPanelObj->depreciation_type == 'amount')
                    {{ trans('general.depreciation_options.amount') }}
                @elseif ($infoPanelObj->depreciation_type == 'percent')
                    {{ trans('general.depreciation_options.amount') }}
                @endif
            </x-info-element>
        @endif



        @if ($infoPanelObj->purchase_date)
            <x-info-element>
                <x-icon type="calendar" class="fa-fw" title="{{ trans('general.purchase_date') }}" />
                {{ trans('general.purchased_plain') }}
                {{ Helper::getFormattedDateObject($infoPanelObj->purchase_date, 'datetime', false) }}
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

        @if (isset($infoPanelObj->requestable))
            <x-info-element title="{{ trans('general.requestable') }}">
            @if ($infoPanelObj->requestable == 1)
                <x-icon type="checkmark" class="fa-fw text-success" />
               {{ trans('admin/hardware/general.requestable') }}
            @else
                <x-icon type="x" class="fa-fw text-danger" />
                {{ trans('admin/hardware/general.requestable') }}
            @endif
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
                @if ($infoPanelObj->require_acceptance == 1)
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

</div>

<!-- end side info-box -->

