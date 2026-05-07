@props([
    'item',
])

@can('files', $item)
    <li class="snipetab uploadtab pull-right" role="presentation">
    <a href="#" data-toggle="modal" data-target="#uploadFileModal" data-tooltip="true" data-placement="top" data-title="{{ trans('general.upload_files') }}">
        <x-icon type="paperclip" style="font-size: 16px"/>
        <span class="visible-xs">
            {{ trans('general.upload_files') }}
        </span>
    </a>
</li>
@endcan