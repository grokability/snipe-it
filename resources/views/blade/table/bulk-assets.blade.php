@aware(['name'])

    <form
            method="POST"
            action="{{ route('hardware.bulkedit.show') }}"
            accept-charset="UTF-8"
            class="form-inline"
            id="{{ Illuminate\Support\Str::camel($name) }}Form"
    >
        @csrf

        <div style="width:100% !important;" class="hidden-print">
            {{-- The sort and order will only be used if the cookie is actually empty (like on first-use) --}}
            <input name="sort" type="hidden" value="assets.id">
            <input name="order" type="hidden" value="asc">
            <label>
            <span class="sr-only">
                {{ trans('button.bulk_actions') }}
            </span>

            <select name="bulk_actions" class="form-control select2" aria-label="bulk_actions" style="width: 350px !important;">
                @if ((isset($status)) && ($status == 'Deleted'))
                    @can('delete', \App\Models\Asset::class)
                        <option value="restore">{{trans('button.restore')}}</option>
                    @endcan
                @else

                    @can('update', \App\Models\Asset::class)
                        <option value="edit">{{ trans('general.bulk_edit') }}</option>
                        <option value="maintenance">{{ trans('button.add_maintenance') }}</option>
                    @endcan

                    @if((!isset($status)) || (($status != 'Deployed') && ($status != 'Archived')))
                        @can('checkout', \App\Models\Asset::class)
                            <option value="checkout">{{ trans('general.bulk_checkout') }}</option>
                        @endcan
                    @endif

                    @can('delete', \App\Models\Asset::class)
                        <option value="delete">{{ trans('general.bulk_delete') }}</option>
                    @endcan

                    <option value="labels">{{ trans_choice('button.generate_labels', 2) }}</option>
                @endif
            </select>

            <button class="btn btn-theme" id="{{ Illuminate\Support\Str::camel($name) }}Button" disabled>{{ trans('button.go') }}</button>
            </label>
            </div>
    </form>
