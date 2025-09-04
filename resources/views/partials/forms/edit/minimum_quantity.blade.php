<!-- Min QTY -->
<div class="form-group{{ $errors->has('min_amt') ? ' has-error' : '' }}">
    <label for="min_amt" class="col-md-3 control-label">{{ trans('general.min_amt') }}</label>
    <div class="col-md-9">
       <div class="col-md-2" style="padding-left:0px">
           <input class="form-control col-md-3" maxlength="5" type="text" name="min_amt" id="min_amt"
                  aria-label="min_amt"
                  value="{{ old('min_amt', ($item->min_amt ?? '')) }}"
                   {{   (isset($item) && (Helper::checkIfRequired($item, 'min_amt')) ? ' required' : '') }}/>
        </div>
            <div class="col-md-7" style="margin-left: -15px;">

                <a href="#" data-tooltip="true" title="{{ trans('general.min_amt_help') }}">
                    <x-icon type="info-circle" />
                <span class="sr-only">{{ trans('general.min_amt_help') }}</span>
            </a>
        </div>
        <div class="col-md-12">
           {!! $errors->first('min_amt', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>
</div>
