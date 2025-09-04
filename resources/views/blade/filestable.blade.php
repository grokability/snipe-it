<!-- begin redirect submit options -->
@props([
    'object',
    'object_type' => '',
])

<!-- begin non-ajaxed file listing table -->
<div class="table-responsive">
    <table
            data-columns="{{ \App\Presenters\UploadedFilesPresenter::dataTableLayout() }}"
            data-cookie-id-table="{{ $object_type }}-FileUploadsTable"
            data-id-table="{{ $object_type }}-FileUploadsTable"
            id="{{ $object_type }}-FileUploadsTable"
            data-side-pagination="server"
            data-pagination="true"
            data-sort-order="desc"
            data-sort-name="created_at"
            data-show-custom-view="true"
            data-custom-view="customViewFormatter"
            data-show-custom-view-button="true"
            data-url="{{ route('api.files.index', ['object_type' => $object_type, 'id' => $object->id]) }}"
            class="table table-striped snipe-table"
            data-export-options='{
                    "fileName": "export-uploads-{{ str_slug($object->name) }}-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["image","delete","download","icon"]
                    }'>
    </table>

    <x-gallery-card />

</div>



<!-- end non-ajaxed file listing table -->