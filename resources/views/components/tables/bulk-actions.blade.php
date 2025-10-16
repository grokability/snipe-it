@props([
        'id_divname',
        'id_formname',
        'id_button',
        'action_route',
        'action_method',
        'model_name' => 'asset',
    ])

<div id="{{ $id_divname }}" style="min-width:400px">
    <form
            method="POST"
            action="{{ $action_route }}"
            accept-charset="UTF-8"
            class="form-inline"
            id="{{  $id_formname }}"
    >
        @csrf

        {{--        The sort and order will only be used if the cookie is actually empty (like on first-use)--}}
        <input name="sort" type="hidden" value="{{`$model_name.id`}}">
        <input name="order" type="hidden" value="asc">
        <label for="bulk_actions">
            <span class="sr-only">
                {{ trans('button.bulk_actions') }}
            </span>
        </label>
        <select name="bulk_actions" class="form-control select2" aria-label="bulk_actions" style="min-width: 350px;">
            {{ $slot }}
        </select>

        <button class="btn btn-primary" id="{{ $id_button }}"
                disabled>{{ trans('button.go') }}</button>
    </form>
</div>