<span id=advancedSearchModalContainer>
    @if ($showModal)
    
    {{-- CSS --}}
    <style>
        .modal-radiobutton-label {
            margin-left: 1vw;
        }
        .modal-radio {
            margin-bottom: 1.5vh;
        }
    </style>

    <div
    @class(['modal', 'fade', 'in'])
    id="advancedSearchModal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="modalLabel"
    aria-hidden="false"
    style="display: block;" {{-- Show immediately --}}
    wire:ignore.self
    >
        <div @class(['modal-dialog']) role="document">
            <div @class(['modal-content'])>

                <!-- Header -->
                <div @class(['modal-header'])>
                    <button type="button" @class(['close']) wire:click="closeModal">
                        <span>&times;</span>
                    </button>
                    <h4 @class(['modal-title'])>{{ trans('general.predefined_filter_name') }}</h4>
                </div>

                <!-- Body -->
                <div @class(['modal-body'])>
                    {{-- Filter name --}}
                    <div @class(['form-group'])>
                        <label for="filterName">{{ trans('general.predefined_filter_name') }}</label>
                        <input type="text" @class(['form-control']) id="filterName" wire:model.defer="name">
                    </div>

                    {{-- Visibility --}}
                    <div @class(['form-group'])>
                        <label>{{ trans('general.visibility') }}</label>
                        <div @class(['radio', 'modal-radio'])>
                            <label>
                                <input type="radio" name="visibility" value="public"
                                    wire:click="$set('visibility', 'Public')"
                                    {{ $visibility === \App\Livewire\Partials\Advancedsearch\FilterVisibility::Public ? 'checked' : '' }}>
                                {{ trans('general.public') }}
                            </label>
                        </div>
                       <div @class(['radio', 'modal-radio'])>
                           <label>
                               <input type="radio" name="visibility" value="private"
                                   wire:click="$set('visibility', 'Private')"
                                   {{ $visibility === \App\Livewire\Partials\Advancedsearch\FilterVisibility::Private ? 'checked' : '' }}>
                               {{ trans('general.private') }}
                           </label>
                       </div>
                   </div>

                   {{-- Group Select --}}
                  @include('partials.select.dropdowns.group-select', [
                      'translated_name' => trans('admin/hardware/form.model'),
                      'select_id' => 'group_select',
                      'fieldname' => 'groupSelect',
                      'required' => 'false',
                      'multiple' => 'true',
                  ])
              </div>

              <!-- Footer -->
              <div @class(['modal-footer'])>
                  <button type="button" @class(['btn', 'btn-default']) wire:click="closePredefinedFiltersModal">
                      {{ trans('general.close') }}
                  </button>
                  <button type="button" @class(['btn', 'btn-primary'])>
                     {{ trans('general.save') }}
                 </button>
             </div>

         </div>
     </div>
    </div>

    @endif
</span>