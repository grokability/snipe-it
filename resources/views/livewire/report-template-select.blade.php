<div class="form-group">
    <label for="saved_report_select">{{ trans('admin/reports/general.open_saved_template') }}</label>
    <select
        id="saved_report_select"
        class="form-control select2"
        data-placeholder="{{ trans('admin/reports/general.select_a_template') }}"
    >
        <option></option>
        @foreach($this->templates as $template)
            <option
                value="{{ $template->id }}"
                data-route="{{ route('report-templates.show', $template->id) }}"
                @selected($template->is(request()->route()->parameter('reportTemplate')))
            >
                {{ $template->name }}
            </option>
        @endforeach
    </select>
</div>
