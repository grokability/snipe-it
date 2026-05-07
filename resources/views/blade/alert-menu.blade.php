@props([
    'snipeSettings' => \App\Models\Setting::getSettings(),
])

@if ($snipeSettings->show_alerts_in_menu=='1')
<!-- Tasks: style can be found in dropdown.less -->
<?php
    $alert_items = \App\Helpers\Helper::checkLowInventory();
    $deprecations = \App\Helpers\Helper::deprecationCheck();
?>

<li class="dropdown tasks-menu">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <x-icon type="alerts" />
        <span class="sr-only">{{ trans('general.alerts') }}</span>
        @if(count($alert_items) + count($deprecations))
            <span class="label label-danger">{{ count($alert_items) + count($deprecations)}}</span>
        @endif
    </a>
    <ul class="dropdown-menu">

        @if ((count($alert_items) + count($deprecations)) > 0)

            @can('superadmin')
                @if($deprecations)
                    @foreach ($deprecations as $key => $deprecation)
                        @if ($deprecation['check'])
                            <li class="header alert-warning">{!! $deprecation['message'] !!}</li>
                        @endif
                    @endforeach
                @endif
            @endcan

            @if($alert_items)
                <li class="header">
                    {{ trans_choice('general.quantity_minimum', count($alert_items)) }}
                </li>
                <li>
                    <!-- inner menu: contains the actual data -->
                    <ul class="menu">

                        @if (count($alert_items) <= 50)
                            @for($i = 0; count($alert_items) > $i; $i++)


                            <!-- Task item -->
                            <li>
                                <a href="{{ route($alert_items[$i]['type'].'.show', $alert_items[$i]['id'])}}">
                                    <h2 class="task_menu">{{ $alert_items[$i]['name'] }}
                                        <small class="pull-right">
                                            {{ $alert_items[$i]['remaining'] }} {{ trans('general.remaining') }}
                                        </small>
                                    </h2>
                                    <div class="progress xs">
                                        <div class="progress-bar progress-bar-yellow"
                                             style="width: {{ $alert_items[$i]['percent'] }}%"
                                             role="progressbar"
                                             aria-valuenow="{{ $alert_items[$i]['percent'] }}"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                            <span class="sr-only">
                                                {{ $alert_items[$i]['percent'] }}%
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            </li>

                                <!-- end task item -->
                            @endfor
                        @endif
                    </ul>
                </li>
            @endif
        @else
            <li class="header">
                {{ trans_choice('general.quantity_minimum', 0) }}
            </li>

        @endif
        {{--                                        <li class="footer">--}}
        {{--                                          <a href="#">{{ trans('general.tasks_view_all') }}</a>--}}
        {{--                                        </li>--}}
    </ul>
</li>

@if (!$slot->isEmpty())
    {{ $slot }}
@endif
@endif