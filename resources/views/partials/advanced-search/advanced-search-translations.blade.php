<script type="module">
import { container } from '/js/dist/simpleDIContainer.min.js';
const AdvancedSearchTranslations = {
    general_close_filters: "{{ trans('general.close_filters') }}" ,
    general_error: "{{ trans('general.error') }}",
    general_failed_to_apply_predefined_filter: "{{ trans('general.failed_to_apply_predefined_filter') }}",
    general_can_not_save_empty_filter: "{{ trans('general.can_not_save_empty_filter') }}",
    general_can_not_update_empty_filter: "{{ trans('general.can_not_update_empty_filter') }}",
    general_open_filters: "{{ trans('general.open_filters') }}",
};
container.register("advancedSearchTranslations", AdvancedSearchTranslations);
</script>
