<div id="assigned_user" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}">

    <label for="{{ $fieldname }}" class="col-md-3 control-label">{{ $translated_name }}</label>

    <div class="col-md-7">
        <select class="js-data-ajax" data-endpoint="suppliers" data-placeholder="{{ trans('general.select_supplier') }}" name="{{ $fieldname }}" style="width: 100%" id="supplier_select" aria-label="{{ $fieldname }}"{{ (isset($multiple) && ($multiple=='true')) ? " multiple='multiple'" : '' }}{{ (isset($item) && (Helper::checkIfRequired($item, $fieldname))) ? ' required' : '' }}>
            @isset ($selected)
                @foreach ($selected as $supplier_id)
                    <option value="{{ $supplier_id }}" selected="selected" role="option" aria-selected="true">
                        {{ \App\Models\Supplier::find($supplier_id)->name }}
                    </option>
                @endforeach
            @endisset
            @if ($supplier_id = old($fieldname, (isset($item)) ? $item->{$fieldname} : ''))
                <option value="{{ $supplier_id }}" selected="selected" role="option" aria-selected="true" role="option">
                    {{ (\App\Models\Supplier::find($supplier_id)) ? \App\Models\Supplier::find($supplier_id)->name : '' }}
                </option>
            @endif
        </select>
    </div>

    <div class="col-md-1 col-sm-1 text-left">
        @can('create', \App\Models\Supplier::class)
            @if ((!isset($hide_new)) || ($hide_new!='true'))
                <a href='{{ route('modal.show', 'supplier') }}' data-toggle="modal"  data-target="#createModal" data-select='supplier_select' class="btn btn-sm btn-theme">{{ trans('button.new') }}</a>
            @endif
        @endcan
    </div>

    {!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>') !!}

</div>
