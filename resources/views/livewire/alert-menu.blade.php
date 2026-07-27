{{-- Top-nav alert bell. Hydrated lazily via <livewire:alert-menu /> in
     layouts/default.blade.php. The setting-gate lives on the parent
     tag — this view assumes the operator opted in. --}}
<li class="dropdown tasks-menu">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <x-icon type="alerts"/>
        <span class="sr-only">{{ trans('general.alerts') }}</span>
        @if (count($alert_items) + count($deprecations))
            <span class="label label-danger">{{ count($alert_items) + count($deprecations) }}</span>
        @endif
    </a>
    <ul class="dropdown-menu">

        @if ((count($alert_items) + count($deprecations)) > 0)

            @can('superadmin')
                @if ($deprecations)
                    @foreach ($deprecations as $deprecation)
                        @if ($deprecation['check'])
                            <li class="header alert-warning">{!! $deprecation['message'] !!}</li>
                        @endif
                    @endforeach
                @endif
            @endcan

            @if ($alert_items)
                <li class="header">
                    {{ trans_choice('general.quantity_minimum', count($alert_items)) }}
                </li>
                <li>
                    <ul class="menu">
                        {{-- Cap the visual list at 50 to keep the dropdown a
                             sensible height even when a big deployment has
                             hundreds of low-inventory items simultaneously. --}}
                        @if (count($alert_items) <= 50)
                            @foreach ($alert_items as $alert_item)
                                <li>
                                    <a href="{{ route($alert_item['type'].'.show', $alert_item['id']) }}">
                                        <h2 class="task_menu">{{ $alert_item['name'] }}
                                            <small class="pull-right">
                                                {{ $alert_item['remaining'] }} {{ trans('general.remaining') }}
                                            </small>
                                        </h2>
                                        <div class="progress xs">
                                            <div class="progress-bar progress-bar-yellow"
                                                 style="width: {{ $alert_item['percent'] }}%"
                                                 role="progressbar"
                                                 aria-valuenow="{{ $alert_item['percent'] }}"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100">
                                                <span class="sr-only">{{ $alert_item['percent'] }}%</span>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </li>
            @endif
        @else
            <li class="header">
                {{ trans_choice('general.quantity_minimum', 0) }}
            </li>
        @endif
    </ul>
</li>
