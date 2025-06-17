@props([
    'asset',
    'field_array',
    'counter',
])

{{--
    This component was extracted for the account.view_assets view. Not guaranteed to work in other views.
--}}

<tr>
    <td>{{ $counter }}</td>
    <td>
        @if (($asset->image) && ($asset->image!=''))
            <img src="{{ Storage::disk('public')->url(app('assets_upload_path').e($asset->image)) }}" style="max-height: 30px; width: auto" class="img-responsive" alt="">
        @elseif (($asset->model) && ($asset->model->image!=''))
            <img src="{{ Storage::disk('public')->url(app('models_upload_path').e($asset->model->image)) }}" style="max-height: 30px; width: auto" class="img-responsive" alt="">
        @endif
    </td>
    <td>
        @if (($asset->model) && ($asset->model->category))
            {{ $asset->model->category->name }}
        @endif
    </td>
    <td>
        {{ $asset->asset_tag }}
    </td>
    <td>
        {{ $asset->name }}
    </td>
    <td>
        {{ $asset->model->name }}
    </td>
    <td>
        {{ $asset->model->model_number }}
    </td>
    <td>
        {{ $asset->serial }}
    </td>
    <td>
        {{ ($asset->defaultLoc) ? $asset->defaultLoc->name : '' }}
    </td>
    <td>
        {{ ($asset->location) ? $asset->location->name : '' }}
    </td>
    @can('self.view_purchase_cost')
        <td>
            {!! Helper::formatCurrencyOutput($asset->purchase_cost) !!}
        </td>
    @endcan

    <td>
        {{ ($asset->asset_eol_date != '') ? Helper::getFormattedDateObject($asset->asset_eol_date, 'date', false) : null }}
    </td>

    <td>
        {{ Helper::getFormattedDateObject($asset->last_audit_date, 'datetime', false) }}
    </td>
    <td>
        {{ Helper::getFormattedDateObject($asset->next_audit_date, 'date', false) }}
    </td>

    @foreach ($field_array as $db_column => $field_value)
        <td>
            {{ $asset->{$db_column} }}
        </td>
    @endforeach
</tr>
