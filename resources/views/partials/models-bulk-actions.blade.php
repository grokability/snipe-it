<div id="modelsBulkEditToolbar">
    <form
        method="POST"
        action="{{route('models.bulkedit.index')}}"
        accept-charset="UTF-8"
        class="form-inline"
        id="modelsBulkForm"
    >
    @csrf
    @if (request('status')!='deleted')
        @can('delete', \App\Models\AssetModel::class)
            <div id="models-toolbar">
                <label for="bulk_actions" class="sr-only">{{ trans('general.bulk_actions') }}</label>
                <select name="bulk_actions" class="form-control select2" style="width: 200px;" aria-label="bulk_actions">
                    <option value="edit">{{ trans('general.bulk_edit') }}</option>
                    <option value="delete">{{ trans('general.bulk_delete') }}</option>
                </select>
                <button class="btn btn-theme" id="bulkModelsEditButton" disabled>{{ trans('button.go') }}</button>
            </div>
        @endcan
    @endif
    </form>
</div>


