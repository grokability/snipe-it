@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{trans('general.accept', ['asset' => $acceptance->checkoutable->display_name])}}
    @parent
@stop


{{-- Page content --}}
@section('content')


    <link rel="stylesheet" href="{{ url('css/signature-pad.min.css') }}">

    <style>
        .form-horizontal .control-label, .form-horizontal .radio, .form-horizontal .checkbox, .form-horizontal .radio-inline, .form-horizontal .checkbox-inline {
            padding-top: 17px;
            padding-right: 10px;
        }

        .m-signature-pad--body {
            border-style: dashed;
            border-color: grey;
            border-width: thick;
            padding-top: 0px;
        }


        .m-signature-pad {
            box-shadow: none;
            background-color: inherit;
            border: none;

        }


    </style>


    <form class="form-horizontal" method="post" action="" autocomplete="off">
        <!-- CSRF Token -->
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />


        <div class="row">
            <div class="col-sm-12 col-sm-offset-1 col-md-10 col-md-offset-1">
                <div class="panel box box-default">
                    <div class="box-header with-border">
                        <h2 class="box-title">
                            {{ $acceptance->checkoutable->display_name }}
                            @if ($acceptance->qty > 1)
                                <strong>×{{ $acceptance->qty }}</strong>
                            @endif

                            {!!  (($acceptance->checkoutable) && ($acceptance->checkoutable->serial)) ? '<br>'.trans('general.serial_number').': '.e($acceptance->checkoutable->serial) : '' !!}

                        </h2>
                    </div>
                    <div class="box-body">
                        @if ($acceptance->checkoutable->getEula())
                            <div class="col-md-12" style="padding-top: 5px; padding-bottom: 15px;">
                                <div style="background-color: rgba(211,211,211,0.25); padding: 0px 10px 10px 10px; border: lightgrey 1px solid;">
                                    {!!  str_replace('<p>', '<p dir="auto">', Helper::parseEscapedMarkedown($acceptance->checkoutable->getEula())) !!}
                                </div>
                            </div>
                        @endif
                        <div class="col-md-12">
                            <label class="form-control">
                                <input type="radio" name="asset_acceptance" id="accepted" value="accepted">
                                @if ($acceptance->qty)
                                    {{trans_choice('general.i_accept_with_count', $acceptance->qty)}}
                                @else
                                    {{trans('general.i_accept')}}
                                @endif
                            </label>
                            <label class="form-control">
                                <input type="radio" name="asset_acceptance" id="declined" value="declined">
                                @if ($acceptance->qty)
                                    {{trans_choice('general.i_decline_with_count', $acceptance->qty)}}
                                @else
                                    {{trans('general.i_decline')}}
                                @endif
                            </label>

                        </div>
                        <div class="col-md-12">
                            <br>
                                <label id="note_label" for="note" style="text-align:center;" >{{trans('admin/settings/general.acceptance_note')}}</label>
                                <br>
                                <textarea id="note" name="note" rows="4" class="form-control" style="width:100%">{{ old('note') }}</textarea>

                        </div>

                        @if ($snipeSettings->require_accept_signature=='1')
                            <div class="col-md-12">
                                <h3 style="padding-top: 20px">{{trans('general.sign_tos')}}</h3>
                                <div id="signature-pad" class="m-signature-pad">
                                    <div class="m-signature-pad--body col-md-12 col-sm-12 col-lg-12 col-xs-12">
                                        <canvas style="width:100%;"></canvas>
                                        <input type="hidden" name="signature_output" id="signature_output">
                                    </div>
                                    <div class="col-md-12 col-sm-12 col-lg-12 col-xs-12 text-left">
                                        <button type="button" class="btn btn-sm btn-default clear" data-action="clear" id="clear_button">{{trans('general.clear_signature')}}</button>
                                    </div>
                                </div>
                            </div>
                        @endif


                    </div> <!-- / box-body -->
                    <div class="box-footer" style="display: none;" id="showSubmit">
                        <div class="row">
                            <div class="col-md-7">
                                @if (auth()->user()->email!='')
                                    <div class="col-md-12" style="display: none;" id="showEmailBox">
                                        <label class="form-control">
                                            <input type="checkbox" value="1" name="send_copy" id="send_copy" checked="checked" aria-label="send_copy">
                                            {{ trans('mail.send_pdf_copy') }} ({{ auth()->user()->email }})
                                        </label>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-5 text-right">
                                <button type="submit" class="btn btn-success" id="submit-button">
                                    <i class="fa fa-check icon-white" aria-hidden="true" id="submitIcon"></i>
                                    <span id="buttonText">
                                {{ trans_choice('general.i_accept_item', $acceptance->qty ?? null) }}
                            </span>
                                </button>
                            </div>
                        </div>

                    </div><!-- /.box-footer -->
                </div> <!-- / box-default -->
            </div> <!-- / col -->
        </div> <!-- / row -->
    </form>

@stop

@section('moar_scripts')

    <script nonce="{{ csrf_token() }}">

        @if ($snipeSettings->require_accept_signature=='1')

        var wrapper = document.getElementById("signature-pad"),
            clearButton = wrapper.querySelector("[data-action=clear]"),
            saveButton = wrapper.querySelector("[data-action=save]"),
            canvas = wrapper.querySelector("canvas"),
            signaturePad;

        signaturePad = new SignaturePad(canvas);

        // Adjust canvas coordinate space taking into account pixel ratio,
        // to make it look crisp on smaller screens.
        // https://github.com/szimek/signature_pad#handling-high-dpi-screens
        // (This also causes canvas to be cleared.)
        function resizeCanvas() {
            // When zoomed out to less than 100%, for some very strange reason,
            // some browsers report devicePixelRatio as less than 1
            // and only part of the canvas is cleared then.
            var ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear(); // otherwise isEmpty() might return incorrect value
        }
        window.onresize = resizeCanvas;
        resizeCanvas();

        $('#clear_button').on("click", function (event) {
            signaturePad.clear();
        });

        $('#submit-button').on("click", function (event) {
            if (signaturePad.isEmpty()) {
                alert("Please provide signature first.");
                return false;
            } else {
                $('#signature_output').val(signaturePad.toDataURL());
            }
        });
        @endif
        
        $('[name="asset_acceptance"]').on('change', function() {

            if ($(this).is(':checked') && $(this).attr('id') === 'declined') {
                $("#showEmailBox").hide();
                $("#showSubmit").show();
                $("#submit-button").removeClass("btn-success").addClass("btn-danger").show();
                $("#submitIcon").removeClass("fa-check").addClass("fa-times");
                $("#buttonText").text('{{ trans_choice('general.i_decline_item', $acceptance->qty ?? 1) }}');
                $("#note").prop('required', true);

            } else if ($(this).is(':checked') && $(this).attr('id') === 'accepted') {
                $("#showEmailBox").show();
                $("#showSubmit").show();
                $("#submit-button").removeClass("btn-danger").addClass("btn-success").show();
                $("#submitIcon").removeClass("fa-check").addClass("fa-check");
                $("#buttonText").text('{{ trans_choice('general.i_accept_item', $acceptance->qty ?? 1) }}');
                $("#note").prop('required', false);
            }
        });
    </script>
@stop
