@props([
    'model' => null,
])

{{-- Marketing name is intentionally not surfaced here: when the admin
     has mapped the vendor's marketing-name extra to native:model, it's
     already the AssetModel's own name. The column still exists on the
     model and is editable from the model form for cases where the
     admin wants a distinct marketing name separate from the display
     name. --}}
@if ($model)
    @if ($model->mfg_release_date)
        <x-info-element icon_type="start_date" title="{{ trans('admin/models/general.mfg_release_date') }}">
            {{ \App\Helpers\Helper::getFormattedDateObject($model->mfg_release_date, 'date', false) }}
        </x-info-element>
    @endif

    @if ($model->mfg_discontinued_date)
        <x-info-element icon_type="end_date" title="{{ trans('admin/models/general.mfg_discontinued_date') }}">
            {{ \App\Helpers\Helper::getFormattedDateObject($model->mfg_discontinued_date, 'date', false) }}
        </x-info-element>
    @endif
@endif
