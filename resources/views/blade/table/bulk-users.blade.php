@aware(['name'])

<form
        method="POST"
        action="{{ route('users/bulkedit') }}"
        accept-charset="UTF-8"
        class="form-inline"
        id="{{ Illuminate\Support\Str::camel($name) }}Form"
>
    @csrf

    <div style="width:100% !important;" class="hidden-print">
        {{-- The sort and order will only be used if the cookie is actually empty (like on first-use) --}}
        <input name="sort" type="hidden" value="users.id">
        <input name="order" type="hidden" value="asc">
        <label for="bulk_actions">
            <span class="sr-only">
                {{ trans('button.bulk_actions') }}
            </span>
        </label>
        <select name="bulk_actions" class="form-control select2" aria-label="bulk_actions" style="width: 350px !important;">
            @can('update', \App\Models\User::class)
                <option value="edit">{{ trans('general.bulk_edit') }}</option>
                <option value="send_assigned">{{ trans('admin/users/general.email_assigned') }}</option>
            @endcan

            @can('delete', \App\Models\User::class)
                <option value="delete">{!! trans('general.bulk_checkin_delete') !!}</option>
                <option value="merge">{!! trans('general.merge_users') !!}</option>
            @endcan

            <option value="bulkpasswordreset">{{ trans('button.send_password_link') }}</option>
            <option value="print">{{ trans('admin/users/general.print_assigned') }}</option>
        </select>

        <button class="btn btn-theme" id="{{ Illuminate\Support\Str::camel($name) }}Button" disabled>{{ trans('button.go') }}</button>
    </div>
</form>
