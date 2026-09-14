<div>
    <div class="modal fade" id="new-label-modal" tabindex="-1" role="dialog" aria-labelledby="newLabelModalLabel"
         wire:ignore.self>
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <form action="{{ route('settings.labels.create') }}" method="GET">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>

                        <h4 class="modal-title" id="newLabelModalLabel">
                            {{ trans('admin/labels/general.create_label') }}
                        </h4>
                    </div>

                    <div class="modal-body">

                        <div class="form-group">
                            <label>{{ trans('admin/labels/general.label_type') }}</label>

                            <div class="row label-type-selector">
                                <div class="col-xs-6">
                                    <label class="label-type-card {{ $type === 'sheet' ? 'active' : '' }}">
                                        <input
                                                type="radio"
                                                name="type"
                                                value="sheet"
                                                wire:model.live="type"
                                        >

                                        <i class="fa-regular fa-file-lines"></i>
                                        <span>{{ trans('admin/labels/general.sheet_labels') }}</span>
                                    </label>
                                </div>

                                <div class="col-xs-6">
                                    <label class="label-type-card {{ $type === 'tape' ? 'active' : '' }}">
                                        <input
                                                type="radio"
                                                name="type"
                                                value="tape"
                                                wire:model.live="type"
                                        >

                                        <i class="fa-solid fa-tape"></i>
                                        <span>{{ trans('admin/labels/general.tape_labels') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <label>
                            Unit: mm (applies to all dimensions)
                        </label>
                        <br><br>
                        @if ($type === 'sheet')
                            <div id="sheet-options">

                                <div class="form-group">
                                    <label>{{ trans('admin/labels/general.page_size') }}</label>

                                    <select name="page_size" class="form-control" wire:model.live="pageSize">
                                        @foreach($pageSizes as $key => $page)
                                            <option value="{{ $key }}">{{ $page['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <label>{{ trans('admin/labels/general.fields.columns') }}</label>
                                        <input
                                                type="number"
                                                min="1"
                                                step="1"
                                                name="columns"
                                                class="form-control"
                                                wire:model.live="columns"
                                                required
                                        >
                                    </div>

                                    <div class="col-md-6">
                                        <label>{{ trans('admin/labels/general.fields.rows') }}</label>
                                        <input
                                                type="number"
                                                min="1"
                                                step="1"
                                                name="rows"
                                                class="form-control"
                                                wire:model.live="rows"
                                                required
                                        >
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row" style="margin-top: 15px;">
                            <div class="col-md-6">
                                <label>{{ trans('admin/labels/general.fields.width') }}</label>

                                <input
                                        type="number"
                                        step="0.1"
                                        min="0.1"
                                        name="label_width"
                                        class="form-control"
                                        wire:model.live.debounce.300ms="labelWidth"
                                        required
                                >
                            </div>

                            <div class="col-md-6">
                                <label>{{ trans('admin/labels/general.fields.height') }}</label>

                                <input
                                        type="number"
                                        step="0.1"
                                        min="0.1"
                                        name="label_height"
                                        class="form-control"
                                        wire:model.live.debounce.300ms="labelHeight"
                                        required
                                >
                            </div>
                        </div>
                        @if ($type === 'tape')
                            <div class="row" style="margin-top: 15px;">
                                <div class="col-md-6">
                                    <label>{{ trans('admin/labels/general.fields.label_gap') }}</label>
                                    <input
                                            type="number"
                                            step="0.1"
                                            min="0"
                                            name="label_gap"
                                            class="form-control"
                                            placeholder="0"
                                            wire:model.live.debounce.300ms="labelGap"
                                            value="0"
                                    >
                                </div>
                            </div>
                        @endif

                        @if ($type === 'sheet')
                            <hr>

                            <div id="sheet-summary" class="text-center">
                                <strong>{{ trans('admin/labels/general.labels_per_sheet') }}</strong>

                                <div class="huge text-primary">
                                    {{ $this->labelsPerSheet }}
                                </div>

                                <small class="text-muted">
                                    {{ trans('admin/labels/general.label_count_help') }}
                                </small>
                            </div>
                        @endif

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            {{ trans('general.cancel') }}
                        </button>

                        <button type="submit" class="btn btn-primary">
                            {{ trans('general.continue') }}
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>