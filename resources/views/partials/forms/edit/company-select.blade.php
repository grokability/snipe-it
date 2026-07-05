<!-- Company -->
<!-- When FMCS is enabled the companies selectlist API automatically scopes results to
     the current user's companies (primary + pivot), so no separate disabled branch is needed. -->
<div id="{{ $fieldname }}" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}">
    <label for="{{ $fieldname }}" class="col-md-3 control-label">{{ $translated_name }}</label>
    <div class="col-md-6">
        <select class="js-data-ajax" data-endpoint="companies" data-placeholder="{{ trans('general.select_company') }}"
                @isset($only_top_level) data-only-top-level="{{ $only_top_level ? 'true' : '' }}" @endisset
                @isset($exclude_id) data-exclude-id="{{ $exclude_id }}" @endisset
                name="{{ $fieldname }}{{ (isset($multiple) && ($multiple=='true')) ? '[]' : '' }}" style="width: 100%"{{ (isset($multiple) && ($multiple=='true')) ? " multiple='multiple'" : '' }}>
            @isset ($selected)
                @foreach ($selected as $company_id)
                    <option value="{{ $company_id }}" selected="selected" role="option" aria-selected="true">
                        {{ \App\Models\Company::find($company_id)->name }}
                    </option>
                @endforeach
            @endisset
            @if (!isset($multiple) || $multiple !== 'true')
                @if ($company_id = old($fieldname, (isset($item)) ? $item->{$fieldname} : ''))
                    <option value="{{ $company_id }}" selected="selected">
                        {{ (\App\Models\Company::find($company_id)) ? \App\Models\Company::find($company_id)->name : '' }}
                    </option>
                @else
                    <option value="" role="option">{{ trans('general.select_company') }}</option>
                @endif
            @endif
        </select>
    </div>
    @if ($snipeSettings->full_multiple_companies_support == '1')
        @cannot('superadmin')
            <div class="col-md-6 col-md-offset-3">
                <p class="help-block"><x-icon type="tip" /> {{ trans('general.fmcs_company_select_note') }}</p>
            </div>
        @endcannot
        @can('superadmin')
            <div class="col-md-6 col-md-offset-3">
                <p class="help-block"><x-icon type="tip" /> {{ trans('general.fmcs_company_select_superadmin_note') }}</p>
            </div>
        @endcan
    @endif

    {!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>') !!}
</div>
