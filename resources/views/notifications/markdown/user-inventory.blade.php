@component('mail::message')

{{ trans('general.reminder_checked_out_items', array('reply_to_name' => config('mail.reply_to.name'), 'reply_to_address' => config('mail.reply_to.address')))}}

@component('mail::table')

@if ($assets->count() > 0)

## {{ $assets->count() }} {{ trans('general.assets') }}

<table width="100%">
    <tr>
        <th align="left">{{ trans('mail.asset_tag') }}</th>
        <th align="left">{{ trans('mail.name') }} </th>
        <th align="left">{{ trans('general.category') }}</th>
        <th align="left">{{ trans('admin/hardware/form.model') }}</th>
        <th align="left">{{ trans('admin/hardware/table.location') }}</th>
        <th align="left">{{ trans('admin/hardware/form.serial') }}</th>
        <th align="left">{{ trans('admin/hardware/form.checkout_date') }}</th>
    </tr>


@foreach($assets as $asset)
<tr>
    <td> {{ $asset->asset_tag }} </td>
    <td>{{ $asset->display_name }}</td>
    <td> {{ $asset->model->category->name }}</td>
    <td> {{ $asset->model->name }}</td>
    <td> {{ ($asset->location) ? $asset->location->name : '' }} </td>
    <td> {{ $asset->serial }} </td>
    <td> {{ $asset->last_checkout }}
    @if (($snipeSettings->show_images_in_email =='1') && $asset->getImageUrl())
    <td>
        <img src="{{ asset($asset->getImageUrl()) }}" alt="Asset" style="max-width: 64px;">
    </td>
    @endif
</tr>
@endforeach
</table>
@endif

@if ($licenses->count() > 0)
## {{ $licenses->count() }} {{ trans('general.licenses') }}

<table width="100%">
    <tr>
        <th align="left">{{ trans('mail.name') }} </th>
        <th align="left">{{ trans('mail.serial') }} </th>
        <th align="left">{{ trans('mail.checkout_date') }} </th>
    </tr>
@foreach($licenses as $license)
<tr>
    <td>{{ $license->name }}</td>
    <td>{{ $license->serial }}</td>
    <td>{{ $license->pivot->created_at }}</td>
</tr>
@endforeach
</table>
@endif

@if ($accessories->count() > 0)
## {{ $accessories->count() }} {{ trans('general.accessories') }}

<table width="100%">
    <tr>
        <th align="left">{{ trans('mail.name') }} </th>
        <th></th>
        <th align="left">{{ trans('general.category') }} </th>
        <th align="left">{{ trans('mail.checkout_date') }} </th>
    </tr>
    @foreach($accessories as $accessory)
        <tr>
            <td>{{ $accessory->name }}</td>
            @if (($snipeSettings->show_images_in_email =='1') && $accessory->getImageUrl())
                <td>
                    <img src="{{ asset($accessory->getImageUrl()) }}" alt="Accessory" style="max-width: 64px;">
                </td>
            @endif
            <td>{{ $accessory->category->name }}</td>
            <td>{{ $accessory->pivot->created_at }}</td>
        </tr>
    @endforeach
</table>
@endif

@if ($consumables->count() > 0)
## {{ $consumables->count() }} {{ trans('general.consumables') }}

<table width="100%">
<tr>
    <th align="left">{{ trans('mail.name') }} </th>
    <th align="left">{{ trans('general.category') }} </th>
    <th align="left">{{ trans('mail.checkout_date') }} </th>
</tr>
@foreach($consumables as $consumable)
<tr>
<td>{{ $consumable->name }}</td>
    <td>{{ $consumable->category->name }}</td>
    <td>{{ $consumable->pivot->created_at }}</td>
</tr>
@endforeach
</table>
@endif

@if($indirectItemsCount > 0)
## {{ $indirectItemsCount }} {{ trans('mail.assigned_to_assets') }}
<table width="100%">
    <tr>
        <th align="left">{{ trans('mail.assigned_to') }} </th>
        <th align="left">{{ trans('general.category') }} </th>
        <th align="left">{{ trans('mail.item') }} </th>
        <th align="left">{{ trans('general.quantity') }} </th>
        <th></th>
    </tr>
    @foreach($assets as $asset)
        @foreach($asset->assignedAssets as $indirectAsset)
            <tr>
                <td>{{ $asset->display_name ?? '' }}</td>
                <td>{{ $indirectAsset->model?->name  ?? ''}}</td>
                <td>{{ $indirectAsset->display_name ?? '' }}</td>
                <td>1</td>
            </tr>
        @endforeach
        @foreach($asset->assignedAccessories as $accessory)
            <tr>
                <td>{{ $asset->display_name ?? '' }}</td>
                <td>{{ $accessory->accessory->category?->name ?? '' }}</td>
                <td>{{ $accessory->accessory->name ?? '' }}</td>
                <td>1</td>
            </tr>
        @endforeach
        @foreach($asset->licenses as $license)
            <tr>
                <td>{{ $asset->display_name ?? ''}}</td>
                <td>{{ $license->category?->name ?? '' }}</td>
                <td>{{ $license->name ?? '' }}</td>
                <td>1</td>
            </tr>
        @endforeach
        @foreach($asset->components as $component)
        <tr>
            <td>{{ $asset->display_name ?? ''}}</td>
            <td>{{ $component->category?->name ?? '' }}</td>
            <td>{{ $component->name }}</td>
            <td>{{ $component->pivot->assigned_qty }}</td>
        </tr>
        @endforeach
    @endforeach
</table>
@endif
@endcomponent


@endcomponent
