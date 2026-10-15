<span>
    <a href="#" data-toggle="modal" data-target="#cloneFieldsetModal{{ $id }}" class="btn btn-sm btn-info hidden-print" data-tooltip="true" data-placement="top" title="{{ trans('admin/custom_fields/general.clone_fieldset') }}" data-title="{{ trans('admin/custom_fields/general.clone_fieldset') }}">
        <x-icon type="clone" class="fa-fw"/>
        @if ($iconOnly)
            <span class="sr-only">{{ trans('admin/custom_fields/general.clone_fieldset') }}</span>
        @else
            {{ trans('general.clone') }}
        @endif
    </a>

    @teleport('body')
        <x-modals
            id="cloneFieldsetModal{{ $id }}"
            stacked
            :title="$this->title"
            :submit_label="trans('general.clone')"
            form_attrs='wire:submit="submit"'
            wire:ignore.self
        >
            <x-form.row
                :label="trans('general.name')"
                name="name"
                id="clone_fieldset_name_{{ $id }}"
            >
                <x-slot:input>
                    <input type="text" class="form-control" id="clone_fieldset_name_{{ $id }}" wire:model="name">
                </x-slot:input>
            </x-form.row>
        </x-modals>
    @endteleport
</span>
