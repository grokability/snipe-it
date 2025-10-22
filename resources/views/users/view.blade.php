@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/users/general.view_user', ['name' => $user->display_name]) }}
@parent
@stop

{{-- Page content --}}
@section('content')



<div class="row">

    @if ($user->deleted_at!='')
        <div class="col-md-12">
            <div class="callout callout-warning">
                <x-icon type="warning" />
                {{ trans('admin/users/message.user_deleted_warning') }}
            </div>
        </div>
    @endif

  <div class="col-md-12">




    <div class="nav-tabs-custom">
      <ul class="nav nav-tabs hidden-print">

        <li class="active">
          <a href="#details" data-toggle="tab">
            <span class="hidden-lg hidden-md">
                <x-icon type="info-circle" class="fa-2x" />
            </span>
            <span class="hidden-xs hidden-sm">{{ trans('admin/users/general.info') }}</span>
          </a>
        </li>

        <li>
          <a href="#asset" data-toggle="tab">
            <span class="hidden-lg hidden-md">
            <x-icon type="assets" class="fa-2x" />
            </span>
            <span class="hidden-xs hidden-sm">{{ trans('general.assets') }}
              {!! ($user->assets()->AssetsForShow()->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($user->assets()->AssetsForShow()->withoutTrashed()->count()).'</span>' : '' !!}
            </span>
          </a>
        </li>

        <li>
          <a href="#licenses" data-toggle="tab">
            <span class="hidden-lg hidden-md">
            <x-icon type="licenses" class="fa-2x" />
            </span>
            <span class="hidden-xs hidden-sm">{{ trans('general.licenses') }}
              {!! ($user->licenses->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($user->licenses->count()).'</span>' : '' !!}
            </span>
          </a>
        </li>

        <li>
          <a href="#accessories" data-toggle="tab">
            <span class="hidden-lg hidden-md">
            <x-icon type="accessories" class="fa-2x" />
            </span> 
            <span class="hidden-xs hidden-sm">{{ trans('general.accessories') }}
              {!! ($user->accessories->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($user->accessories->count()).'</span>' : '' !!}
            </span>
          </a>
        </li>

        <li>
          <a href="#consumables" data-toggle="tab">
            <span class="hidden-lg hidden-md">
                <x-icon type="consumables" class="fa-2x" />
            </span>
            <span class="hidden-xs hidden-sm">{{ trans('general.consumables') }}
              {!! ($user->consumables->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($user->consumables->count()).'</span>' : '' !!}
            </span>
          </a>
        </li>

        <li>
          <a href="#files" data-toggle="tab">
            <span class="hidden-lg hidden-md">
                <x-icon type="files" class="fa-2x" />
            </span>
            <span class="hidden-xs hidden-sm">{{ trans('general.file_uploads') }}
              {!! ($user->uploads->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($user->uploads->count()).'</span>' : '' !!}
            </span>
          </a>
        </li>

          <li>
              <a href="#eulas" data-toggle="tab">
            <span class="hidden-lg hidden-md" aria-hidden="true">
                <x-icon type="files" class="fa-2x" />
              </span>
                  <span class="hidden-xs hidden-sm">{{ trans('general.eula') }}
                      {!! ($user->eulas->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($user->eulas->count()).'</span>' : '' !!}
            </span>
              </a>
          </li>

        <li>
          <a href="#history" data-toggle="tab">
            <span class="hidden-lg hidden-md">
                <x-icon type="history" class="fa-2x" />
            </span>
            <span class="hidden-xs hidden-sm">{{ trans('general.history') }}</span>
          </a>
        </li>

        @if ($user->managedLocations->count() >= 0 )
        <li>
          <a href="#managed-locations" data-toggle="tab">
            <span class="hidden-lg hidden-md">
                <x-icon type="locations" class="fa-2x" />
            </span>
            <span class="hidden-xs hidden-sm">{{ trans('admin/users/table.managed_locations') }}
              {!! ($user->managedLocations->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($user->managedLocations->count()).'</span>' : '' !!}
          </a>
        </li>
        @endif

          @if ($user->managesUsers->count() >= 0 )
              <li>
                  <a href="#managed-users" data-toggle="tab">
                    <span class="hidden-lg hidden-md">
                      <x-icon type="users" class="fa-2x" />
                    </span>
                      <span class="hidden-xs hidden-sm">{{ trans('admin/users/table.managed_users') }}
                      {!! ($user->managesUsers->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($user->managesUsers->count()).'</span>' : '' !!}
                  </a>
              </li>
          @endif


      @can('update', $user)
          <li class="dropdown pull-right">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
              <span class="hidden-xs"><i class="fas fa-cog" aria-hidden="true"></i></span>
              <span class="hidden-lg hidden-md hidden-xl"><i class="fas fa-cog fa-2x" aria-hidden="true"></i></span>

              <span class="hidden-xs hidden-sm">
                {{ trans('button.actions') }}
              </span>
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <li><a href="{{ route('users.edit', $user->id) }}">{{ trans('admin/users/general.edit') }}</a></li>
              <li><a href="{{ route('users.clone.show', $user->id) }}">{{ trans('admin/users/general.clone') }}</a></li>
              @if ((Auth::user()->id !== $user->id) && (!config('app.lock_passwords')) && ($user->deleted_at=='') && ($user->isDeletable()))
                <li><a href="{{ route('users.destroy', $user->id) }}">{{ trans('button.delete') }}</a></li>
              @endif
            </ul>
          </li>
        @endcan

        @can('update', \App\Models\User::class)
          <li class="pull-right">
              <a href="#" data-toggle="modal" data-target="#uploadFileModal">
              <span class="hidden-xs"><x-icon type="paperclip" /></span>
              <span class="hidden-lg hidden-md hidden-xl"><x-icon type="paperclip" class="fa-2x" /></span>
              <span class="hidden-xs hidden-sm">{{ trans('button.upload') }}</span>
              </a>
          </li>
        @endcan
      </ul>

      <div class="tab-content">
        <div class="tab-pane active" id="details">
          <div class="row">

        <div class="info-stack-container">
            <!-- Start button column -->
            <div class="col-md-3 col-xs-12 col-sm-push-9 info-stack">


              <div class="col-md-12 text-center">

                 @if (($user->isSuperUser()) || ($user->hasAccess('admin')))
                      <x-icon type="superadmin" class="fa-2x {{  ($user->isSuperUser()) ? 'text-danger' : 'text-orange'}}" />
                        <div class="{{  ($user->isSuperUser()) ? 'text-danger' : ' text-orange'}}" style="font-weight: bold">{{  ($user->isSuperUser()) ? strtolower(trans('general.superuser')) : strtolower(trans('general.admin_user')) }}</div>
                  @endif


              </div>
              <div class="col-md-12 text-center">
                <img src="{{ $user->present()->gravatar() }}"  class=" img-thumbnail hidden-print" style="margin-bottom: 20px;" alt="{{ $user->display_name }}">
               </div>

              @can('update', $user)
                <div class="col-md-12">
                  <a href="{{ ($user->deleted_at=='') ? route('users.edit', $user->id) : '#' }}" style="width: 100%;" class="btn btn-sm btn-warning btn-social hidden-print{{ ($user->deleted_at!='') ? ' disabled' : '' }}">
                      <x-icon type="edit" />
                      {{ trans('admin/users/general.edit') }}
                  </a>
                </div>
              @endcan

                @can('view', $user)
                <div class="col-md-12" style="padding-top: 5px;">

                @if($user->allAssignedCount() != '0') 
                  <a href="{{ route('users.print', $user->id) }}" style="width: 100%;" class="btn btn-sm btn-primary btn-social hidden-print" target="_blank" rel="noopener">
                      <x-icon type="print" />
                      {{ trans('admin/users/general.print_assigned') }}
                  </a>
                  @else
                  <button style="width: 100%;" class="btn btn-sm btn-primary btn-social hidden-print" rel="noopener" disabled title="{{ trans('admin/users/message.user_has_no_assets_assigned') }}">
                      <x-icon type="print" />
                      {{ trans('admin/users/general.print_assigned') }}</button>
                @endif
                </div>
                @endcan

                @can('view', $user)
                  <div class="col-md-12" style="padding-top: 5px;">
                  @if(!empty($user->email) && ($user->allAssignedCount() != '0'))
                    <form action="{{ route('users.email',['userId'=> $user->id]) }}" method="POST">
                      {{ csrf_field() }}
                      <button class="btn-block btn btn-sm btn-primary btn-social hidden-print" rel="noopener">
                          <x-icon type="email" />
                          {{ trans('admin/users/general.email_assigned') }}
                      </button>
                    </form>
                  @elseif(!empty($user->email) && ($user->allAssignedCount() == '0'))
                      <button class="btn btn-block btn-sm btn-primary btn-social hidden-print" rel="noopener" disabled title="{{ trans('admin/users/message.user_has_no_assets_assigned') }}">
                          <x-icon type="email" />
                          {{ trans('admin/users/general.email_assigned') }}
                      </button>
                  @else
                      <button class="btn btn-block btn-sm btn-primary btn-social hidden-print" rel="noopener" disabled title="{{ trans('admin/users/message.user_has_no_email') }}">
                          <x-icon type="email" />
                          {{ trans('admin/users/general.email_assigned') }}
                      </button>
                  @endif
                  </div>
                @endcan

                @can('update', $user)
                  @if ((($user->deleted_at=='')) && ($user->activated == '1') && ($user->ldap_import == '0'))
                  <div class="col-md-12" style="padding-top: 5px;">
                    @if (($user->email != '') && ($user->activated=='1'))
                      <form action="{{ route('users.password',['userId'=> $user->id]) }}" method="POST">
                          {{ csrf_field() }}
                      <button class="btn btn-block btn-sm btn-primary btn-social hidden-print">
                          <x-icon type="password" />
                          {{ trans('button.send_password_link') }}
                      </button>
                      </form>
                    @else
                      <button class="btn btn-block btn-sm btn-primary btn-social hidden-print" rel="noopener" disabled title="{{ trans('admin/users/message.user_has_no_email') }}">
                          <x-icon type="email" />
                          {{ trans('button.send_password_link') }}
                      </button>
                    @endif
                  </div>
                  @endif
                @endcan

                @can('create', $user)
                    <div class="col-md-12" style="padding-top: 5px;">
                        <a href="{{ route('users.clone.show', $user->id) }}" class="btn btn-block btn-sm btn-info btn-social hidden-print">
                            <x-icon type="clone" />
                            {{ trans('admin/users/general.clone') }}
                        </a>
                    </div>
                @endcan


            @can('update', $user)
                  @if ($user->deleted_at=='')
                    <div class="col-md-12" style="padding-top: 30px;">
                        @if ($user->isDeletable())
                            <a href="" class="delete-asset btn-block btn btn-sm btn-danger btn-social hidden-print" data-toggle="modal" data-title="{{ trans('general.delete') }}" data-content="{{ trans('general.sure_to_delete_var', ['item' => $user->display_name]) }}" data-icon="fa-trash" data-target="#dataConfirmModal" onClick="return false;" >
                                <x-icon type="delete" />
                                {{ trans('button.delete')}}
                            </a>
                            @else
                            <button class="btn-block btn btn-sm btn-danger btn-social hidden-print disabled">
                                <x-icon type="delete" />
                                {{ trans('button.delete')}}
                            </button>
                        @endif
                    </div>
                    <div class="col-md-12" style="padding-top: 5px;">
                      <form action="{{ route('users/bulkedit') }}" method="POST">
                        <!-- CSRF Token -->
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                        <input type="hidden" name="bulk_actions" value="delete" />

                        <input type="hidden" name="ids[{{ $user->id }}]" value="{{ $user->id }}" />
                        <button class="btn btn-block btn-sm btn-danger btn-social hidden-print">
                            <x-icon type="checkin-and-delete" />
                            {{ trans('button.checkin_and_delete') }}
                        </button>
                      </form>
                    </div>
                  @else
                    <div class="col-md-12" style="padding-top: 5px;">
                        <form method="POST" action="{{ route('users.restore.store', $user->id) }}">
                            @csrf
                            <button class="btn btn-block btn-sm btn-warning btn-social hidden-print">
                                <x-icon type="restore" />
                                {{ trans('button.restore') }}
                            </button>
                        </form>
                    </div>
                  @endif
                @endcan
                <br><br>
            </div>

            <!-- End button column -->
          
            <div class="col-md-9 col-xs-12 col-sm-pull-3 info-stack">

               <div class="row-new-striped">

                  <div class="row">
                    <!-- name -->

    
                      <div class="col-md-3">
                        {{ trans('admin/users/table.name') }}
                      </div>
                      <div class="col-md-9">
                        {{ $user->first_name }} {{ $user->last_name }}
                      </div>

                  </div>



                    <!-- username -->
                    <div class="row">

                      <div class="col-md-3">
                        {{ trans('admin/users/table.username') }}
                      </div>
                      <div class="col-md-9">

                        @if ($user->isSuperUser())
                          <span class="label label-danger" data-tooltip="true" title="{{ trans('general.superuser_tooltip') }}"><x-icon type="superadmin" title="{{ trans('general.superuser') }}" /></span>&nbsp;
                        @elseif ($user->hasAccess('admin'))
                          <span class="label label-warning" data-tooltip="true" title="{{ trans('general.admin_tooltip') }}"><x-icon type="superadmin" title="{{ trans('general.admin_user') }}" /></span>&nbsp;
                        @endif
                         {{ $user->username }}

                      </div>

                    </div>

                   <!-- display name -->
                   @if ($user->display_name)
                   <div class="row">

                       <div class="col-md-3">
                           {{ trans('admin/users/table.display_name') }}
                       </div>
                       <div class="col-md-9">
                           {{ $user->getRawOriginal('display_name') }}
                       </div>
                   </div>
                   @endif

                    <!-- address -->
                    @if (($user->address) || ($user->city) || ($user->state) || ($user->country))
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.address') }}
                      </div>
                      <div class="col-md-9">

                          @if ($user->address)
                          {{ $user->address }} <br>
                          @endif
                          @if ($user->city)
                            {{ $user->city }}
                          @endif
                          @if ($user->state)
                            {{ $user->state }}
                          @endif
                          @if ($user->country)
                            {{ $user->country }}
                          @endif
                          @if ($user->zip)
                              {{ $user->zip }}
                          @endif

                      </div>
                    </div>
                    @endif

                   <!-- company -->
                   @if (!is_null($user->company))
                       <div class="row">

                           <div class="col-md-3">
                               {{ trans('general.company') }}
                           </div>
                           <div class="col-md-9">
                               @can('view', 'App\Models\Company')
                                   <a href="{{ route('companies.show', $user->company->id) }}">
                                       {{ $user->company->name }}
                                   </a>
                               @else
                                   {{ $user->company->name }}
                               @endcan
                           </div>

                       </div>

                   @endif

                     <!-- groups -->
                     <div class="row">
                        <div class="col-md-3">
                          {{ trans('general.groups') }}
                        </div>
                        <div class="col-md-9">
                          @if ($user->groups->count() > 0)
                            @foreach ($user->groups as $group)

                              @can('superadmin')
                                  <a href="{{ route('groups.show', $group->id) }}" class="label label-default">{{ $group->name }}</a>
                              @else
                              {{ $group->name }}
                              @endcan

                            @endforeach
                          @else
                              --
                          @endif
                        </div>
                      </div>

                   <!-- start date -->
                   @if ($user->start_date)
                       <div class="row">
                           <div class="col-md-3">
                               {{ trans('general.start_date') }}
                           </div>
                           <div class="col-md-9">
                               {{ \App\Helpers\Helper::getFormattedDateObject($user->start_date, 'date', false) }}
                           </div>
                       </div>
                   @endif

                   <!-- end date -->
                   @if ($user->end_date)
                       <div class="row">
                           <div class="col-md-3">
                               {{ trans('general.end_date') }}
                           </div>
                           <div class="col-md-9">
                               {{ \App\Helpers\Helper::getFormattedDateObject($user->end_date, 'date', false) }}
                           </div>
                       </div>
                   @endif

                    @if ($user->jobtitle)
                     <!-- jobtitle -->
                     <div class="row">

                        <div class="col-md-3">
                          {{ trans('admin/users/table.job') }}
                        </div>
                        <div class="col-md-9">
                          {{ $user->jobtitle }}
                        </div>

                      </div>
                    @endif

                    @if ($user->employee_num)
                      <!-- employee_num -->
                      <div class="row">

                        <div class="col-md-3">
                          {{ trans('admin/users/table.employee_num') }}
                        </div>
                        <div class="col-md-9">
                          {{ $user->employee_num }}
                        </div>

                      </div>
                    @endif

                    @if ($user->manager)
                      <!-- manager -->
                      <div class="row">

                        <div class="col-md-3">
                          {{ trans('admin/users/table.manager') }}
                        </div>
                        <div class="col-md-9">
                          <a href="{{ route('users.show', $user->manager->id) }}">
                            {{ $user->manager->display_name }}
                          </a>
                        </div>

                      </div>

                    @endif


                    @if ($user->email)
                    <!-- email -->
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('admin/users/table.email') }}
                      </div>
                      <div class="col-md-9">
                        <a href="mailto:{{ $user->email }}" data-tooltip="true" title="{{ trans('general.send_email') }}">
                            <x-icon type="email" />
                            {{ $user->email }}</a>
                      </div>
                    </div>
                    @endif

                    @if ($user->website)
                     <!-- website -->
                     <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.website') }}
                      </div>
                      <div class="col-md-9">
                          <a href="{{ $user->website }}" target="_blank"><x-icon type="external-link" /> {{ $user->website }}</a>
                      </div>
                    </div>
                    @endif

                    @if ($user->phone)
                      <!-- phone -->
                      <div class="row">
                        <div class="col-md-3">
                          {{ trans('admin/users/table.phone') }}
                        </div>
                        <div class="col-md-9">
                          <a href="tel:{{ $user->phone }}" data-tooltip="true" title="{{ trans('general.call') }}">
                              <x-icon type="phone" />
                              {{ $user->phone }}</a>
                        </div>
                      </div>
                    @endif

                   @if ($user->mobile)
                       <!-- phone -->
                       <div class="row">
                           <div class="col-md-3">
                               {{ trans('admin/users/table.mobile') }}
                           </div>
                           <div class="col-md-9">
                               <a href="tel:{{ $user->mobile }}" data-tooltip="true" title="{{ trans('general.call') }}">
                                   <x-icon type="mobile" />
                                   {{ $user->mobile }}</a>
                           </div>
                       </div>
                   @endif
                    @if ($user->userloc)
                     <!-- location -->
                     <div class="row">
                      <div class="col-md-3">
                        {{ trans('admin/users/table.location') }}
                      </div>
                      <div class="col-md-9">
                        {{ link_to_route('locations.show', $user->userloc->name, [$user->userloc->id]) }}
                      </div>
                    </div>
                    @endif

                    <!-- last login -->
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.last_login') }}
                      </div>
                      <div class="col-md-9">
                        {{ \App\Helpers\Helper::getFormattedDateObject($user->last_login, 'datetime', false) }}
                      </div>
                    </div>


                    @if ($user->department)
                      <!-- empty -->
                      <div class="row">
                        <div class="col-md-3">
                          {{ trans('general.department') }}
                        </div>
                        <div class="col-md-9">
                          <a href="{{ route('departments.show', $user->department) }}">
                            {{ $user->department->name }}
                          </a>
                        </div>
                      </div>
                      @if($user->department->manager)
                        <div class="row">
                          <div class="col-md-3">
                            {{ trans('admin/users/general.department_manager') }}
                          </div>
                          <div class="col-md-9">
                            <a href="{{ route('users.show', $user->department->manager) }}">
                              {{ $user->department->manager->full_name }}
                            </a>
                          </div>
                        </div>
                      @endif
                    @endif

                    @if ($user->created_at)
                    <!-- created at -->
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.created_at') }}
                      </div>
                      <div class="col-md-9">
                        {{ \App\Helpers\Helper::getFormattedDateObject($user->created_at, 'datetime')['formatted']}}

                          @if ($user->createdBy)
                              -
                              @if ($user->createdBy->deleted_at=='')
                                  <a href="{{ route('users.show', ['user' => $user->created_by]) }}">{{ $user->createdBy->display_name }}</a>
                              @else
                                  <del>{{ $user->createdBy->present()->fullName }}</del>
                              @endif


                          @endif
                      </div>
                    </div>
                    @endif

                    <!-- vip -->
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('admin/users/general.vip_label') }}
                      </div>
                      <div class="col-md-9">
                          @if ($user->vip=='1')
                              <x-icon type="checkmark" class="fa-fw text-success" />
                              {{ trans('general.yes') }}
                          @else
                              <x-icon type="x" class="fa-fw text-danger" />
                              {{ trans('general.no') }}
                          @endif
                      </div>
                    </div>

                    <!-- remote -->
                     <div class="row">
                      <div class="col-md-3">
                        {{ trans('admin/users/general.remote') }}
                      </div>
                      <div class="col-md-9">
                          @if ($user->remote == '1')
                              <x-icon type="checkmark" class="fa-fw text-success" />
                              {{ trans('general.yes') }}
                          @else
                              <x-icon type="x" class="fa-fw text-danger" />
                              {{ trans('general.no') }}
                          @endif
                      </div>
                    </div>

                    <!-- login enabled -->
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.login_enabled') }}
                      </div>
                      <div class="col-md-9">
                          @if ($user->activated == '1')
                              <x-icon type="checkmark" class="fa-fw text-success" />
                              {{ trans('general.yes') }}
                          @else
                              <x-icon type="x" class="fa-fw text-danger" />
                              {{ trans('general.no') }}
                          @endif
                      </div>
                    </div>

                   <!-- auto assign license -->
                   <div class="row">
                       <div class="col-md-3">
                           {{ trans('general.autoassign_licenses') }}
                       </div>
                       <div class="col-md-9">
                           @if ($user->autoassign_licenses == '1')
                               <x-icon type="checkmark" class="fa-fw text-success" />
                               {{ trans('general.yes') }}
                           @else
                               <x-icon type="x" class="fa-fw text-danger" />
                               {{ trans('general.no') }}
                           @endif
                       </div>
                   </div>


                   <!-- LDAP -->
                    <div class="row">
                      <div class="col-md-3">
                          LDAP
                      </div>
                      <div class="col-md-9">
                          @if ($user->ldap_import == '1')
                              <x-icon type="checkmark" class="fa-fw text-success" />
                              {{ trans('general.yes') }}
                          @else
                              <x-icon type="x" class="fa-fw text-danger" />
                              {{ trans('general.no') }}
                          @endif

                      </div>
                    </div>

                    @if ($user->activated == '1')

                          <!-- 2FA active -->
                          <div class="row">
                            <div class="col-md-3">
                              {{ trans('admin/users/general.two_factor_active') }}
                            </div>
                            <div class="col-md-9">

                                @if ($user->two_factor_active())
                                    <x-icon type="checkmark" class="fa-fw text-success" />
                                    {{ trans('general.yes') }}
                                @else
                                    <x-icon type="x" class="fa-fw text-danger" />
                                    {{ trans('general.no') }}
                                @endif
                                
                            </div>
                          </div>

                          <!-- 2FA enrolled -->
                          <div class="row two_factor_resetrow">
                            <div class="col-md-3">
                              {{ trans('admin/users/general.two_factor_enrolled') }}
                            </div>
                            <div class="col-md-9" id="two_factor_reset_toggle">
                                @if ($user->two_factor_active_and_enrolled())
                                <x-icon type="checkmark" class="fa-fw text-success" />
                                {{ trans('general.yes') }}
                                @else
                                    <x-icon type="x" class="fa-fw text-danger" />
                                    {{ trans('general.no') }}
                                @endif

                            </div>
                          </div>

                          @if ((Auth::user()->isSuperUser()) && ($user->two_factor_active_and_enrolled()) && ($snipeSettings->two_factor_enabled!='0') && ($snipeSettings->two_factor_enabled!=''))

                            <!-- 2FA reset -->
                            <div class="row">
                              <div class="col-md-3">

                              </div>
                              <div class="col-md-9">

                                <a class="btn btn-default btn-sm" id="two_factor_reset" style="margin-right: 10px; margin-top: 10px;">
                                  {{ trans('admin/settings/general.two_factor_reset') }}
                                </a>
                                <span id="two_factor_reseticon">
                                </span>
                                <span id="two_factor_resetresult">
                                </span>
                                <span id="two_factor_resetstatus">
                                </span>
                                <br>

                                <p class="help-block" style="line-height: 1.6;">
                                    {{ trans('admin/settings/general.two_factor_reset_help') }}
                                </p>
                          
                                
                              </div>
                            </div>
                            @endif
                  @endif


                    @if ($user->notes)
                     <!-- empty -->
                     <div class="row">

                      <div class="col-md-3">
                        {{ trans('admin/users/table.notes') }}
                      </div>
                      <div class="col-md-9">
                          {!! nl2br(Helper::parseEscapedMarkedownInline($user->notes)) !!}
                      </div>

                    </div>
                    @endif
                   @if($user->getUserTotalCost()->total_user_cost > 0)
                   <div class="row">
                       <div class="col-md-3">
                           {{ trans('admin/users/table.total_assets_cost') }}
                       </div>
                       <div class="col-md-9">
                           {{Helper::formatCurrencyOutput($user->getUserTotalCost()->total_user_cost)}}

                           <a id="optional_info" class="text-primary">
                               <x-icon type="caret-right" id="optional_info_icon" />
                               <strong>{{ trans('admin/hardware/form.optional_infos') }}</strong>
                           </a>
                       </div>
                           <div id="optional_details" class="col-md-12" style="display:none">
                               <div class="col-md-3" style="border-top:none;"></div>
                               <div class="col-md-9" style="border-top:none;">
                               {{trans('general.assets').': '. Helper::formatCurrencyOutput($user->getUserTotalCost()->asset_cost)}}<br>
                               {{trans('general.licenses').': '. Helper::formatCurrencyOutput($user->getUserTotalCost()->license_cost)}}<br>
                               {{trans('general.accessories').': '.Helper::formatCurrencyOutput($user->getUserTotalCost()->accessory_cost)}}<br>
                               </div>
                           </div>
                   </div><!--/.row-->
                   @endif
                  </div> <!--/end striped container-->
                </div> <!-- end col-md-9 -->
             </div><!-- end info-stack-container-->
          </div> <!--/.row-->
        </div><!-- /.tab-pane -->

        <div class="tab-pane" id="asset">
          <!-- checked out assets table -->

            @include('partials.asset-bulk-actions')

            <div class="table table-responsive">

            <table
                    data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                    data-show-columns-search="true"
                    data-cookie-id-table="userAssetsListingTable"
                    data-id-table="userAssetsListingTable"
                    data-side-pagination="server"
                    data-show-footer="true"
                    data-sort-name="name"
                    data-toolbar="#assetsBulkEditToolbar"
                    data-bulk-button-id="#bulkAssetEditButton"
                    data-bulk-form-id="#assetsBulkForm"
                    id="userAssetsListingTable"
                    data-buttons="assetButtons"
                    class="table table-striped snipe-table"
                    data-url="{{ route('api.assets.index',['assigned_to' => e($user->id), 'assigned_type' => 'App\Models\User']) }}"
                    data-export-options='{
                "fileName": "export-{{ str_slug($user->username) }}-assets-{{ date('Y-m-d') }}",
                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
            </table>
          </div>
        </div><!-- /asset -->

        <div class="tab-pane" id="licenses">


          <div class="table-responsive">
            <table
                    data-cookie-id-table="userLicenseTable"
                    data-id-table="userLicenseTable"
                    id="userLicenseTable"
                    data-buttons="licenseButtons"
                    data-side-pagination="client"
                    data-show-footer="true"
                    data-sort-name="name"
                    class="table table-striped snipe-table table-hover"
                    data-export-options='{
                    "fileName": "export-license-{{ str_slug($user->username) }}-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","delete","download","icon"]
                    }'>

              <thead>
                <tr>
                  <th>{{ trans('general.name') }}</th>
                  <th>{{ trans('admin/licenses/form.license_key') }}</th>
                  <th data-footer-formatter="sumFormatter" data-fieldname="purchase_cost">{{ trans('general.purchase_cost') }}</th>
                  <th>{{ trans('admin/licenses/form.purchase_order') }}</th>
                  <th>{{ trans('general.order_number') }}</th>
                  <th class="col-md-1 hidden-print">{{ trans('general.action') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($user->licenses as $license)
                <tr>
                  <td class="col-md-4">
                    {!! $license->present()->nameUrl() !!}
                  </td>
                  <td class="col-md-4">
                    @can('viewKeys', $license)
                          <code class="single-line"><span class="js-copy-link" data-clipboard-target=".js-copy-key-{{ $license->id }}" aria-hidden="true" data-tooltip="true" data-placement="top" title="{{ trans('general.copy_to_clipboard') }}"><span class="js-copy-key-{{ $license->id }}">{{ $license->serial }}</span></span></code>
                    @else
                      ------------
                    @endcan
                  </td>
                  <td class="col-md-2">
                    {{ Helper::formatCurrencyOutput($license->purchase_cost) }}
                  </td>
                  <td>
                    {{ $license->purchase_order }}
                  </td>
                  <td>
                    {{ $license->order_number }}
                  </td>
                  <td class="hidden-print col-md-2">
                    @can('update', $license)
                      <a href="{{ route('licenses.checkin', $license->pivot->id, ['backto'=>'user']) }}" class="btn bg-purple btn-sm hidden-print">{{ trans('general.checkin') }}</a>
                     @endcan
                  </td>
                </tr>
                @endforeach
              </tbody>
          </table>
          </div>
        </div><!-- /licenses-tab -->

        <div class="tab-pane" id="accessories">
          <div class="table-responsive">
            <table
                    data-cookie-id-table="userAccessoryTable"
                    data-id-table="userAccessoryTable"
                    id="userAccessoryTable"
                    data-buttons="accessoryButtons"
                    data-side-pagination="client"
                    data-sort-name="name"
                    class="table table-striped snipe-table table-hover"
                    data-export-options='{
                    "fileName": "export-accessory-{{ str_slug($user->username) }}-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","delete","download","icon"]
                    }'>
              <thead>
                <tr>
                    <th>{{ trans('general.id') }}</th>
                    <th>{{ trans('general.name') }}</th>
                    <th>{{ trans('general.date') }}</th>
                    <th data-fieldname="note">{{ trans('general.notes') }}</th>
                    <th data-footer-formatter="sumFormatter" data-fieldname="purchase_cost">{{ trans('general.unit_cost') }}</th>
                    <th class="hidden-print">{{ trans('general.action') }}</th>
                </tr>
              </thead>
              <tbody>
                  @foreach ($user->accessories as $accessory)
                  <tr>
                      <td>{{ $accessory->pivot->id }}</td>
                      <td>{!! $accessory->present()->nameUrl() !!}</td>
                      <td>{{ Helper::getFormattedDateObject($accessory->pivot->created_at, 'datetime',  false) }}</td>
                      <td>{{ $accessory->pivot->note }}</td>
                      <td>
                      {!! Helper::formatCurrencyOutput($accessory->purchase_cost) !!}
                      </td>
                    <td class="hidden-print">
                      @can('checkin', $accessory)
                        <a href="{{ route('accessories.checkin.show', array('accessoryID'=> $accessory->pivot->id, 'backto'=>'user')) }}" class="btn btn-primary btn-sm hidden-print">{{ trans('general.checkin') }}</a>
                      @endcan
                    </td>
                  </tr>
                  @endforeach
              </tbody>
            </table>
          </div>
        </div><!-- /accessories-tab -->

        <div class="tab-pane" id="consumables">
          <div class="table-responsive">
            <table
                    data-cookie-id-table="userConsumableTable"
                    data-id-table="userConsumableTable"
                    id="userConsumableTable"
                    data-buttons="consumableButtons"
                    data-side-pagination="client"
                    data-show-footer="true"
                    data-sort-name="name"
                    class="table table-striped snipe-table table-hover"
                    data-export-options='{
                    "fileName": "export-consumable-{{ str_slug($user->username) }}-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","delete","download","icon"]
                    }'>
              <thead>
                <tr>
                  <th class="col-md-3">{{ trans('general.name') }}</th>
                  <th class="col-md-2" data-footer-formatter="sumFormatter" data-fieldname="purchase_cost">{{ trans('general.unit_cost') }}</th>
                  <th class="col-md-2">{{ trans('general.date') }}</th>
                    <th class="col-md-5">{{ trans('general.notes') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($user->consumables as $consumable)
                <tr>
                  <td>{!! $consumable->present()->nameUrl() !!}</td>
                  <td>
                    {!! Helper::formatCurrencyOutput($consumable->purchase_cost) !!}
                  </td>
                  <td>{{ Helper::getFormattedDateObject($consumable->pivot->created_at, 'datetime',  false) }}</td>
                  <td>{{ $consumable->pivot->note }}</td>
                </tr>
                @endforeach
              </tbody>
          </table>
          </div>
        </div><!-- /consumables-tab -->

        <div class="tab-pane" id="files">
          <div class="row">

            <div class="col-md-12 col-sm-12">
                <x-filestable object_type="users" :object="$user" />
            </div>
          </div> <!--/ROW-->
        </div><!--/FILES-->

          <div class="tab-pane" id="eulas">
              <table
                      data-toolbar="#userEULAToolbar"
                      data-cookie-id-table="userEULATable"
                      data-id-table="userEULATable"
                      id="userEULATable"
                      data-side-pagination="client"
                      data-show-footer="true"
                      data-show-refresh="false"
                      data-sort-order="asc"
                      data-sort-name="name"
                      class="table table-striped snipe-table table-hover"
                      data-url="{{ route('api.user.eulas', $user) }}"
                      data-export-options='{
                    "fileName": "export-eula-{{ str_slug($user->username) }}-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","delete","purchasecost", "icon"]
                    }'>
                  <thead>
                  <tr>
                      <th data-visible="true" data-field="icon" style="width: 40px;" class="hidden-xs" data-formatter="iconFormatter">{{ trans('admin/hardware/table.icon') }}</th>
                      <th data-visible="true" data-field="item.name">{{ trans('general.item') }}</th>
                      <th data-visible="true" data-field="created_at" data-sortable="true" data-formatter="dateDisplayFormatter">{{ trans('general.accepted_date') }}</th>
                      <th data-field="note">{{ trans('general.notes') }}</th>
                      <th data-field="url" data-formatter="fileDownloadButtonsFormatter">{{ trans('general.download') }}</th>
                  </tr>
                  </thead>
              </table>
          </div><!-- /eulas-tab -->

        <div class="tab-pane" id="history">
          <div class="table-responsive">


              <table
                      data-columns="{{ \App\Presenters\HistoryPresenter::dataTableLayout() }}"
                      class="table table-striped snipe-table"
                      data-cookie-id-table="UserHistoryTable"
                      data-id-table="UserHistoryTable"
                      id="UserHistoryTable"
                      data-side-pagination="server"
                      data-sort-order="desc"
                      data-export-options='{
                       "fileName": "export-{{ str_slug($user->name) }}-history-{{ date('Y-m-d') }}",
                       "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                     }'
                      data-url="{{ route('api.activity.index', ['item_id' => $user->id, 'item_type' => User::class]) }}">
              </table>

          </div>
        </div><!-- /.tab-pane -->

        <div class="tab-pane" id="managed-locations">

            @include('partials.locations-bulk-actions')


            <table
                    data-columns="{{ \App\Presenters\LocationPresenter::dataTableLayout() }}"
                    data-cookie-id-table="locationTable"
                    data-id-table="locationTable"
                    data-toolbar="#locationsBulkEditToolbar"
                    data-bulk-button-id="#bulkLocationsEditButton"
                    data-bulk-form-id="#locationsBulkForm"
                    data-side-pagination="server"
                    id="locationTable"
                    data-buttons="locationButtons"
                    class="table table-striped snipe-table"
                    data-url="{{ route('api.locations.index', ['manager_id' => $user->id]) }}"
                    data-export-options='{
              "fileName": "export-locations-{{ date('Y-m-d') }}",
              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
              }'>
            </table>

          </div>

          <div class="tab-pane" id="managed-users">

              @include('partials.users-bulk-actions')


              <table
                      data-columns="{{ \App\Presenters\UserPresenter::dataTableLayout() }}"
                      data-cookie-id-table="managedUsersTable"
                      data-id-table="managedUsersTable"
                      data-toolbar="#usersBulkEditToolbar"
                      data-bulk-button-id="#bulkUserEditButton"
                      data-bulk-form-id="#usersBulkForm"
                      data-side-pagination="server"
                      id="managedUsersTable"
                      data-buttons="userButtons"
                      class="table table-striped snipe-table"
                      data-url="{{ route('api.users.index', ['manager_id' => $user->id]) }}"
                      data-export-options='{
              "fileName": "export-users-{{ date('Y-m-d') }}",
              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
              }'>
              </table>

          </div>
        </div><!-- /consumables-tab -->
      </div><!-- /.tab-content -->
    </div><!-- nav-tabs-custom -->
  </div>

  @can('update', \App\Models\User::class)
    @include ('modals.upload-file', ['item_type' => 'user', 'item_id' => $user->id])
  @endcan



  @stop

