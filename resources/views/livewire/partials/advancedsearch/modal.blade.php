<span id="advancedSearchModalContainer">
    @if ($showModal)
        {{-- CSS --}}
        <style>
            .modal-radio {
                margin-bottom: 1.5vh;
            }

            .radio-label-text {
                margin-left: 0.5em; /* adjust as needed */
                display: inline-block;
            }

            .capitalizeFirstLetter {
                text-transform: capitalize;
            }

            .sr-only {
                position: absolute !important;
                width: 1px !important;
                height: 1px !important;
                margin: -1px !important;
                padding: 0 !important;
                overflow: hidden !important;
                clip: rect(0,0,0,0) !important;
                border: 0 !important;
            }
        </style>

        <div
            @class(["modal", "fade", "in"])
            id="advancedSearchModal"
            tabindex="-1"
            role="dialog"
            aria-labelledby="modalLabel"
            aria-hidden="false"
            style="display: block"
            {{-- Show immediately --}}
            wire:ignore.self
        >
            <div
                @class(["modal-dialog"])
                role="document"
            >
                <div @class(["modal-content"])>
                    <!-- Header -->
                    <div @class(["modal-header"])>
                        <button
                            type="button"
                            @class(["close"])
                            wire:click="closePredefinedFiltersModal"
                        >
                            <span>&times;</span>
                        </button>

                        @if ($modalActionType === App\Livewire\Partials\Advancedsearch\AdvancedsearchModalAction::Create)
                            <h4 class="modal-title">{{ __("general.create") }}</h4>
                        @elseif ($modalActionType === App\Livewire\Partials\Advancedsearch\AdvancedsearchModalAction::Edit)
                            <h4 class="modal-title capitalizeFirstLetter">{{ __("general.edit") }}</h4>
                        @elseif ($modalActionType === App\Livewire\Partials\Advancedsearch\AdvancedsearchModalAction::Delete)
                            <h4 class="modal-title">{{ __("general.delete") }}</h4>
                        @endif
                    </div>

                    <!-- Body -->
                    <div @class(["modal-body"]) wire:ignore>
                        @if ($modalActionType !== App\Livewire\Partials\Advancedsearch\AdvancedsearchModalAction::Delete)
                        {{-- Filter name --}}
                        <div
                            @class(["form-group"])
                            wire:ignore.self
                        >
                            <label for="filterName">{{ trans("general.predefined_filter_name") }}</label>
                            <input
                                type="text"
                                @class(["form-control"])
                                id="filterName"
                                wire:model.defer="name"
                            />
                        </div>

                        {{-- Visibility --}}
                        <div class="form-group" x-init="$nextTick(() => { window.advancedSearchModalSetGroupSelectDisabled(true) })">
                            <label>Visibility</label>

                            <div class="radio modal-radio">
                                <label>
                                    <input
                                        type="radio"
                                        name="visibility"
                                        value="public"
                                        wire:model="visibility"
                                        @change="window.advancedSearchModalSetGroupSelectDisabled(false)"
                                        @checked(old("active", $this->visibility === App\Livewire\Partials\Advancedsearch\FilterVisibility::Public))
                                    />
                                    <span class="radio-label-text">Public</span>
                                </label>
                            </div>

                            <div class="radio modal-radio">
                                <label>
                                    <input
                                        type="radio"
                                        name="visibility"
                                        value="private"
                                        wire:model="visibility"
                                        @change="window.advancedSearchModalSetGroupSelectDisabled(true)"
                                        @checked(old("active", $this->visibility === App\Livewire\Partials\Advancedsearch\FilterVisibility::Private))
                                    />
                                    <span class="radio-label-text">Private</span>
                                </label>
                            </div>
                        </div>

                        {{-- Group Select --}}
                        @include(
                            "partials.select.dropdowns.group-select",
                            [
                                "translated_name" => trans("admin/hardware/form.model"),
                                "select_id" => "group_select",
                                "fieldname" => "groupSelect",
                                "required" => "false",
                                "multiple" => "true",
                                "selected" => $groupSelect,
                                "otherOptions" => $groupSelectOtherOptions,
                            ]
                        )
                    @endif
                    </div>

                    <!-- Footer -->
                    <div @class(["modal-footer"])>
                        <button
                            type="button"
                            @class(["btn", "btn-default"])
                            wire:click="closePredefinedFiltersModal"
                        >
                            {{ trans("general.close") }}
                        </button>
                        @if ($modalActionType === App\Livewire\Partials\Advancedsearch\AdvancedsearchModalAction::Create)
                        <button
                            type="button"
                            id="submitButton"
                            @class(["btn", "btn-primary"])
                            @click="window.advancedSearchModalSendInputToBackend('create');"
                        >
                                {{ trans("general.save") }}
                        </button>
                        @elseif ($modalActionType === App\Livewire\Partials\Advancedsearch\AdvancedsearchModalAction::Edit)
                        <button
                            type="button"
                            id="submitButton"

                            @class(["btn", "btn-primary", "capitalizeFirstLetter"])
                            @click="window.advancedSearchModalSendInputToBackend('edit');"
                        >
                                {{ trans("general.edit") }}
                        </button>
                        @elseif ($modalActionType === App\Livewire\Partials\Advancedsearch\AdvancedsearchModalAction::Delete)
                        <button
                            type="button"
                            id="submitButton"
                            @class(["btn", "btn-primary"])
                            wire:click="deletePredefinedFiltersModal"
                        >
                                {{ trans("general.delete") }}
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Javascript --}}
        @script
        <script>
            window.advancedSearchModalSendInputToBackend = function(action) {

                let selectedGroups = $("#group_select").select2('data');
                selectedGroups = selectedGroups.map((item) => { return parseInt(item.id); }); 
                const component = Livewire.getByName('partials.advancedsearch.modal')[0];
                
                if (component) {
                    component.set('groupSelect', selectedGroups);
                } else {
                    console.error('Livewire component not found!');
                }
                
                if(action === 'create')
                {
                    Livewire.dispatch('savePredefinedFiltersModal');
                } else if(action === 'edit')
                {
                    Livewire.dispatch('updatePredefinedFiltersModal');
                } else {
                    console.warn(`${action} is an unkown action type`);
                }
            }

            window.advancedSearchModalSetGroupSelectDisabled = function(disabled) {
                const element = document.getElementById("group_select").disabled = disabled; 
            }

        </script>
        @endscript
        @endif
</span>
