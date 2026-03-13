<x-mail::message>

<style>
    th, td {
        vertical-align: top;
    }
    hr {
        display: block;
        height: 1px;
        border: 0;
        border-top: 1px solid #ccc;
        margin: 1em 0;
        padding: 0;
    }
</style>

{{ $introduction }}

@if ($requires_acceptance)
{{ $requires_acceptance_info }}

{{ $requires_acceptance_prompt }}
<hr>
@endif

@if ((isset($expected_checkin)) && ($expected_checkin!=''))
**{{ trans('mail.expecting_checkin_date') }}**: {{ Helper::getFormattedDateObject($expected_checkin, 'date', false) }}
@endif

@if ($note)
**{{ trans('mail.additional_notes') }}**: {{ $note }}
@endif

@foreach($assetsByCategory as $group)
<x-mail::panel>

**{{ $group->first()->model->category->name }}**

<x-mail::table>
|        |        |
| ------------- | ------------- |
@foreach($group as $asset)
| **{{ trans('general.asset_tag') }}** | <a href="{{ route('hardware.show', $asset->id) }}">{{ $asset->display_name }}</a><br><small>{{trans('mail.serial').': '.$asset->serial}}</small> |
@if (isset($asset->manufacturer))
| **{{ trans('general.manufacturer') }}** | {{ $asset->manufacturer->name }} |
@endif
@if (isset($asset->model))
| **{{ trans('general.asset_model') }}** | {{ $asset->model->name }} |
@endif
@if ((isset($asset->model?->model_number)))
| **{{ trans('general.model_no') }}** | {{ $asset->model->model_number }} |
@endif
@if (isset($asset->assetstatus))
| **{{ trans('general.status') }}** | {{ $asset->assetstatus->name }} |
@endif
@if($asset->fields)
@foreach($asset->fields as $field)
@if ($asset->{ $field->db_column_name() } != '')
| **{{ $field->name }}** | {{ $asset->{ $field->db_column_name() } }} |
@endif
@endforeach
@endif
@if(!$loop->last)
| <hr> | <hr> |
@endif
@endforeach
</x-mail::table>

@if (!$singular_eula && $group->first()->eula)
<hr>
{{ $group->first()->eula }}
@endif

</x-mail::panel>
@endforeach

@if ($singular_eula)
<x-mail::panel>
{{ $singular_eula }}
</x-mail::panel>
@endif

@if ($requires_acceptance)
{{ $requires_acceptance_prompt }}
@endif

**{{ trans('general.administrator') }}**: {{ $admin->display_name }}

{{ trans('mail.best_regards') }}<br>

{{ $snipeSettings->site_name }}
</x-mail::message>
