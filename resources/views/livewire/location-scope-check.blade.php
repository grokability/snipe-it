<div>
    <label class="form-control{{ (!$is_tested || count($mismatched) > 0 ) ? ' form-control--disabled' : '' }}">
        <input type="checkbox" name="scope_locations_fmcs" value="1"
               @checked(old('scope_locations_fmcs', $setting->scope_locations_fmcs)) aria-label="scope_locations_fmcs" {{ (!$is_tested || count($mismatched) > 0) ? ' disabled' : '' }}/>
        {{ trans('admin/settings/general.scope_locations_fmcs_support_text') }}
    </label>
    <p class="help-block">
        {{ trans('admin/settings/general.scope_locations_fmcs_support_help_text') }}
        <strong>{{ ($is_tested && count($mismatched) > 0) ? trans('admin/settings/general.scope_locations_fmcs_support_disabled_text', ['count' => count($mismatched)]) : '' }}</strong>
        @if(!$is_tested)
            <strong>{{ trans('admin/settings/general.scope_locations_fmcs_test_needed') }}</strong>
        @endif
    </p>
    <button class="btn btn-sm btn-default" wire:click.prevent="check_locations">{{ trans('admin/settings/general.scope_locations_fmcs_check_button') }}</button>
</div>