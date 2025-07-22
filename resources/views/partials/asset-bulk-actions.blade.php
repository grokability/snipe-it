<div id="{{ (isset($id_divname)) ? $id_divname : 'assetsBulkEditToolbar' }}" style="min-width:400px">
    <form
    method="POST"
    action="{{ route('hardware/bulkedit') }}"
    accept-charset="UTF-8"
    class="form-inline"
    id="{{ (isset($id_formname)) ? $id_formname : 'assetsBulkForm' }}"
>
    @csrf

    {{-- The sort and order will only be used if the cookie is actually empty (like on first-use) --}}
    <input name="sort" type="hidden" value="assets.id">
    <input name="order" type="hidden" value="asc">
    <label for="bulk_actions">
        <span class="sr-only">
            {{ trans('button.bulk_actions') }}
        </span>
    </label>
    <select name="bulk_actions" class="form-control select2" aria-label="bulk_actions" style="min-width: 350px !important;">
        @if ((isset($status)) && ($status == 'Deleted'))
            @can('delete', \App\Models\Asset::class)
                <option value="restore">{{trans('button.restore')}}</option>
            @endcan
        @else

            @can('update', \App\Models\Asset::class)
                <option value="edit">{{ trans('button.edit') }}</option>
                <option value="maintenance">{{ trans('button.add_maintenance') }}</option>
            @endcan

            @if((!isset($status)) || (($status != 'Deployed') && ($status != 'Archived')))
                @can('checkout', \App\Models\Asset::class)
                    <option value="checkout">{{ trans('general.bulk_checkout') }}</option>
                @endcan
            @endif

            @can('delete', \App\Models\Asset::class)
                <option value="delete">{{ trans('button.delete') }}</option>
            @endcan

            <option value="labels" {{$snipeSettings->shortcuts_enabled == 1 ? "accesskey=l" : ''}}>{{ trans_choice('button.generate_labels', 2) }}</option>
        @endif
    </select>

    <button class="btn btn-primary" id="{{ (isset($id_button)) ? $id_button : 'bulkAssetEditButton' }}" disabled>{{ trans('button.go') }}</button>
    </form>
</div>
