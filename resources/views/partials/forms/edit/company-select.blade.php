<!-- Company -->
@if(!\App\Models\Company::canManageUsersCompanies())
    <!-- full company support is enabled, this user isn't a superadmin and has no mapped companies -->
    <div class="form-group">
        <label for="{{ $fieldname }}" class="col-md-3 control-label">{{ $translated_name }}</label>
        <div class="col-md-6">
            <select class="js-data-ajax" disabled="true" data-endpoint="companies" data-placeholder="{{ trans('general.select_company') }}" name="{{ $fieldname }}" style="width: 100%" id="company_select" aria-label="{{ $fieldname }}"{{ (isset($multiple) && ($multiple=='true')) ? " multiple='multiple'" : '' }}>
                @if ($company_id = old($fieldname, (isset($item)) ? $item->{$fieldname} : ''))
                    <option value="{{ $company_id }}" selected="selected" role="option" aria-selected="true"  role="option">
                        {{ (\App\Models\Company::find($company_id)) ? \App\Models\Company::find($company_id)->name : '' }}
                    </option>
                @else
                    {!! (!isset($multiple) || ($multiple=='false')) ? '<option value="" role="option">'.trans('general.select_company').'</option>' : ''  !!}
                @endif
            </select>
        </div>
    </div>
@else
    <!-- full company support is disabled or this user is a superadmin or has mapped companies -->
    <div id="{{ $fieldname }}" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}">
        <label for="{{ $fieldname }}" class="col-md-3 control-label">{{ $translated_name }}</label>
        <div class="col-md-8">
            <select class="js-data-ajax" data-endpoint="companies" data-placeholder="{{ trans('general.select_company') }}" name="{{ $fieldname }}" style="width: 100%" id="company_select"{{ (isset($multiple) && ($multiple=='true')) ? " multiple='multiple'" : '' }}>
                @isset ($selected)
                    @foreach ($selected as $company_id)
                        <option value="{{ $company_id }}" selected="selected" role="option" aria-selected="true">
                            {{ \App\Models\Company::find($company_id)->name }}
                        </option>
                    @endforeach
                @endisset
                @if ($company_id = old($fieldname, (isset($item)) ? $item->{$fieldname} : ''))
                    <option value="{{ $company_id }}" selected="selected">
                        {{ (\App\Models\Company::find($company_id)) ? \App\Models\Company::find($company_id)->name : '' }}
                    </option>
                @else
                    {!! (!isset($multiple) || ($multiple=='false')) ? '<option value="" role="option">'.trans('general.select_company').'</option>' : ''  !!}
                @endif
            </select>
        </div>
        {!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>') !!}

    </div>
@endif
