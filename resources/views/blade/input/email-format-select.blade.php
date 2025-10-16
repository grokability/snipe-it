@php
    $formats = [
        'firstname.lastname' => trans('admin/settings/general.email_formats.firstname_lastname_format'),
        'firstname' => trans('admin/settings/general.email_formats.first_name_format'),
        'lastname' => trans('admin/settings/general.email_formats.last_name_format'),
        'filastname' => trans('admin/settings/general.email_formats.filastname_format'),
        'lastnamefirstinitial' => trans('admin/settings/general.email_formats.lastnamefirstinitial_format'),
        'firstname_lastname' => trans('admin/settings/general.email_formats.firstname_lastname_underscore_format'),
        'firstinitial.lastname' => trans('admin/settings/general.email_formats.firstinitial_lastname'),
        'lastname_firstinitial' => trans('admin/settings/general.email_formats.lastname_firstinitial'),
        'lastname.firstinitial' => trans('admin/settings/general.email_formats.lastname_dot_firstinitial_format'),
        'firstnamelastname' => trans('admin/settings/general.email_formats.firstnamelastname'),
        'firstnamelastinitial' => trans('admin/settings/general.email_formats.firstnamelastinitial'),
        'lastname.firstname' => trans('admin/settings/general.email_formats.lastnamefirstname'),
    ];
@endphp

<x-input.select {{ $attributes }}>
    @foreach($formats as $format => $label)
        <option
            value="{{ $format }}"
            @selected($selected == $format)
        >
            {{ $label }}
        </option>
    @endforeach
</x-input.select>
