<!-- Company -->
@if (($snipeSettings->full_multiple_companies_support=='1') && (!Auth::user()->isSuperUser()))
    <!-- full company support is enabled and this user isn't a superadmin -->
    <div class="form-group">
        <label for="{{ $fieldname }}" class="col-md-3 control-label">{{ $translated_name }}</label>
        <div class="col-md-6">
            @include('partials.select/dropdowns/company-select', [
                'fieldname' => $fieldname,
                'translated_name' => $translated_name,
                'item' => $item ?? null,
                'multiple' => $multiple ?? 'false',
                'selected' => $selected ?? null,
                'disabled' => true
            ])
        </div>
    </div>

@else
    <!-- full company support is enabled or this user is a superadmin -->
    <div id="{{ $fieldname }}" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}">
        <label for="{{ $fieldname }}" class="col-md-3 control-label">{{ $translated_name }}</label>
        <div class="col-md-8">
            @include('partials.select/dropdowns/company-select', [
                'fieldname' => $fieldname,
                'translated_name' => $translated_name,
                'item' => $item ?? null,
                'multiple' => $multiple ?? 'false',
                'selected' => $selected ?? null,
                'disabled' => false
            ])
        </div>
        {!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>') !!}

    </div>

@endif
