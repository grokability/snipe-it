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

            <select name="bulk_actions" class="form-control select2" aria-label="bulk_actions" style="width: 350px !important;" id="{{ Illuminate\Support\Str::camel($name) }}BulkActions">
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

                    @php $printables = \App\Models\Printable::orderBy('name')->get(); @endphp
                    @if ($printables->isNotEmpty())
                        <option value="printables">{{ trans_choice('button.generate_printable', 2) }}</option>
                    @endif
                @endif
            </select>

            {{-- Printable template selector – shown only when "Generate Printables" is selected --}}
            @if ($printables->isNotEmpty())
                <select name="printable_id"
                        id="{{ Illuminate\Support\Str::camel($name) }}PrintableSelect"
                        class="form-control select2"
                        aria-label="{{ trans('general.printables') }}"
                        style="width: 200px !important; display: none; margin-left: 5px;">
                    @foreach ($printables as $printable)
                        <option value="{{ $printable->id }}">{{ $printable->name }}</option>
                    @endforeach
                </select>
            @endif

            <button class="btn btn-theme" id="{{ Illuminate\Support\Str::camel($name) }}Button" disabled>{{ trans('button.go') }}</button>
            </label>
            </div>
    </form>

@push('moar_scripts')
<script nonce="{{ csrf_token() }}">
    (function () {
        var bulkSelect  = document.getElementById('{{ Illuminate\Support\Str::camel($name) }}BulkActions');
        var printSel    = document.getElementById('{{ Illuminate\Support\Str::camel($name) }}PrintableSelect');

        if (bulkSelect && printSel) {
            bulkSelect.addEventListener('change', function () {
                printSel.style.display = (this.value === 'printables') ? 'inline-block' : 'none';
            });
        }
    })();
</script>
@endpush