@section('moar_scripts')
  @include ('partials.bootstrap-table', ['simple_view' => true])
<script nonce="{{ csrf_token() }}">
$(function () {



  $("#two_factor_reset").click(function(){
    $("#two_factor_resetrow").removeClass('success');
    $("#two_factor_resetrow").removeClass('danger');
    $("#two_factor_resetstatus").html('');
    $("#two_factor_reseticon").html('<x-icon type="spinner" />');
    $.ajax({
      url: '{{ route('api.users.two_factor_reset', ['id'=> $user->id]) }}',
      type: 'POST',
      data: {},
      headers: {
        "X-Requested-With": 'XMLHttpRequest',
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
      },
      dataType: 'json',

      success: function (data) {
        $("#two_factor_reset_toggle").html('').html('<span class="text-danger"><x-icon type="x" /> {{ trans('general.no') }}</span>');
        $("#two_factor_reseticon").html('');
        $("#two_factor_resetstatus").html('<span class="text-success"><x-icon type="checkmark" class="fa-2x" /> ' + data.message + '</span>');

      },

      error: function (data) {
        $("#two_factor_reseticon").html('');
        $("#two_factor_reseticon").html('<x-icon type="warning" class="text-danger" />');
        $('#two_factor_resetstatus').text(data.message);
      }


    });
  });


    // binds to onchange event of your input field
    var uploadedFileSize = 0;
    $('#fileupload').bind('change', function() {
      uploadedFileSize = this.files[0].size;
      $('#progress-container').css('visibility', 'visible');
    });

    $('#fileupload').fileupload({
        //maxChunkSize: 100000,
        dataType: 'json',
        formData:{
        _token:'{{ csrf_token() }}',
        notes: $('#notes').val(),
        },

        progress: function (e, data) {
            //var overallProgress = $('#fileupload').fileupload('progress');
            //var activeUploads = $('#fileupload').fileupload('active');
            var progress = parseInt((data.loaded / uploadedFileSize) * 100, 10);
            $('.progress-bar').addClass('progress-bar-warning').css('width',progress + '%');
            $('#progress-bar-text').html(progress + '%');
            //console.dir(overallProgress);
        },

        done: function (e, data) {
            console.dir(data);
            // We use this instead of the fail option, since our API
            // returns a 200 OK status which always shows as "success"

            if (data && data.jqXHR && data.jqXHR.responseJSON && data.jqXHR.responseJSON.status === "error") {
                var errorMessage = data.jqXHR.responseJSON.messages["file.0"];
                $('#progress-bar-text').html(errorMessage[0]);
                $('.progress-bar').removeClass('progress-bar-warning').addClass('progress-bar-danger').css('width','100%');
                $('.progress-checkmark').fadeIn('fast').html('<x-icon type="xt" class="fa-3x text-danger" />');
            } else {
                $('.progress-bar').removeClass('progress-bar-warning').addClass('progress-bar-success').css('width','100%');
                $('.progress-checkmark').fadeIn('fast');
                $('#progress-container').delay(950).css('visibility', 'visible');
                $('.progress-bar-text').html('Finished!');
                $('.progress-checkmark').fadeIn('fast').html('<x-icon type="checkmark" class="fa-3x text-success" />');
                $.each(data.result, function (index, file) {
                    $('<tr><td>' + file.note + '</td><td>' + file.filename + '</td></tr>').prependTo("#files-table > tbody");
                });
            }
            $('#progress').removeClass('active');


        }
    });
    $("#optional_info").on("click",function(){
        $('#optional_details').fadeToggle(100);
        $('#optional_info_icon').toggleClass('fa-caret-right fa-caret-down');
        var optional_info_open = $('#optional_info_icon').hasClass('fa-caret-down');
        document.cookie = "optional_info_open="+optional_info_open+'; path=/';
    });
});
</script>


@stop
