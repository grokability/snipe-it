<!-- Location -->
<div id="{{ $fieldname }}" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}"{!!  (isset($style)) ? ' style="'.e($style).'"' : ''  !!}>

    <label for="{{ $fieldname }}" class="col-md-3 control-label">{{ $translated_name }}</label>
    <div class="col-md-7">
        <select class="js-data-ajax" data-endpoint="locations" data-placeholder="{{ trans('general.select_location') }}" name="{{ $fieldname }}" style="width: 100%" id="{{ $fieldname }}_location_select" aria-label="{{ $fieldname }}"{{ (isset($multiple) && ($multiple=='true')) ? " multiple='multiple'" : '' }}{!!  ((isset($item)) && (Helper::checkIfRequired($item, $fieldname))) ? ' required ' : '' !!}>
            @isset($selected)
                @foreach($selected as $location_id)
                    <option value="{{ $location_id }}" selected="selected" role="option" aria-selected="true"  role="option">
                        {{ (\App\Models\Location::find($location_id))->name }}
                    </option>
                @endforeach
            @endisset
            @if ($location_id = old($fieldname, (isset($item)) ? $item->{$fieldname} : ''))
                <option value="{{ $location_id }}" selected="selected" role="option" aria-selected="true"  role="option">
                    {{ (\App\Models\Location::find($location_id)) ? \App\Models\Location::find($location_id)->name : '' }}
                </option>
            @endif
        </select>
    </div>

    <div class="col-md-1 col-sm-1 text-left">
        @can('create', \App\Models\Location::class)
            @if ((!isset($hide_new)) || ($hide_new!='true'))
            <a href='{{ route('modal.show', 'location') }}' data-toggle="modal"  data-target="#createModal" data-select='{{ $fieldname }}_location_select' class="btn btn-sm btn-primary">{{ trans('button.new') }}</a>
            @endif
        @endcan
    </div>

    {!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>') !!}

    @if (isset($help_text))
    <div class="col-md-7 col-sm-11 col-md-offset-3">
        <p class="help-block">{{ $help_text }}</p>
    </div>
    @endif

    @if (isset($hide_location_radio))
    <!-- Update actual location  -->
    <div class="form-group">
        <div class="col-md-9 col-md-offset-3">
            <label class="form-control">
                <input name="update_default_location" type="radio" value="1" checked="checked" aria-label="update_default_location" />
                {{ trans('admin/hardware/form.asset_location') }}
            </label>
            <label class="form-control">
                <input name="update_default_location" type="radio" value="0" aria-label="update_default_location" />
                {{ trans('admin/hardware/form.asset_location_update_default_current') }}
            </label>
        </div>
    </div> <!--/form-group-->
    @endif

</div>



