<!-- Modal -->
<div class="modal fade" id="advancedSearchModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="modalCloseBtn">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalLabel"></h4>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Filter Name -->
                <div class="form-group">
                    <label for="filterName">{{ trans('general.predefined_filter_name') }}</label>
                    <input type="text" class="form-control" id="filterName"
                        placeholder="{{ trans('general.enter_predefined_filter_name') }}">
                </div>

                <!-- Visibility -->
                <div class="form-group">
                    <label>{{ trans('general.visibility') }}</label>
                    <div class="radio modal-radio">
                        <label>
                            <input type="radio" class="modal-radiobutton-label" name="visibility" value="public" checked>
                            <span class="modal-radiobutton-label">
                                {{ trans('general.public') }}
                            </span>
                        </label>
                    </div>
                    <div class="radio modal-radio">
                        <label>
                            <input type="radio" class="modal-radiobutton-label" name="visibility" value="private">
                            <span class="modal-radiobutton-label">
                                {{ trans('general.private') }}
                            </span>
                        </label>
                    </div>
                </div>
                @include ('partials.select.dropdowns.group-select', [
                    'translated_name' => trans('admin/hardware/form.model'),
                    'select_id' => "group_select",
                    'fieldname' => "groupSelect",
                    'required' => 'false',
                    'multiple' => 'true',
                ])
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="modalCancelBtn" data-dismiss="modal">
                    {{ trans('general.close') }}
                </button>
                <button type="button" class="btn btn-primary" id="modalSaveBtn">
                    <!-- Text set dynamically -->
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    let groupSelectDropdown = {};

    function openFilterCreateUpdateModal(createNew, name = null, permissionGroupResponses = []) {
        return new Promise((resolve, reject) => {
            const $modal = $('#advancedSearchModal');
            const $title = $('#modalLabel');
            const $input = $('#filterName');
            const $saveBtn = $('#modalSaveBtn');
            const $errorMsg = $('<p class="text-danger" id="nameError" style="display:none;"></p>');

            // Insert error message if not already present
            if ($('#nameError').length === 0) {
                $input.closest('.form-group').append($errorMsg);
            }

            // Reset input fields and UI
            $input.val(name || '').removeClass('is-invalid').css('border-color', '');
            $('#nameError').hide().text('');
            $('input[name="visibility"][value="public"]').prop('checked', true);

            // Set modal title and button text
            $title.text(createNew ? '{{ trans('general.create') }}' : '{{ trans('general.edit') }}');
            $saveBtn.text(createNew ? '{{ trans('general.save') }}' : '{{ trans('general.update') }}');

            const currentlySetFilterGroupIDs = permissionGroupResponses.map(group => group.id);
            groupSelectDropdown.clear();
            groupSelectDropdown.setValue(currentlySetFilterGroupIDs, "groups");

            // Show the modal
            $modal.modal('show');

            const onCancel = () => {
                cleanup();
                reject(new Error('Modal cancelled'));
            };

            const onSave = () => {
                const inputName = $input.val().trim();
                const permissionInput = groupSelectDropdown.getValue();

                // Validate input
                if (!inputName) {
                                    $input.addClass('is-invalid').css('border-color', '#d9534f');
                                      $('#nameError').text('{{ trans('general.validation_required') }}').show();
                                    return;
                }


                const permissions = permissionInput.map(id => ({
                    permission_group_id: id
                }));

                const inputData = {
                    name: inputName,
                    visibility: $('input[name="visibility"]:checked').val(),
                    permissions: permissions,
                };
                console.log("onsave");
                console.log(inputData);

                // Close modal before resolving
                $modal.modal('hide');

                // Delay resolve slightly to allow modal animation to complete
                setTimeout(() => {
                    cleanup();
                    resolve(inputData);
                }, 300);
            };

            const cleanup = () => {
                $saveBtn.off('click', onSave);
                $('#modalCancelBtn, #modalCloseBtn').off('click', onCancel);
                $modal.off('hidden.bs.modal', onCancel);
                $input.off('input');
                $('#nameError').hide();
                $input.removeClass('is-invalid').css('border-color', '');
            };

            $saveBtn.on('click', onSave);
            $('#modalCancelBtn, #modalCloseBtn').on('click', onCancel);
            $modal.on('hidden.bs.modal', onCancel);

            $input.on('input', () => {
                $input.removeClass('is-invalid').css('border-color', '');
                $('#nameError').hide();
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        groupSelectDropdown = new SelectFilterInput(document.getElementById("group_select"));
    }
);
</script>

<style>
    .modal-radiobutton-label {
        margin-left: 1vw;
    }
    .modal-radio {
        margin-bottom: 1.5vh;
    }
</style>