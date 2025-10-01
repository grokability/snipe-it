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

                        @if ($modalActionType === App\Livewire\Partials\Advancedsearch\AdvancedsearchModalAction::Edit)
                            <h4 class="modal-title">{{ __("general.edit") }}</h4>
                        @else
                            <h4 class="modal-title">{{ __("general.create") }}</h4>
                        @endif
                    </div>

                    <!-- Body -->
                    <div @class(["modal-body"]) wire:ignore>
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
                        <div class="form-group">
                            <label>Visibility</label>

                            <div class="radio modal-radio">
                                <label>
                                    <input
                                        type="radio"
                                        name="visibility"
                                        value="public"
                                        wire:model="visibility"
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
                        <button
                            type="button"
                            id="submitButton"
                            @class(["btn", "btn-primary"])
                            @click="window.advancedSearchModalSendInputToBackend();"
                        >
                            @if ($modalActionType === App\Livewire\Partials\Advancedsearch\AdvancedsearchModalAction::Edit)
                                {{ trans("general.update") }}
                            @else
                                {{ trans("general.save") }}
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Javascript --}}
        @script
        <script>
            window.advancedSearchModalSendInputToBackend = function() {
                console.log("submit");
                const component = Livewire.getByName('partials.advancedsearch.modal')[0];
                
                if (component) {
                    component.set('groupSelect', [2, 3, 4, 5]);
                } else {
                    console.error('Livewire component not found!');
                }
                
                Livewire.dispatch('savePredefinedFiltersModal');
            }
            </script>
        @endscript
        @endif
</span>
