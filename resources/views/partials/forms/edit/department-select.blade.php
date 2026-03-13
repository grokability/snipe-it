<div id="assigned_user" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}">

    <label for="{{ $fieldname }}" class="col-md-3 control-label">{{ $translated_name }}</label>

    <div class="col-md-6">
        <select class="js-data-ajax" data-endpoint="departments" data-placeholder="{{ trans('general.select_department') }}" name="{{ $fieldname }}" style="width: 100%" id="department_select" aria-label="{{ $fieldname }}"{{ (isset($multiple) && ($multiple=='true')) ? " multiple='multiple'" : '' }}>
            @isset ($selected)
                @if (!is_iterable($selected))
                    @php
                        $selected = [$selected];
                    @endphp
                @endif
                @foreach ($selected as $department_id)
                    <option value="{{ $department_id }}" selected="selected" role="option" aria-selected="true">
                        {{ \App\Models\Department::find($department_id)->name }}
                    </option>
                @endforeach
            @endisset
            @if ($department_id = old($fieldname, (isset($item)) ? $item->{$fieldname} : ''))
                <option value="{{ $department_id }}" selected="selected" role="option" aria-selected="true"  role="option">
                    {{ (\App\Models\Department::find($department_id)) ? \App\Models\Department::find($department_id)->name : '' }}
                </option>
            @endif
        </select>
    </div>


    {!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>') !!}

</div>
