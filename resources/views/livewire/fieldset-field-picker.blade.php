<div>
@if ($fieldsetId === 0)
    {{-- Create phase. The picker needs a saved fieldset to operate on,
         so show the name prompt until createFieldset() persists one.
         Explicit submit, not blur-save, so a half-typed name doesn't
         race-create a draft on every focus change. --}}
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <x-box :header="trans('admin/custom_fields/general.create_fieldset_title')">
                <form wire:submit="createFieldset">
                    <x-form.row
                        :label="trans('general.name')"
                        name="name"
                        required
                    >
                        <x-slot:input>
                            <input
                                type="text"
                                class="form-control"
                                wire:model="name"
                                autofocus
                                required
                            >
                        </x-slot:input>
                    </x-form.row>

                    <div class="form-group">
                        <div class="col-md-9 col-md-offset-3 text-right">
                            <button type="submit" class="btn btn-success">
                                <x-icon type="arrow-right" />
                                {{ trans('general.next') }}
                            </button>
                        </div>
                    </div>
                </form>
            </x-box>
        </div>
    </div>
@else
<div class="row">
    {{-- LEFT: Attached fields --}}
    <div class="col-md-6">
        <x-box :header="trans('admin/custom_fields/general.fields_in_fieldset') . ' (' . $attached->count() . ')'">
            {{-- Fieldset name edit field. Saves on blur via the
                 updatedName() lifecycle hook, so admins can rename
                 without an explicit save click. --}}
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}" style="margin-bottom: 15px;">
                <label for="cf-picker-name" class="sr-only">{{ trans('general.name') }}</label>
                <input
                    id="cf-picker-name"
                    type="text"
                    class="form-control"
                    wire:model.blur="name"
                    placeholder="{{ trans('admin/custom_fields/general.fieldset_name') }}"
                    required
                >
                <x-form.error name="name" />
            </div>

            {{-- Always rendered to reserve the vertical space. When nothing
                 is selected, visibility: hidden hides the strip and blocks
                 pointer events while keeping layout identical, so the
                 table underneath does not jump as selections toggle. --}}
            <div
                class="clearfix"
                style="margin-bottom: 15px;{{ empty($selectedFieldIds) ? ' visibility: hidden;' : '' }}"
                @if (empty($selectedFieldIds)) aria-hidden="true" @endif
            >
                <span class="text-muted">
                    <strong>{{ count($selectedFieldIds) }}</strong>
                    {{ trans('general.selected') }}
                </span>
                <div class="pull-right">
                    <button
                        type="button"
                        class="btn btn-sm btn-success"
                        wire:click="bulkSetRequired(true)"
                    >
                        <i class="fa fa-check" aria-hidden="true"></i>
                        {{ trans('admin/custom_fields/general.make_required') }}
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm btn-warning"
                        wire:click="bulkSetRequired(false)"
                    >
                        {{ trans('admin/custom_fields/general.make_optional') }}
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm btn-danger"
                        wire:click="bulkRemove"
                    >
                        <i class="fa fa-minus" aria-hidden="true"></i>
                        {{ trans('button.remove') }}
                    </button>
                </div>
            </div>

            @if ($attached->isEmpty())
                <div class="text-center text-muted" style="padding: 40px 0;">
                    <i class="fa fa-arrow-right fa-2x" aria-hidden="true" style="margin-bottom: 10px;"></i>
                    <br>
                    {{ trans('admin/custom_fields/general.no_attached_fields') }}
                </div>
            @else
                @php
                    $attachedIds = $attached->pluck('id')->map(fn ($x) => (int) $x)->all();
                    $allAttachedSelected = ! empty($attachedIds) && count(array_intersect($attachedIds, array_map('intval', $selectedFieldIds))) === count($attachedIds);
                @endphp
                <table class="table table-striped snipe-table">
                    <thead>
                        <tr>
                            <th scope="col" class="col-md-1">
                                <input
                                    type="checkbox"
                                    aria-label="{{ trans('general.select_all_none') }}"
                                    data-tooltip="true"
                                    title="{{ trans('general.select_all_none') }}"
                                    @checked($allAttachedSelected)
                                    wire:click="toggleSelectAllAttached($event.target.checked)"
                                >
                            </th>
                            <th scope="col" class="col-md-1"><span class="sr-only">{{ trans('admin/custom_fields/general.reorder') }}</span></th>
                            <th scope="col">{{ trans('admin/custom_fields/general.field_name') }}</th>
                            <th scope="col">{{ trans('admin/custom_fields/general.field_element_short') }}</th>
                            <th scope="col" class="col-md-2 text-center">{{ trans('admin/custom_fields/general.required') }}</th>
                            <th scope="col" class="col-md-1"><span class="sr-only">{{ trans('button.remove') }}</span></th>
                        </tr>
                    </thead>
                    <tbody wire:sort="reorder">
                        @foreach ($attached as $field)
                            <tr wire:key="attached-{{ $field->id }}" wire:sort:item="{{ $field->id }}">
                                <td>
                                    <input
                                        type="checkbox"
                                        value="{{ $field->id }}"
                                        wire:model.live="selectedFieldIds"
                                        aria-label="{{ trans('general.select') }}"
                                    >
                                </td>
                                <td class="text-center text-muted" style="cursor: grab;">
                                    <i class="fa fa-grip-vertical" aria-hidden="true"></i>
                                </td>
                                <td>{{ $field->name }}</td>
                                <td class="text-muted">{{ $field->element }}</td>
                                <td class="text-center">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-link"
                                        wire:click="toggleRequired({{ $field->id }})"
                                        data-tooltip="true"
                                        title="{{ $field->pivot->required
                                            ? trans('admin/custom_fields/general.make_optional')
                                            : trans('admin/custom_fields/general.make_required') }}"
                                    >
                                        @if ($field->pivot->required)
                                            <i class="fa fa-check text-success" aria-hidden="true"></i>
                                            <span class="sr-only">{{ trans('admin/custom_fields/general.required') }}</span>
                                        @else
                                            <i class="fa fa-times text-danger" aria-hidden="true"></i>
                                            <span class="sr-only">{{ trans('admin/custom_fields/general.optional') }}</span>
                                        @endif
                                    </button>
                                </td>
                                <td>
                                    {{-- onclick fires before the Livewire round-trip, so the
                                         BS3 tooltip floater (parented to <body> via container: 'body')
                                         is detached before this row disappears. Without it the
                                         tooltip is orphaned in body once Livewire morphs the row out. --}}
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-danger"
                                        wire:click="detach({{ $field->id }})"
                                        onclick="$(this).tooltip('hide')"
                                        data-tooltip="true"
                                        title="{{ trans('general.remove_customfield_association') }}"
                                    >
                                        <i class="fa fa-minus" aria-hidden="true"></i>
                                        <span class="sr-only">{{ trans('button.remove') }}</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </x-box>
    </div>

    {{-- RIGHT: Available fields --}}
    <div class="col-md-6">
        <x-box :header="trans('admin/custom_fields/general.available_fields')">
            <div class="form-group" style="margin-bottom: 15px;">
                <div class="input-group">
                    <input
                        type="search"
                        class="form-control"
                        placeholder="{{ trans('general.search') }}"
                        wire:model.live.debounce.300ms="search"
                        aria-label="{{ trans('general.search') }}"
                    >
                    <span class="input-group-btn">
                        <button
                            type="button"
                            class="btn btn-theme"
                            wire:click="$set('search', '')"
                            @disabled($search === '')
                            data-tooltip="true"
                            title="{{ trans('general.clear_selection') }}"
                            aria-label="{{ trans('general.clear_selection') }}"
                        >
                            <i class="fa fa-times" aria-hidden="true"></i>
                        </button>
                    </span>
                </div>
            </div>

            <div
                class="clearfix"
                style="margin-bottom: 15px;{{ empty($selectedAvailableIds) ? ' visibility: hidden;' : '' }}"
                @if (empty($selectedAvailableIds)) aria-hidden="true" @endif
            >
                <span class="text-muted">
                    <strong>{{ count($selectedAvailableIds) }}</strong>
                    {{ trans('general.selected') }}
                </span>
                <div class="pull-right">
                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        wire:click="bulkAttach"
                    >
                        <i class="fa fa-plus" aria-hidden="true"></i>
                        {{ trans('admin/custom_fields/general.add_field_to_fieldset') }}
                    </button>
                </div>
            </div>

            @if ($available->isEmpty())
                <div class="text-center text-muted" style="padding: 20px 0;">
                    {{ $search !== ''
                        ? trans('general.no_results')
                        : trans('admin/custom_fields/general.no_available_fields') }}
                </div>
            @else
                @php
                    $availablePageIds = $available->pluck('id')->map(fn ($x) => (int) $x)->all();
                    $allAvailableOnPageSelected = ! empty($availablePageIds) && count(array_intersect($availablePageIds, array_map('intval', $selectedAvailableIds))) === count($availablePageIds);
                @endphp
                <table class="table table-striped snipe-table">
                    <thead>
                        <tr>
                            <th scope="col" class="col-md-1">
                                <input
                                    type="checkbox"
                                    aria-label="{{ trans('general.select_all_none') }}"
                                    data-tooltip="true"
                                    title="{{ trans('general.select_all_none') }}"
                                    @checked($allAvailableOnPageSelected)
                                    wire:click="toggleSelectAllAvailable($event.target.checked, {{ json_encode($availablePageIds) }})"
                                >
                            </th>
                            <th scope="col">{{ trans('admin/custom_fields/general.field_name') }}</th>
                            <th scope="col">{{ trans('admin/custom_fields/general.field_element_short') }}</th>
                            <th scope="col" class="col-md-2"><span class="sr-only">{{ trans('admin/custom_fields/general.add_field_to_fieldset') }}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($available as $field)
                            <tr wire:key="available-{{ $field->id }}">
                                <td>
                                    <input
                                        type="checkbox"
                                        value="{{ $field->id }}"
                                        wire:model.live="selectedAvailableIds"
                                        aria-label="{{ trans('general.select') }}"
                                    >
                                </td>
                                <td>{{ $field->name }}</td>
                                <td class="text-muted">{{ $field->element }}</td>
                                <td class="text-right">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-primary"
                                        wire:click="attach({{ $field->id }})"
                                        onclick="$(this).tooltip('hide')"
                                        data-tooltip="true"
                                        title="{{ trans('admin/custom_fields/general.add_field_to_fieldset') }}"
                                    >
                                        <i class="fa fa-plus" aria-hidden="true"></i>
                                        <span class="sr-only">{{ trans('admin/custom_fields/general.add_field_to_fieldset') }}</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if ($available->hasPages())
                <div style="margin-top: 15px;">
                    {{ $available->links() }}
                </div>
            @endif
        </x-box>
    </div>
</div>
@endif
</div>
