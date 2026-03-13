<!-- Asset Model -->
<div id="{{ $fieldname }}" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}">

    <label for="{{ $fieldname }}" class="col-md-3 control-label">{{ $translated_name }}</label>

    <div class="col-md-7">
        <select class="js-data-ajax" data-endpoint="statuslabels" data-placeholder="{{ trans('general.select_statuslabel') }}" name="{{ $fieldname }}" style="width: 100%" id="status_select_id" aria-label="{{ $fieldname }}" {!!  ((isset($item)) && (Helper::checkIfRequired($item, $fieldname))) ? ' required ' : '' !!}{{ (isset($multiple) && ($multiple=='true')) ? " multiple='multiple'" : '' }}>
            @isset ($selected)
                @foreach ($selected as $status_id)
                    <option value="{{ $status_id }}" selected="selected" role="option" aria-selected="true">
                        {{ \App\Models\Statuslabel::find($status_id)->name }}
                    </option>
                @endforeach
            @endisset
            @if ($status_id = old($fieldname,  (isset($item)) ? $item->{$fieldname} : ''))
                <option value="{{ $status_id }}" selected="selected" role="option" aria-selected="true"  role="option">
                    {{ (\App\Models\Statuslabel::find($status_id)) ? \App\Models\Statuslabel::find($status_id)->name : '' }}
                </option>
            @endif

        </select>
    </div>

    <div class="col-md-1 col-sm-1 text-left">
        @can('create', \App\Models\Statuslabel::class)
            @if ((!isset($hide_new)) || ($hide_new!='true'))
                <a href='{{ route('modal.show', 'statuslabel') }}' data-toggle="modal"  data-target="#createModal" data-select='status_select_id' class="btn btn-sm btn-theme">{{ trans('button.new') }}</a>
            @endif
        @endcan
    </div>


    {!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>') !!}
</div>
