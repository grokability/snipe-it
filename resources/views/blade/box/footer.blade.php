@props([
    'cancel_route' => url()->previous(),
])

<!-- Start box footer component -->
<div class="box-footer">
    <div class="row">

        <div class="col-md-3">
            <a class="btn btn-link" href="{{ $cancel_route }}">
                {{ trans('general.cancel') }}
            </a>
        </div>

        <div class="col-md-9 text-right">
            <x-input.button class="btn-success" id="submit_button">
                <x-icon type="checkmark" class="icon-white" />
                {{ trans('general.save') }}
            </x-input.button>
        </div>

    </div>
</div>