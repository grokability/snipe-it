
@foreach ($permissions as $main_section => $main_section_permission)

  <!-- handle superadmin and reports, and anything else with only one option -->
  @php
    // Ugh, this sucks, but we need to special case reports to map to reports.view
    $sectionPermission = $main_section_permission[0];
    if ((str_slug($main_section)) == 'reports') {
        $section_name = 'reports.view';
    } else {
        $section_name =  str_slug($main_section);
    }
  @endphp
  <div class="form-group {{ ($sectionPermission['permission']!='superuser') ? ' nonsuperuser' : '' }}{{ ( ($sectionPermission['permission']!='superuser') && ($sectionPermission['permission']!='admin')) ? ' nonadmin' : '' }}">

      <!-- start callout legend for major sections -->
      <div class="callout callout-legend col-md-12">
          
         <!-- start left column with area name and note -->
          <div class="col-md-10">

              <h4 id="{{ str_slug($sectionPermission['permission'])}}" class="{{ (count($main_section_permission) > 1) ? 'remember-toggle': '' }}">
                @if (count($main_section_permission) > 1)
                  <x-icon type="caret-down" class="fa-fw" id="toggle-arrow-{{ str_slug($sectionPermission['permission'])}}" />
                @endif
                {{ trans('permissions.'.str_slug($main_section).'.name') }}
              </h4>

                @if (\Lang::has('permissions.'.str_slug($main_section).'.note'))
                  <p>{{ trans('permissions.'.str_slug($main_section).'.note') }}</p>
                @endif
          </div>
          <!-- end left column with area name and note -->

          <!-- Handle the checkall ALLOW and DENY radios in the right column -->
          <div class="col-md-2 text-right header-row">
            <div class="radio-toggle-wrapper">

              <!-- start .radio-slider-inputs allow -->
              <div class="radio-slider-inputs" data-tooltip="true" title="{{ (count($main_section_permission) > 1) ? trans('permissions.grant_all', ['area' => $main_section])  : trans('permissions.grant', ['area' => $main_section]) }}">
                <input
                        class="form-control {{ str_slug($main_section) }} allow"
                        data-checker-group="{{ str_slug($main_section) }}"
                        aria-label="{{ str_slug($main_section) }}"
                        name="permission[{{ $section_name }}]"
                        @checked(array_key_exists($section_name, $groupPermissions) && $groupPermissions[$section_name] == '1')
                        type="radio"
                        value="1"
                        {{-- Disable the superuser and admin allow if the user is not a superuser --}}
                        @if (((str_slug($main_section) == 'admin') && (!auth()->user()->hasAccess('admin'))) || ((str_slug($main_section) == 'superuser') && (!auth()->user()->isSuperUser())))
                          disabled
                        @endif
                        id="{{ str_slug($main_section) }}_allow"
                >

                <label class="allow" for="{{ str_slug($main_section) }}_allow">
                  <i class="fa-solid fa-square-check"></i>
                </label>
              </div>
              <!-- end .radio-slider-inputs allow -->

              <!-- start .radio-slider-inputs inherit if used -->
              @if ($use_inherit)
                <div class="radio-slider-inputs" data-tooltip="true" title="{{ (count($main_section_permission) > 1) ? trans('permissions.inherit_all', ['area' => $main_section])  : trans('permissions.inherit', ['area' => $main_section]) }}">
                  <input
                          class="form-control  {{ str_slug($main_section) }} inherit"
                          data-checker-group="{{ str_slug($main_section) }}"
                          aria-label="{{ str_slug($main_section) }}"
                          name="permission[{{ str_slug($main_section) }}]"
                          @checked((array_key_exists(str_slug($main_section), $groupPermissions) && $groupPermissions[str_slug($main_section)] == '0') || (!array_key_exists(str_slug($main_section), $groupPermissions)))
                          type="radio"
                          value="0"
                          {{-- Disable the superuser and admin allow if the user is not a superuser --}}
                          @if (((str_slug($main_section) == 'admin') && (!auth()->user()->hasAccess('admin'))) || ((str_slug($main_section) == 'superuser') && (!auth()->user()->isSuperUser())))
                            disabled
                          @endif
                          id="{{ str_slug($main_section) }}_inherit"
                  >

                  <label class="inherit" for="{{ str_slug($main_section) }}_inherit">
                    <i class="fa-solid fa-layer-group"></i>
                  </label>
                </div>
              @endif
              <!-- end .radio-slider-inputs inherit if used -->

              <!-- start .radio-slider-inputs deny -->
              <div class="radio-slider-inputs" data-tooltip="true" title="{{ (count($main_section_permission) > 1) ? trans('permissions.deny_all', ['area' => $main_section])  : trans('permissions.deny', ['area' => $main_section]) }}">
                <input
                        class="form-control  {{ str_slug($main_section) }} deny"
                        data-checker-group="{{ str_slug($main_section) }}"
                        aria-label="{{ str_slug($main_section) }}"
                        name="permission[{{ $section_name }}]"
                        @checked(array_key_exists(str_slug($main_section), $groupPermissions) && $groupPermissions[str_slug($main_section)] == '-1')
                        type="radio"
                        value="-1"
                        {{-- Disable the superuser and admin allow if the user is not a superuser --}}
                        @if (((str_slug($main_section) == 'admin') && (!auth()->user()->hasAccess('admin'))) || ((str_slug($main_section) == 'superuser') && (!auth()->user()->isSuperUser())))
                          disabled
                        @endif
                        id="{{ str_slug($main_section) }}_deny"
                >

                <label class="deny" for="{{ str_slug($main_section) }}_deny">
                  <i class="fa-solid fa-square-xmark"></i>
                </label>
              </div>
                <!-- end .radio-slider-inputs deny -->

            </div> <!-- end .radio-toggle-wrapper -->
          </div> <!-- end right column radios -->

    </div> <!-- end callout legend for major sections -->
  </div> <!-- end form row -->

  <!-- now handle sub-permissions if they exist -->
  @if (count($main_section_permission) > 2)
      <div
           class="toggle-content-{{ str_slug($sectionPermission['permission']) }} {{ str_slug($sectionPermission['permission']) }}
          {{ ($sectionPermission['permission']!='superuser') ? ' nonsuperuser' : '' }}{{ ( ($sectionPermission['permission']!='superuser') && ($sectionPermission['permission']!='admin')) ? ' nonadmin' : '' }}"
      >

                @foreach ($main_section_permission as $index => $this_permission)
                  @if ($this_permission['display'])

                    @php
                        $section_translation = trans('permissions.'.str_slug($this_permission['permission']).'.name');
                    @endphp

                      <div class="form-group" style="border-bottom: 1px solid #eee; padding-right: 13px;">
                        <div class="col-md-10">
                          <strong>{{ $section_translation }}</strong>
                          @if (\Lang::has('permissions.'.str_slug($this_permission['permission']).'.note'))
                            <p>{{ trans('permissions.'.str_slug($this_permission['permission']).'.note') }}</p>
                          @endif
                        </div>

                        <div class="form-group col-md-2 text-right">
                          <div class="radio-toggle-wrapper">

                                <div class="radio-slider-inputs" data-tooltip="true" title="{{ trans('permissions.grant', ['area' => $section_translation]) }}">
                                  <input
                                          class="form-control allow radiochecker-{{ str_slug($main_section) }}"
                                          aria-label="permission[{{ $this_permission['permission'] }}]"
                                          @checked(array_key_exists($this_permission['permission'], $groupPermissions) && $groupPermissions[$this_permission['permission']] == '1')
                                          name="permission[{{ $this_permission['permission'] }}]"
                                          type="radio"
                                          id="{{ str_slug($this_permission['permission']) }}_allow"
                                          value="1"
                                  >
                                  <label for="{{ str_slug($this_permission['permission']) }}_allow" class="allow">
                                    <i class="fa-solid fa-square-check"></i>
                                  </label>
                              </div>

                            @if ($use_inherit)
                                <div class="radio-slider-inputs" data-tooltip="true" title="{{ trans('permissions.inherit', ['area' => $section_translation]) }}">
                                  <input
                                          class="form-control inherit radiochecker-{{ str_slug($main_section) }}"
                                          aria-label="permission[{{ $this_permission['permission'] }}]"
                                          @checked(array_key_exists($this_permission['permission'], $groupPermissions) && $groupPermissions[$this_permission['permission']] == '0')
                                          name="permission[{{ $this_permission['permission'] }}]"
                                          type="radio"
                                          id="{{ str_slug($this_permission['permission']) }}_inherit"
                                          value="0"
                                  >
                                  <label for="{{ str_slug($this_permission['permission']) }}_inherit" class="inherit">
                                    <i class="fa-solid fa-layer-group"></i>
                                  </label>
                                </div>
                            @endif

                            <div class="radio-slider-inputs" data-tooltip="true" title="{{ trans('permissions.deny', ['area' => $section_translation]) }}">
                                <input
                                        class="form-control deny radiochecker-{{ str_slug($main_section) }}"
                                        aria-label="permission[{{ $this_permission['permission'] }}]"
                                        @checked(array_key_exists($this_permission['permission'], $groupPermissions) && $groupPermissions[$this_permission['permission']] == '-1')
                                        name="permission[{{ $this_permission['permission'] }}]"
                                        type="radio"
                                        value="-1"
                                        id="{{ str_slug($this_permission['permission']) }}_deny"
                                >
                                <label for="{{ str_slug($this_permission['permission']) }}_deny">
                                  <i class="fa-solid fa-square-xmark"></i>
                                </label>
                            </div>
                          </div>



                      </div>
                    </div>
                  @endif
                @endforeach
      </div>
  @endif

@endforeach


