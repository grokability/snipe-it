{{-- stacked: default false, giving BS3 form-horizontal (label-left,
     input-right grid) which matches every regular edit/create form in
     the app. Pass stacked to opt into a vertical form (labels above
     inputs), typically paired with x-form.row's stacked prop on each
     row. form_class layers additional classes on top of the layout
     default, if a caller needs one. --}}
@props([
    'id' => null,
    'title' => null,
    'action' => null,
    'submitToSelect2' => false,
    'submit_label' => null,
    'submit_class' => 'btn-primary',
    'labelledby' => null,
    'form_attrs' => '',
    'form_class' => null,
    'stacked' => false,
])

@php
    $submitLabel = $submit_label ?? trans('general.save');
    $labelledById = $labelledby ?? ($id ? $id.'Label' : null);
    $onsubmit = $submitToSelect2 ? ' onsubmit="return false"' : '';
    $saveButtonAttrs = $submitToSelect2 ? ' type="button" id="modal-save"' : ' type="submit"';
    $formClass = trim(($stacked ? '' : 'form-horizontal').' '.($form_class ?? ''));
@endphp

@if ($id)
    <div {{ $attributes->merge(['class' => 'modal fade']) }} id="{{ $id }}" tabindex="-1" role="dialog" @if ($labelledById) aria-labelledby="{{ $labelledById }}" @endif aria-hidden="true">
@endif
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('button.close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
                @isset($header)
                    {{ $header }}
                @else
                    <h4 class="modal-title" @if ($labelledById) id="{{ $labelledById }}" @endif>{{ $title }}</h4>
                @endisset
            </div>

            {{-- Two form shapes. submitToSelect2 modals: <form> lives inside
                 .modal-body so snipeit_modals.js's
                 $('.modal-body form').attr('action') can read the target url,
                 and the save button in .modal-footer stays outside the form
                 (delegated click handler POSTs via jQuery and pushes the new
                 row into the trigger select2). Standalone modals: <form>
                 wraps both body and footer so the submit button natively
                 submits the form.   --}}
            @if ($submitToSelect2)
                <div class="modal-body">
                    <x-alert type="danger" id="modal_error_msg" style="display:none"></x-alert>
                    <form action="{{ $action }}" method="POST" @if ($formClass) class="{{ $formClass }}" @endif{!! $onsubmit !!} {!! $form_attrs !!}>
                        @csrf
                        {{ $slot }}
                    </form>
                </div>
                <div class="modal-footer">
                    @isset($footer)
                        {{ $footer }}
                    @else
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('button.cancel') }}</button>
                        <button class="btn {{ $submit_class }} pull-right"{!! $saveButtonAttrs !!}>{{ $submitLabel }}</button>
                    @endisset
                </div>
            @else
                <form action="{{ $action }}" method="POST" @if ($formClass) class="{{ $formClass }}" @endif{!! $onsubmit !!} {!! $form_attrs !!}>
                    @csrf
                    <div class="modal-body">
                        {{ $slot }}
                    </div>
                    <div class="modal-footer">
                        @isset($footer)
                            {{ $footer }}
                        @else
                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('button.cancel') }}</button>
                            <button class="btn {{ $submit_class }} pull-right"{!! $saveButtonAttrs !!}>{{ $submitLabel }}</button>
                        @endisset
                    </div>
                </form>
            @endif
        </div>
    </div>
@if ($id)
</div>
@endif
