{{-- Top-nav alert bell. Hydrated lazily via <livewire:alert-menu /> in
     layouts/default.blade.php. The setting-gate lives on the parent
     tag — this view assumes the operator opted in. --}}
<li class="dropdown tasks-menu">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <x-icon type="alerts"/>
        <span class="sr-only">{{ trans('general.alerts') }}</span>
        {{-- Always render a label so the top-nav width stays constant
             whether or not there are alerts. When count > 0, show the
             real badge. When zero, hide with visibility (still occupies
             layout width). Prevents the layout shift that would otherwise
             happen when the lazy placeholder hydrates in.

             Null-coalesce inline on ($alert_count ?? 0) is defensive.
             AlertMenu::render() always passes alert_count, but a Rollbar
             report from develop.snipeitapp.com showed
             "Undefined variable $alert_count" firing from a Livewire
             /livewire/update flow. Cheaper to guard the blade than to
             chase down which lifecycle path lost the data. --}}
        <span class="label label-danger" @if (($alert_count ?? 0) === 0) aria-hidden="true" style="visibility: hidden" @endif>{{ ($alert_count ?? 0) ?: '0' }}</span>
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
                        {{-- Cap the visual list at 50 so the dropdown stays
                             a sensible height even when a big deployment has
                             hundreds of low-inventory items simultaneously.
                             Earlier form gated the entire @foreach on count
                             <= 50, which rendered an empty menu when the
                             count exceeded 50. Slice-then-render shows the
                             first 50 regardless of total. --}}
                        @foreach (array_slice($alert_items, 0, 50) as $alert_item)
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
