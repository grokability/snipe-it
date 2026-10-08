<div>
    <a href="#" data-toggle="modal" data-target="#cloneFieldsetModal" class="btn btn-sm btn-info hidden-print" data-tooltip="true" data-placement="top" data-title="{{ trans('admin/custom_fields/general.clone_fieldset') }}">
        <x-icon type="clone" class="fa-fw"/>
        {{ trans('general.clone') }}
    </a>

    @teleport('body')
        <x-modals
            id="cloneFieldsetModal"
            stacked
            :title="trans('admin/custom_fields/general.clone_fieldset')"
            :submit_label="trans('general.clone')"
            form_attrs='wire:submit="submit"'
            wire:ignore.self
        >
            <x-form.row
                :label="trans('general.name')"
                name="name"
                id="clone_fieldset_name"
            >
                <x-slot:input>
                    <input type="text" class="form-control" id="clone_fieldset_name" wire:model="name">
                </x-slot:input>
            </x-form.row>
        </x-modals>
    @endteleport
</div>
