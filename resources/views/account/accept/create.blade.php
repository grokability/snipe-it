@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{trans('general.accept', ['asset' => $acceptance->checkoutable->present()->name()])}}
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

        #eula_div {
            width: 100%;
            height: auto;
            overflow: auto;
        }

        .m-signature-pad--body {
            border-style: solid;
            border-color: grey;
            border-width: thin;
        }

    </style>


    <form class="form-horizontal" method="post" action="" autocomplete="off">
        <!-- CSRF Token -->
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />


        <div class="row">
            <div class="col-sm-12 col-sm-offset-1 col-md-10 col-md-offset-1">
                <div class="panel box box-default">
                    <div class="box-body">
                        <div class="col-md-12" style="padding-top: 20px;">
                        @if ($acceptance->checkoutable->getEula())
                            <div id="eula_div" style="padding-bottom: 20px">
                                {!!  $acceptance->checkoutable->getEula() !!}
                            </div>
                        @endif
                        </div>
                        <div class="col-md-12">
                        <h3>{{$acceptance->checkoutable->present()->name()}}</h3>
                        </div>
                        <div class="col-md-12">
                            <label class="form-control">
                                <input type="radio" name="asset_acceptance" id="accepted" value="accepted">
                                {{trans('general.i_accept')}}
                            </label>
                            <label class="form-control">
                                <input type="radio" name="asset_acceptance" id="declined" value="declined">
                                {{trans('general.i_decline')}}
                            </label>

                        </div>
                        <div class="col-md-12">
                            <br>
                            <div class="col-md-12" style="display:block;">
                                <label id="note_label" for="note" style="text-align:center;" >{{trans('admin/settings/general.acceptance_note')}}</label>
                            </div>
                            <div class="col-md-12">
                                <textarea id="note" name="note" rows="4" value="note" class="form-control" style="width:100%"></textarea>
                            </div>
                        </div>

                        @if ($snipeSettings->require_accept_signature=='1')
                            <div class="col-md-12">
                                <h3 style="padding-top: 20px">{{trans('general.sign_tos')}}</h3>
                                <div id="signature-pad" class="m-signature-pad">
                                    <div class="m-signature-pad--body col-md-12 col-sm-12 col-lg-12 col-xs-12">
                                        <canvas style="width:100%;"></canvas>
                                        <input type="hidden" name="signature_output" id="signature_output">
                                    </div>
                                    <div class="col-md-12 col-sm-12 col-lg-12 col-xs-12 text-center">
                                        <button type="button" class="btn btn-sm btn-default clear" data-action="clear" id="clear_button">{{trans('general.clear_signature')}}</button>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if (auth()->user()->email!='')
                            <div class="col-md-12" style="padding-top: 20px; display: none;" id="showEmailBox">
                                <label class="form-control">
                                    <input type="checkbox" value="1" name="send_copy" id="send_copy" checked="checked" aria-label="send_copy">
                                    {{ trans('mail.send_pdf_copy') }} ({{ auth()->user()->email }})
                                </label>
                            </div>
                        @endif

                    </div> <!-- / box-body -->
                    <div class="box-footer text-right" style="display: none;" id="showSubmit">
                        <button type="submit" class="btn btn-success" id="submit-button">
                            <i class="fa fa-check icon-white" aria-hidden="true" id="submitIcon"></i>
                            <span id="buttonText">
                                {{ trans('general.i_accept_item') }}
                            </span>
                        </button>
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

            if ($(this).is(':checked') && $(this).attr('id') == 'declined') {
                $("#showEmailBox").hide();
                $("#showSubmit").show();
                $("#submit-button").removeClass("btn-success").addClass("btn-danger").show();
                $("#submitIcon").removeClass("fa-check").addClass("fa-times");
                $("#buttonText").text('{{ trans('general.i_decline_item') }}');
                $("#note").prop('required', true);

            } else if ($(this).is(':checked') && $(this).attr('id') == 'accepted') {
                $("#showEmailBox").show();
                $("#showSubmit").show();
                $("#submit-button").removeClass("btn-danger").addClass("btn-success").show();
                $("#submitIcon").removeClass("fa-check").addClass("fa-check");
                $("#buttonText").text('{{ trans('general.i_accept_item') }}');
                $("#note").prop('required', false);



            }

        });



    </script>
@stop
