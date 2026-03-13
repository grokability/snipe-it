<!-- Modal -->
<div class="modal fade" id="{{ $modal_name }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modal_name }}Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="{{ $modal_name }}Label">{{ $title }}</h4>
            </div>
            <form action="{{ $route }}" method="POST" class="form-horizontal">
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <div class="modal-body">

                <div class="row">
                    <div class="col-md-12">
                        {{ $body }}
                    </div>
                </div>

            </div> <!-- /.modal-body-->
            <div class="modal-footer">
                <a href="#" class="pull-left" data-dismiss="modal">{{ trans('button.cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ trans('general.confirm') }}</button>
            </div>
            </form>
        </div>
    </div>
</div>
