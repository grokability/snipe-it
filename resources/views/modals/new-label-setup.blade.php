<div class="modal fade" id="new-label-modal" tabindex="-1" role="dialog" aria-labelledby="newLabelModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <form action="{{ route('settings.labels.create') }}" method="GET">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>

                    <h4 class="modal-title" id="newLabelModalLabel">
                        {{trans('admin/labels/general.create_label')}}
                    </h4>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>{{trans('admin/labels/general.label_type')}}</label>

                        <div class="row label-type-selector">
                            <div class="col-xs-6">
                                <label class="label-type-card active">
                                    <input type="radio" name="type" value="sheet" checked>
                                    <i class="fa-regular fa-file-lines"></i>
                                    <span>{{trans('admin/labels/general.sheet_labels')}}</span>
                                </label>
                            </div>

                            <div class="col-xs-6">
                                <label class="label-type-card">
                                    <input type="radio" name="type" value="tape">
                                    <i class="fa-solid fa-tape"></i>
                                    <span>{{trans('admin/labels/general.tape_labels')}}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="sheet-options">
                        <div class="form-group">
                            <label>{{trans('admin/labels/general.page_size')}}</label>

                            <select name="page_size" class="form-control">
                                @foreach(\App\Models\Labels\RectangleSheet::supportedPageSizes() as $key => $page)
                                    <option value="{{ $key }}"
                                            data-width="{{ $page['width'] }}"
                                            data-height="{{ $page['name'] }}"
                                    >

                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label>{{trans('admin/labels/general.fields.width')}}</label>
                            <input
                                    type="number"
                                    step="0.1"
                                    min="0.1"
                                    name="label_width"
                                    class="form-control"
                                    required
                            >
                        </div>

                        <div class="col-md-6">
                            <label>{{trans('admin/labels/general.fields.height')}}</label>
                            <input
                                    type="number"
                                    step="0.1"
                                    min="0.1"
                                    name="label_height"
                                    class="form-control"
                                    required
                            >
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{trans('admin/labels/general.fields.label_gap')}}</label>
                            <input
                                    type="number"
                                    step="0.1"
                                    min="0.1"
                                    name="label_gap"
                                    class="form-control"
                                    value="0"
                            >
                        </div>
                    </div>


                </div>
                <div id="sheet-summary" class="text-center">
                    <strong>{{ trans('admin/labels/general.labels_per_sheet') }}</strong>

                    <div class="huge text-primary">
                        <span id="labels-per-sheet">0</span>
                    </div>

                    <small class="text-muted">
                        {{ trans('admin/labels/general.based_on_label_dimensions') }}
                    </small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        {{trans('general.cancel')}}
                    </button>

                    <button type="submit" class="btn btn-primary">
                        {{trans('general.continue')}}
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>