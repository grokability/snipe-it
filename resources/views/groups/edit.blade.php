@extends('layouts/edit-form', [
    'createText' => trans('admin/groups/titles.create') ,
    'updateText' => trans('admin/groups/titles.update'),
    'item' => $group,
    'formAction' => ($group !== null && $group->id !== null) ? route('groups.update', ['group' => $group->id]) : route('groups.store'),

])
@section('content')

    <style nonce="{{ csrf_token() }}">
        .form-horizontal .control-label {
            padding-top: 0px;
        }

        input[type='text'][disabled], input[disabled], textarea[disabled], input[readonly], textarea[readonly], .form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control {
            background-color: white;
            color: #555555;
            cursor:text;
        }
        table.permissions {
            display:flex;
            flex-direction: column;
        }

        .permissions.table > thead, .permissions.table > tbody {
            margin: 15px;
            margin-top: 0px;
        }
        .permissions.table > tbody {
            border: 1px solid;
        }
        .header-row {
            border-bottom: 1px solid #ccc;
        }
        .permissions-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .table > tbody > tr > td.permissions-item {
            padding: 1px;
            padding-left: 8px;
        }

        .header-name {
            cursor: pointer;
        }

    </style>

@parent
@stop

@section('inputFields')
<!-- Name -->
<div class="form-group {{ $errors->has('name') ? ' has-error' : '' }}">
    <label for="name" class="col-md-2 control-label">{{ trans('admin/groups/titles.group_name') }}</label>
    <div class="col-md-9 required">
        <input class="form-control" type="text" name="name" id="name" value="{{ old('name', $group->name) }}" required />
        {!! $errors->first('name', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>
</div>

<div class="form-group{!! $errors->has('notes') ? ' has-error' : '' !!}">
    <label for="notes" class="col-md-2 control-label">{{ trans('general.notes') }}</label>
    <div class="col-md-9">
        <x-input.textarea
                name="notes"
                id="notes"
                :value="old('notes', $group->notes)"
                placeholder="{{ trans('general.placeholders.notes') }}"
                aria-label="notes"
                rows="2"
        />

        {!! $errors->first('notes', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>
</div>

<div class="col-md-12">

    <table class="table table-striped permissions">
        <thead>
        <tr class="permissions-row">
            <th class="col-md-5">{{ trans('admin/groups/titles.permission')}}</th>
            <th class="col-md-1">{{ trans('admin/groups/titles.grant')}}</th>
            <th class="col-md-1">{{ trans('admin/groups/titles.deny')}}</th>
        </tr>
        </thead>
        @foreach ($permissions as $area => $area_permission)
            <!-- handle superadmin and reports, and anything else with only one option -->
            <?php $localPermission = $area_permission[0]; ?>
            @if (count($area_permission) == 1)
            <tbody class="permissions-group">
                <tr class="header-row permissions-row">
                    <td class="col-md-5 tooltip-base permissions-item"
                        data-tooltip="true"
                        data-placement="right"
                        title="{{ $localPermission['note'] }}">
                            @unless (empty($localPermission['label']))
                                <h2>{{ $area . ': ' . $localPermission['label'] }}</h2>
                            @else
                                <h2>{{ $area }}</h2>
                            @endunless
                    </td>
                    <td class="col-md-1 permissions-item">
                        <label for="{{ 'permission['.$localPermission['permission'].']' }}" style="form-control"><span class="sr-only">{{ trans('admin/groups/titles.allow')}} {{ 'permission['.$localPermission['permission'].']' }}</span></label>
                        <input
                            value="1"
                            aria-label="permission[{{ $localPermission['permission'] }}]"
                            @checked(array_key_exists($localPermission['permission'], $groupPermissions) && $groupPermissions[$localPermission['permission']] == '1')
                            name="permission[{{ $localPermission['permission'] }}]"
                            type="radio"
                        >
                    </td>
                    <td class="col-md-1 permissions-item">
                        <label for="{{ 'permission['.$localPermission['permission'].']' }}"><span class="sr-only">{{ trans('admin/groups/titles.deny')}} {{ 'permission['.$localPermission['permission'].']' }}</span></label>
                        <input
                            value="0"
                            aria-label="permission[{{ $localPermission['permission'] }}]"
                            @checked(array_key_exists($localPermission['permission'], $groupPermissions) && $groupPermissions[$localPermission['permission']] == '0')
                            name="permission[{{ $localPermission['permission'] }}]"
                            type="radio"
                        >
                    </td>
                </tr>
            </tbody>
            @else
            <tbody class="permission-group">
                <tr class="header-row permissions-row">
                    <td class="col-md-5 tooltip-base permissions-item header-name"
                        data-tooltip="true"
                        data-placement="right"
                        title="{{ $localPermission['note'] }}">
                        <h2>{{ $area }}</h2>


                    </td>
                    <td class="col-md-1 permissions-item" style="vertical-align: bottom">
                        <label for="{{ $area }}"><span class="sr-only">{{ trans('admin/groups/titles.allow')}} {{ $area }}</span></label>
                        <input
                            value="1"
                            data-checker-group="{{ str_slug($area) }}"
                            aria-label="{{ $area }}"
                            name="{{ $area }}"
                            type="radio"
                        >
                    </td>
                    <td class="col-md-1 permissions-item">
                        <label for="{{ $area }}"><span class="sr-only">{{ trans('admin/groups/titles.deny')}} {{ $area }}</span></label>
                        <input
                            value="0"
                            data-checker-group="{{ str_slug($area) }}"
                            aria-label="{{ $area }}"
                            name="{{ $area }}"
                            type="radio"
                        >
                    </td>
                </tr>

                @foreach ($area_permission as $index => $this_permission)
                    @if ($this_permission['display'])
                    <tr class="permissions-row">
                        <td
                                class="col-md-5 tooltip-base permissions-item"
                                data-tooltip="true"
                                data-placement="right"
                                title="{{ $this_permission['note'] }}">
                                {{ $this_permission['label'] }}
                        </td>
                        <td class="col-md-1 permissions-item">
                            <label for="{{ 'permission['.$this_permission['permission'].']' }}"><span class="sr-only">{{ trans('admin/groups/titles.allow')}} {{ 'permission['.$this_permission['permission'].']' }}</span></label>
                            <input
                                class="radiochecker-{{ str_slug($area) }}"
                                aria-label="permission[{{ $this_permission['permission'] }}]"
                                @checked(array_key_exists($this_permission['permission'], $groupPermissions) && $groupPermissions[$this_permission['permission']] == '1')
                                name="permission[{{ $this_permission['permission'] }}]"
                                type="radio"
                                value="1"
                            >
                        </td>
                        <td class="col-md-1 permissions-item">
                            <label for="{{ 'permission['.$this_permission['permission'].']' }}"><span class="sr-only">{{ trans('admin/groups/titles.deny')}} {{ 'permission['.$this_permission['permission'].']' }}</span></label>
                            <input
                                class="radiochecker-{{ str_slug($area) }}"
                                aria-label="permission[{{ $this_permission['permission'] }}]"
                                @checked(array_key_exists($this_permission['permission'], $groupPermissions) && $groupPermissions[$this_permission['permission']] == '0')
                                name="permission[{{ $this_permission['permission'] }}]"
                                type="radio"
                                value="0"
                            >
                        </td>

                    </tr>

                    @endif
                @endforeach

            @endif
        </tbody>
        @endforeach
    </table>

</div>
@stop
@section('moar_scripts')

    <script nonce="{{ csrf_token() }}">

        $(document).ready(function(){

            $('.tooltip-base').tooltip({container: 'body'});
            $(".superuser").change(function() {
                var perms = $(this).val();
                if (perms =='1') {
                    $("#nonadmin").hide();
                } else {
                    $("#nonadmin").show();
                }
            });

            // Check/Uncheck all radio buttons in the group
            $('tr.header-row input:radio').change(function() {
                value = $(this).attr('value');
                area = $(this).data('checker-group');
                $('.radiochecker-'+area+'[value='+value+']').prop('checked', true);
            });


            $('.header-name').click(function() {
                $(this).parent().nextUntil('tr.header-row').slideToggle(500);
            });


        });


    </script>
@stop
