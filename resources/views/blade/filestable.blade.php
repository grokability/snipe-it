<!-- begin redirect submit options -->
@props([
    'filepath',
    'object',
    'showfile_routename',
    'deletefile_routename',
])

<!-- begin non-ajaxed file listing table -->
<div class="table-responsive">
    <table
            data-cookie-id-table="{{ str_slug($object->name ?? $object->id) }}UploadsTable"
            data-id-table="{{ str_slug($object->name ?? $object->id) }}UploadsTable"
            id="{{ str_slug($object->name ?? $object->id) }}UploadsTable"
            data-search="true"
            data-search-highlight="true"
            data-show-print="true"
            data-pagination="true"
            data-side-pagination="client"
            data-show-columns="true"
            data-show-fullscreen="true"
            data-show-export="true"
            data-show-footer="true"
            data-toolbar="#upload-toolbar"
            data-show-refresh="true"
            data-sort-order="asc"
            data-sort-name="name"
            class="table table-striped snipe-table"
            data-export-options='{
                    "fileName": "export-license-uploads-{{ str_slug($object->name) }}-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","delete","download","icon"]
                    }'>

        <thead>
        <tr>
            <th data-visible="false" data-field="id" data-sortable="true">
                {{trans('general.id')}}
            </th>
            <th data-visible="true" data-field="type" data-sortable="true">
                {{trans('general.file_type')}}
            </th>
            <th class="col-md-2" data-searchable="true" data-visible="true" data-field="image">
                {{ trans('general.image') }}
            </th>
            <th class="col-md-2" data-searchable="true" data-visible="true" data-field="filename" data-sortable="true">
                {{ trans('general.file_name') }}
            </th>
            <th class="col-md-1" data-searchable="true" data-visible="true" data-field="filesize">
                {{ trans('general.filesize') }}
            </th>
            <th class="col-md-2" data-searchable="true" data-visible="true" data-field="notes" data-sortable="true">
                {{ trans('general.notes') }}
            </th>
            <th class="col-md-1" data-searchable="true" data-visible="true" data-field="download">
                {{ trans('general.download') }}
            </th>
            <th class="col-md-2" data-searchable="true" data-visible="true" data-field="created_at" data-sortable="true">
                {{ trans('general.created_at') }}
            </th>
            <th class="col-md-2" data-searchable="true" data-visible="true" data-field="created_by" data-sortable="true">
                {{ trans('general.created_by') }}
            </th>
            <th class="col-md-1" data-searchable="true" data-visible="true" data-field="actions">
                {{ trans('table.actions') }}
            </th>
        </tr>
        </thead>
        <tbody>
        @foreach ($object->uploads as $file)
            <tr>
                <td>
                    {{ $file->id }}
                </td>
                <td data-sort-value="{{ pathinfo($filepath.$file->filename, PATHINFO_EXTENSION) }}">
                    @if (Storage::exists($filepath.$file->filename))
                        <span class="sr-only">{{ pathinfo($filepath.$file->filename, PATHINFO_EXTENSION) }}</span>
                        <i class="{{ Helper::filetype_icon($file->filename) }} icon-med" aria-hidden="true" data-tooltip="true" data-title="{{ pathinfo($filepath.$file->filename, PATHINFO_EXTENSION) }}"></i>
                    @endif
                </td>
                <td>

                    @if (($file->filename) && (Storage::exists($filepath.$file->filename)))
                        @if (Helper::checkUploadIsImage($file->get_src(str_plural(strtolower(class_basename(get_class($object)))))))
                            <a href="{{ route($showfile_routename, [$object->id, $file->id, 'inline' => 'true']) }}" data-toggle="lightbox" data-type="image">
                                <img src="{{ route($showfile_routename, [$object->id, $file->id, 'inline' => 'true']) }}" class="img-thumbnail" style="max-width: 50px;">
                            </a>
                        @else
                            {{ trans('general.preview_not_available') }}
                        @endif
                    @else
                        <x-icon type="x" class="text-danger" />
                        {{ trans('general.file_not_found') }}
                    @endif

                </td>
                <td>
                    {{ $file->filename }}
                </td>
                <td data-value="{{ (Storage::exists($filepath.$file->filename)) ? Storage::size($filepath.$file->filename) : '' }}">
                    {{ (Storage::exists($filepath.$file->filename)) ? Helper::formatFilesizeUnits(Storage::size($filepath.$file->filename)) : '' }}
                </td>

                <td>
                    @if ($file->note)
                        {{ $file->note }}
                    @endif
                </td>
                <td style="white-space: nowrap;">
                    @if ($file->filename)
                        @if (Storage::exists($filepath.$file->filename))
                            <a href="{{ route($showfile_routename, [$object->id, $file->id]) }}" class="btn btn-sm btn-default">
                                <x-icon type="download" />
                                <span class="sr-only">
                                    {{ trans('general.download') }}
                                </span>
                            </a>

                            <a href="{{ StorageHelper::allowSafeInline($filepath.$file->filename) ? route($showfile_routename, [$object->id, $file->id, 'inline' => 'true']) : '#' }}" class="btn btn-sm btn-default{{ StorageHelper::allowSafeInline($filepath.$file->filename) ? '' : ' disabled' }}" target="_blank">
                                <x-icon type="external-link" />
                            </a>
                        @endif
                    @endif
                </td>
                <td>
                    {{ $file->created_at ? Helper::getFormattedDateObject($file->created_at, 'datetime', false) : '' }}
                </td>
                <td>
                    {{ ($file->adminuser) ? $file->adminuser->present()->getFullNameAttribute() : '' }}
                </td>
                <td>
                    <a class="btn delete-asset btn-danger btn-sm hidden-print" href="{{ route($deletefile_routename, [$object->id, $file->id]) }}" data-content="Are you sure you wish to delete this file?" data-title="{{ trans('general.delete') }} {{ $file->filename }}?">
                        <x-icon type="delete" />
                        <span class="sr-only">{{ trans('general.delete') }}</span>
                    </a>
                </td>


            </tr>
        @endforeach

        </tbody>
    </table>
</div>
<!-- end non-ajaxed file listing table -->