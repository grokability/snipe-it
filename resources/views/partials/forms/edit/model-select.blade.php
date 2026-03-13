<!-- Asset Model -->
<div id="{{ $fieldname }}" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}">

    <label for="{{ $fieldname }}" class="col-md-3 control-label">{{ $translated_name }}</label>

    <div class="col-md-7">
        <select class="js-data-ajax" data-endpoint="models" data-placeholder="{{ trans('general.select_model') }}" name="{{ $fieldname }}" style="width: 100%" id="model_select_id" aria-label="{{ $fieldname }}"{{  ((isset($field_req)) || ((isset($required) && ($required =='true')))) ?  ' required' : '' }}{{ (isset($multiple) && ($multiple=='true')) ? " multiple='multiple'" : '' }}>
            @isset ($selected)
                @if (!is_iterable($selected))
                    @php
                        $selected = [$selected];
                    @endphp
                @endif
                @foreach ($selected as $model_id)
                    <option value="{{ $model_id }}" selected="selected" role="option" aria-selected="true">
                        {{ \App\Models\AssetModel::find($model_id)->name }}
                    </option>
                @endforeach
            @endisset
            @if ($model_id = old($fieldname, ($item->{$fieldname} ?? request($fieldname) ?? '')))
                <option value="{{ $model_id }}" selected="selected">
                    {{ (\App\Models\AssetModel::find($model_id)) ? \App\Models\AssetModel::find($model_id)->name : '' }}
                </option>
            @endif

        </select>
    </div>
    <div class="col-md-1 col-sm-1 text-left">
        @can('create', \App\Models\AssetModel::class)
            @if ((!isset($hide_new)) || ($hide_new!='true'))
                <a href='{{ route('modal.show', 'model') }}' data-toggle="modal"  data-target="#createModal" data-select='model_select_id' class="btn btn-sm btn-theme">{{ trans('button.new') }}</a>
                <span class="mac_spinner" style="padding-left: 10px; color: green; display:none; width: 30px;">
                    <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
                </span>
            @endif
        @endcan
    </div>

    {!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>') !!}
</div>
