<div @if ($targetId === null) style="display: none;" @endif>
    <div class="box box-primary">
        <div class="box-header with-border">
            <h2 class="box-title">
                {{ trans('admin/users/general.current_items', ['item' => $noun, 'target' => $targetNoun]) }}
            </h2>
        </div>
        <div class="box-body">
            {{-- Shown while any Livewire request to this component is in
                 flight; the results block below is hidden at the same time
                 (wire:loading.remove) so the operator sees a clear "we're
                 fetching new data" state instead of stale results with a
                 tiny header icon. --}}
            <div wire:loading class="text-center text-muted" style="padding: 40px 0;">
                <i class="fas fa-spinner fa-spin fa-3x" aria-hidden="true"></i>
                <div style="padding-top: 12px;">{{ trans('general.loading') }}</div>
            </div>
            <div wire:loading.remove class="row">
                <div class="col-md-12">
                    <table class="table table-striped">
                        @switch($type)

                            @case('assets')
                                <thead>
                                    <tr>
                                        <th scope="col"></th>
                                        <th scope="col">{{ trans('general.name') }}</th>
                                        <th scope="col">{{ trans('admin/hardware/form.tag') }}</th>
                                        <th scope="col">{{ trans('admin/hardware/form.serial') }}</th>
                                        <th scope="col">{{ trans('admin/hardware/form.checkout_date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $asset)
                                        <tr>
                                            <td>
                                                @if ($asset->getImageUrl() ?? null)
                                                    <img src="{{ $asset->getImageUrl() }}" style="max-height: {{ $snipeSettings->thumbnail_max_h }}px; width: auto;" alt="">
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('hardware.show', $asset->id) }}">
                                                    {{ $asset->name ? $asset->name.' ('.$asset->model?->name.')' : $asset->model?->name }}
                                                </a>
                                            </td>
                                            <td>{{ $asset->asset_tag }}</td>
                                            <td>{{ $asset->serial }}</td>
                                            <td>{{ $asset->last_checkout ? \App\Helpers\Helper::getFormattedDateObject($asset->last_checkout, 'date', false) : '' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5">{{ trans('admin/users/message.nothing_currently_assigned') }}</td></tr>
                                    @endforelse
                                </tbody>
                                @break

                            @case('licenses')
                                @php($canViewKeys = Gate::allows('viewKeys', \App\Models\License::class))
                                <thead>
                                    <tr>
                                        <th scope="col">{{ trans('general.name') }}</th>
                                        @if ($canViewKeys)
                                            <th scope="col">{{ trans('admin/licenses/form.license_key') }}</th>
                                        @endif
                                        <th scope="col">{{ trans('admin/hardware/form.checkout_date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $license)
                                        <tr>
                                            <td><a href="{{ route('licenses.show', $license->id) }}">{{ $license->name }}</a></td>
                                            @if ($canViewKeys)
                                                <td>{{ $license->serial }}</td>
                                            @endif
                                            <td>{{ $license->pivot?->created_at ? \App\Helpers\Helper::getFormattedDateObject($license->pivot->created_at, 'date', false) : '' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="{{ $canViewKeys ? 3 : 2 }}">{{ trans('admin/users/message.nothing_currently_assigned') }}</td></tr>
                                    @endforelse
                                </tbody>
                                @break

                            @case('accessories')
                                {{-- Row shape differs by target: User branch returns Accessory-with-pivot (belongsToMany
                                     accessories()), Asset / Location branches return AccessoryCheckout with ->accessory
                                     eager loaded (assignedAccessories()). Reconciled inline via null-coalescing. --}}
                                <thead>
                                    <tr>
                                        <th scope="col">{{ trans('general.name') }}</th>
                                        <th scope="col">{{ trans('admin/hardware/form.checkout_date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $item)
                                        <tr>
                                            <td><a href="{{ route('accessories.show', $item->accessory?->id ?? $item->id) }}">{{ $item->accessory?->name ?? $item->name }}</a></td>
                                            <td>{{ ($item->pivot?->created_at ?? $item->created_at) ? \App\Helpers\Helper::getFormattedDateObject($item->pivot?->created_at ?? $item->created_at, 'date', false) : '' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2">{{ trans('admin/users/message.nothing_currently_assigned') }}</td></tr>
                                    @endforelse
                                </tbody>
                                @break

                            @case('consumables')
                                <thead>
                                    <tr>
                                        <th scope="col">{{ trans('general.name') }}</th>
                                        <th scope="col">{{ trans('admin/hardware/form.checkout_date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $consumable)
                                        <tr>
                                            <td><a href="{{ route('consumables.show', $consumable->id) }}">{{ $consumable->name }}</a></td>
                                            <td>{{ $consumable->pivot?->created_at ? \App\Helpers\Helper::getFormattedDateObject($consumable->pivot->created_at, 'date', false) : '' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2">{{ trans('admin/users/message.nothing_currently_assigned') }}</td></tr>
                                    @endforelse
                                </tbody>
                                @break

                            @case('components')
                                <thead>
                                    <tr>
                                        <th scope="col">{{ trans('general.name') }}</th>
                                        <th scope="col">{{ trans('general.qty') }}</th>
                                        <th scope="col">{{ trans('admin/hardware/form.checkout_date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $component)
                                        <tr>
                                            <td><a href="{{ route('components.show', $component->id) }}">{{ $component->name }}</a></td>
                                            <td>{{ $component->pivot->assigned_qty ?? 1 }}</td>
                                            <td>{{ $component->pivot?->created_at ? \App\Helpers\Helper::getFormattedDateObject($component->pivot->created_at, 'date', false) : '' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3">{{ trans('admin/users/message.nothing_currently_assigned') }}</td></tr>
                                    @endforelse
                                </tbody>
                                @break

                        @endswitch
                    </table>
                </div>
            </div>
        </div>
    </div>

    @script
        <script>
            // Bridge the non-Livewire target selects (user / asset / location)
            // plus the checkout-selector radio toggle to this component. On
            // any change we resolve the currently-active target (based on the
            // radio) + its select value and dispatch. Pages that don't have
            // the radio (consumables/checkout is user-only, components/checkout
            // is asset-only) fall back to the server-supplied defaultTargetType
            // baked into the component's mount config.
            //
            // The surrounding Livewire directive runs once per component
            // INSTANCE, so binding at this level doesn't accumulate handlers.
            var dispatchTarget = function () {
                var radioVal = $('input[name="checkout_to_type"]:checked').val();
                var targetType = radioVal || @json($defaultTargetType);
                var selectSelectors = {
                    'user': '#assigned_user_select',
                    'asset': '#assigned_asset_select',
                    'location': '#assigned_location_location_select',
                };
                var $select = $(selectSelectors[targetType]);
                var targetId = $select.length ? ($select.val() || null) : null;

                $wire.dispatch('checkout-target-selected', {
                    targetType: targetType,
                    targetId: targetId,
                });
            };

            $('#assigned_user, #assigned_asset, #assigned_location').on('change', dispatchTarget);
            $('input[name="checkout_to_type"]').on('change', dispatchTarget);

            // Initial state on page load: pick up whatever the parent form
            // has server-rendered as the pre-selected target (session
            // remembers checkout_to_type across errors) so the sidebar shows
            // straight away instead of waiting for a change event.
            dispatchTarget();
        </script>
    @endscript
</div>
