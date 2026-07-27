@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.bulk_checkin_delete') }}
    @parent
@stop

{{-- Page content --}}
@section('content')
    <x-container class="col-md-8 col-md-offset-2">
        <x-form route="{{ route('users/bulksave') }}">
            <x-box>
                <div class="col-md-12">
                    <x-callout type="danger" icon="warning" live="assertive">
                        <strong>{{ trans('admin/users/general.warning_deletion_information', ['count' => count($users)]) }}</strong>
                    </x-callout>
                </div>

                <div class="col-md-12">
                    <x-demo-callout />
                </div>

                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="display table table-striped">
                            <thead>
                                <tr>
                                    <td colspan="8">
                                        <x-input.select
                                            name="status_id"
                                            id="status_id"
                                            :options="$statuslabel_list"
                                            :selected="old('status_id')"
                                            required
                                            style="width:350px"
                                            aria-label="status_id"
                                            data-gates-submit
                                        />
                                        <label>
                                            {{ trans('admin/users/general.update_user_assets_status') }}
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="8">
                                        <label class="form-control">
                                            <input type="checkbox" name="delete_user" value="1">
                                            <span class="text-warning">
                                                <x-icon type="warning" />
                                                <strong>{{ trans('general.optional') }}: {{ trans('general.bulk_soft_delete') }}</strong>
                                            </span>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="col" class="col-md-1"></th>
                                    <th scope="col" class="col-md-3">{{ trans('general.name') }}</th>
                                    <th scope="col" class="col-md-3">{{ trans('general.groups') }}</th>
                                    <th scope="col" class="text-right" data-tooltip="true" data-title="{{ trans('general.assets') }}">
                                        <i class="fas fa-barcode fa-fw" aria-hidden="true" style="font-size: 17px;"></i>
                                        <span class="sr-only">{{ trans('general.assets') }}</span>
                                    </th>
                                    <th scope="col" class="text-right" data-tooltip="true" data-title="{{ trans('general.accessories') }}">
                                        <i class="far fa-keyboard fa-fw" aria-hidden="true" style="font-size: 17px;"></i>
                                        <span class="sr-only">{{ trans('general.accessories') }}</span>
                                    </th>
                                    <th scope="col" class="text-right" data-tooltip="true" data-title="{{ trans('general.licenses') }}">
                                        <i class="far fa-save fa-fw" aria-hidden="true" style="font-size: 17px;"></i>
                                        <span class="sr-only">{{ trans('general.licenses') }}</span>
                                    </th>
                                    <th scope="col" class="text-right" data-tooltip="true" data-title="{{ trans('general.consumables') }}">
                                        <i class="fas fa-tint fa-fw" aria-hidden="true" style="font-size: 17px;"></i>
                                        <span class="sr-only">{{ trans('general.consumables') }}</span>
                                    </th>
                                    <th scope="col" class="text-right" data-tooltip="true" data-title="{{ trans('general.files') }}">
                                        <i class="fas fa-paperclip fa-fw" aria-hidden="true" style="font-size: 17px;"></i>
                                        <span class="sr-only">{{ trans('general.files') }}</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>
                                            @if (Auth::id() != $user->id)
                                                <input type="checkbox" name="ids[]" value="{{ $user->id }}" checked>
                                            @else
                                                <input type="checkbox" name="ids[]" class="cannot_delete" value="{{ $user->id }}" disabled>
                                            @endif
                                        </td>
                                        <td>
                                            @if (auth()->user()->id == $user->id)
                                                <span style="text-decoration: line-through" class="text-danger">
                                                    {{ $user->display_name }} ({{ $user->username }})
                                                </span>
                                            @elseif ($user->isSuperUser())
                                                <span class="text-danger">
                                                    <i class="fas fa-crown text-danger"></i> {{ $user->display_name }} ({{ $user->username }})
                                                </span>
                                            @elseif ($user->isAdmin())
                                                <span class="text-warning">
                                                    <i class="fas fa-crown text-warning"></i> {{ $user->display_name }} ({{ $user->username }})
                                                </span>
                                            @else
                                                {{ $user->display_name }} ({{ $user->username }})
                                            @endif

                                            @if (auth()->user()->id == $user->id)
                                                <i class="fas fa-x text-danger"></i> {{ trans('tooltips.disabled_assoc.user_self') }}
                                            @endif
                                        </td>
                                        <td>
                                            @foreach ($user->groups as $group)
                                                <a href="{{ route('groups.update', $group->id) }}" class="label label-default">
                                                    {{ $group->name }}
                                                </a>&nbsp;
                                            @endforeach
                                        </td>
                                        <td class="text-right">{{ number_format($user->assets->count()) }}</td>
                                        <td class="text-right">{{ number_format($user->accessories->count()) }}</td>
                                        <td class="text-right">{{ number_format($user->licenses->count()) }}</td>
                                        <td class="text-right">{{ number_format($user->consumables->count()) }}</td>
                                        <td class="text-right">{{ number_format($user->uploads->count()) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <x-slot:customfooter>
                    <div class="box-footer text-right">
                        <a class="btn btn-link pull-left" href="{{ URL::previous() }}">{{ trans('button.cancel') }}</a>
                        <button type="submit" class="btn btn-success" disabled>
                            <x-icon type="checkmark" /> {{ trans('button.submit') }}
                        </button>
                    </div>
                </x-slot:customfooter>
            </x-box>
        </x-form>
    </x-container>
@stop
