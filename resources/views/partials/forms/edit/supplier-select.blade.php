<div id="assigned_user" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}">

    <label for="{{ $fieldname }}" class="col-md-3 control-label">{{ $translated_name }}</label>

    <div class="col-md-7">
        @include('partials.select/dropdowns/supplier-select')
    </div>

    <div class="col-md-1 col-sm-1 text-left">
        @can('create', \App\Models\Supplier::class)
            @if (!isset($hide_new) || $hide_new != 'true')
                <a href='{{ route('modal.show', 'supplier') }}' data-toggle="modal" data-target="#createModal"
                    data-select='supplier_select' class="btn btn-sm btn-primary">{{ trans('button.new') }}</a>
            @endif
        @endcan
    </div>

    {!! $errors->first(
        $fieldname,
        '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>',
    ) !!}

</div>
