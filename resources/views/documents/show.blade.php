@extends('layouts/default')

@section('title')
    {{ $document->number }}
    @parent
@stop

@push('css')
    <link rel="stylesheet" href="{{ url('css/signature-pad.min.css') }}">
    <style>
        #signature-pad { padding: 16px; height: auto; min-width: 0; margin: 0; }
        #signature-pad .m-signature-pad--body { position: relative; top: auto; bottom: auto; width: 100%; height: 160px; }
        #signature-pad .m-signature-pad--footer { position: relative; left: auto; right: auto; bottom: auto; height: auto; }
        .document-toolbar { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
        .document-toolbar a { white-space: normal; }
        .document-terms { overflow-wrap: anywhere; }
        .document-terms table { width: 100%; border-collapse: collapse; }
        .document-terms td, .document-terms th { border: 1px solid #ddd; padding: 6px; }
        #terms-confirmation label { padding-left: 32px; }
    </style>
@endpush

@section('content')

    <div class="row">
        <div class="col-md-8">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        {{ $document->number }}
                        <label class="label
                            @if ($document->status == 'signed') label-success
                            @elseif ($document->status == 'cancelled') label-danger
                            @elseif ($document->status == 'draft') label-default
                            @else label-warning @endif">
                            {{ trans('documents.statuses.'.$document->status) }}
                        </label>
                    </h3>
                    <div class="document-toolbar">
                        @can('download', $document)
                            <a href="{{ route('documents.web-print', $document) }}" class="btn btn-sm btn-default" target="_blank" rel="noopener">{{ trans('documents.custom.web_print') }}</a>
                            @if ($document->pdf_path)
                                <a href="{{ route('documents.pdf', ['document' => $document, 'inline' => 1]) }}" class="btn btn-sm btn-default" target="_blank" rel="noopener">
                                    <i class="fa fa-print" aria-hidden="true"></i> {{ trans('documents.general.print_pdf') }}</a>
                                <a href="{{ route('documents.pdf', $document) }}" class="btn btn-sm btn-default">
                                    <x-icon type="download" /> {{ trans('documents.general.download_pdf') }}</a>
                            @endif
                        @endcan
                    </div>
                </div>
                <div class="box-body">

                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <td>{{ trans('documents.general.type') }}</td>
                                <td>{{ ucfirst($document->type) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('documents.general.assignee') }}</td>
                                <td>
                                    @if ($document->assignedTo)
                                        <a href="{{ route('users.show', $document->assignedTo) }}">{{ $document->assignedTo->present()->fullName }}</a>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>{{ trans('documents.general.template') }}</td>
                                <td>{{ $document->templateVersion?->snapshotField('name') }} (v{{ $document->templateVersion?->version }})</td>
                            </tr>
                            <tr>
                                <td>{{ trans('general.created_at') }}</td>
                                <td>{{ $document->created_at }} &nbsp;({{ \App\Support\Jalali\Jalali::format($document->created_at, 'Y/m/d') }})</td>
                            </tr>
                            @if ($document->signed_at)
                                <tr>
                                    <td>{{ trans('documents.general.signed_at') }}</td>
                                    <td>{{ $document->signed_at }}</td>
                                </tr>
                            @endif
                            @if ($document->notes)
                                <tr><th scope="row">{{ trans('documents.general.notes') }}</th><td style="white-space: pre-wrap;">{{ $document->notes }}</td></tr>
                            @endif
                            @if ($document->cancel_reason)
                                <tr><th scope="row">{{ trans('documents.general.cancel_reason') }}</th><td>{{ $document->cancel_reason }}</td></tr>
                            @endif
                        </tbody>
                    </table>

                    {{-- Covered assets (frozen snapshots, spec §11) --}}
                    <h4>{{ trans('documents.general.assets') }} ({{ $document->items->count() }})</h4>
                    <p class="text-muted">{{ trans('documents.general.inventory_help') }}</p>
                    <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">{{ trans('general.asset_tag') }}</th>
                                <th scope="col">{{ trans('general.name') }}</th>
                                <th scope="col">{{ trans('admin/hardware/form.model') }}</th>
                                <th scope="col">{{ trans('admin/hardware/form.serial') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($document->items as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><a href="{{ route('hardware.show', $item->item_id) }}">{{ $item->asset_tag }}</a></td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->model_name }}</td>
                                    <td>{{ $item->serial }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>

                    @if ($document->templateVersion?->eula_enabled)
                        <h4>{!! app(\App\Services\Documents\DocumentLayout::class)->text($document->templateVersion->eula_title ?: trans('documents.general.eula'), $document) !!}</h4>
                        <p class="text-muted">{{ trans('documents.general.version') }}: {{ $document->templateVersion->eula_version }}</p>
                        <div class="well document-terms">{!! app(\App\Services\Documents\DocumentLayout::class)->terms($document) !!}</div>
                    @endif

                    {{-- Signature panel (spec §7) --}}
                    <h4>{!! app(\App\Services\Documents\DocumentLayout::class)->text($document->templateVersion->snapshot['signature_config']['heading'] ?? trans('documents.general.signatures'), $document) !!}</h4>
                    @if ($document->templateVersion->snapshot['signature_config']['instructions'] ?? null)
                        <p>{!! app(\App\Services\Documents\DocumentLayout::class)->text($document->templateVersion->snapshot['signature_config']['instructions'], $document) !!}</p>
                    @endif
                    <p>{{ trans('documents.general.signature_progress', ['signed' => count(array_intersect($progress['required'], $progress['obtained'])), 'required' => count($progress['required'])]) }}</p>
                    <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>{{ trans('documents.general.role') }}</th>
                                <th>{{ trans('documents.general.signed_by') }}</th>
                                <th>{{ trans('documents.general.method') }}</th>
                                <th>{{ trans('documents.general.signed_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($progress['required'] as $role)
                                @php $signature = $document->signatures->firstWhere('role', $role); @endphp
                                <tr>
                                    <td>{{ $document->templateVersion->snapshot['signature_config']['labels'][$role] ?? ucfirst(str_replace('_', ' ', $role)) }}</td>
                                    <td>{{ $signature->signer_name ?? trans('documents.general.awaiting_signature') }}</td>
                                    <td>{{ $signature ? trans('documents.general.'.$signature->method) : '—' }}</td>
                                    <td>{{ $signature->signed_at ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>

                    {{-- Sign / lifecycle actions --}}
                    @if (! $document->isImmutable())
                        @can('sign', $document)
                            @php
                                $openRoles = array_filter(array_diff($progress['required'], $progress['obtained']), fn ($role) => auth()->user()->can('signRole', [$document, $role, 'digital']) || auth()->user()->can('signRole', [$document, $role, 'printed']));
                            @endphp
                            @if (count($openRoles) > 0)
                                <form method="POST" action="{{ route('documents.sign', $document) }}" id="sign-form">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="method" value="digital" id="sign-method">

                                    <div class="form-group">
                                        <label for="role">{{ trans('documents.general.role') }}</label>
                                        <select class="form-control select2" name="role" id="role" data-minimum-results-for-search="Infinity" style="width: 100%;">
                                            @foreach ($openRoles as $role)
                                                <option value="{{ $role }}" data-digital="{{ auth()->user()->can('signRole', [$document, $role, 'digital']) ? '1' : '0' }}">{{ $document->templateVersion->snapshot['signature_config']['labels'][$role] ?? ucfirst(str_replace('_', ' ', $role)) }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <p id="signature-help" class="help-block">{{ trans('documents.general.signature_help') }}</p>
                                    <div id="signature-pad" class="m-signature-pad">
                                        <div class="m-signature-pad--body">
                                            <canvas style="width:100%; height:160px; touch-action:none;" aria-label="{{ trans('documents.general.sign_digital') }}" aria-describedby="signature-help"></canvas>
                                            <input type="hidden" name="signature" id="signature_output">
                                        </div>
                                        <div class="m-signature-pad--footer" style="margin-top: 8px;">
                                            <button type="button" class="btn btn-sm btn-default clear" data-action="clear" id="clear_button">{{ trans('general.clear_signature') }}</button>
                                        </div>
                                    </div>

                                    @if ($document->templateVersion?->eula_enabled)
                                        <div class="checkbox" id="terms-confirmation">
                                            <label><input type="checkbox" name="agree_terms" id="agree_terms" value="1"> {{ trans('documents.general.agree_terms') }}</label>
                                            <x-form.error name="agree_terms" />
                                        </div>
                                    @endif
                                    <div style="margin-top: 10px;">
                                        <button type="submit" class="btn btn-success" id="sign-digital-button">{{ trans('documents.general.save_signature') }}</button>
                                        <button type="submit" class="btn btn-default" id="sign-printed-button">{{ trans('documents.general.paper_signature') }}</button>
                                    </div>
                                </form>
                            @endif
                        @endcan
                    @endif

                    @can('update', $document)
                        @if ($document->status == 'generated')
                            <form method="POST" action="{{ route('documents.pending', $document) }}" style="margin-top: 12px;">
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-default">{{ trans('documents.general.pending_action') }}</button>
                            </form>
                        @endif
                    @endcan

                    @can('cancel', $document)
                        @if (! $document->isImmutable())
                            <details style="margin-top: 20px;">
                                <summary class="text-danger">{{ trans('documents.general.cancel') }}</summary>
                                <p class="help-block">{{ trans('documents.general.cancel_help') }}</p>
                            <form method="POST" action="{{ route('documents.cancel', $document) }}" style="margin-top: 12px;">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label for="cancel_reason">{{ trans('documents.general.cancel_reason') }}</label>
                                    <input id="cancel_reason" type="text" name="cancel_reason" class="form-control" placeholder="{{ trans('documents.general.cancel_reason') }}" required>
                                </div>
                                <button type="submit" class="btn btn-danger">{{ trans('documents.general.cancel') }}</button>
                            </form>
                            </details>
                        @endif
                    @endcan

                </div>
            </div>
        </div>

        <div class="col-md-4">
            @if ($document->pdf_sha256)
                <div class="well">
                    <details>
                        <summary>{{ trans('documents.general.technical_details') }}</summary>
                        <p>{{ trans('documents.general.sha256') }}</p>
                        <code style="word-break: break-all;">{{ $document->pdf_sha256 }}</code>
                    </details>
                </div>
            @endif
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('documents.general.audit_trail') }}</h3>
                </div>
                <div class="box-body">
                    <table class="table table-condensed">
                        <thead>
                            <tr><th>{{ trans('general.date') }}</th><th>{{ trans('general.action') }}</th><th>{{ trans('documents.general.recorded_by') }}</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td>{{ $log->created_at }}</td>
                                    <td>{{ $log->action_type }}</td>
                                    <td>{{ $log->adminuser?->present()->fullName }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('moar_scripts')
    <script nonce="{{ csrf_token() }}">
        $(function () {
            var wrapper = document.getElementById("signature-pad");
            if (!wrapper) { return; }

            var canvas = wrapper.querySelector("canvas"),
                signaturePad = new SignaturePad(canvas);

            var resizeCanvas = function () {
                var saved = document.createElement('canvas');
                saved.width = canvas.width;
                saved.height = canvas.height;
                saved.getContext('2d').drawImage(canvas, 0, 0);
                var width = canvas.offsetWidth;
                var height = canvas.offsetHeight;
                if (!width || !height) { return; }
                var ratio = Math.min(Math.max(window.devicePixelRatio || 1, 1), 1200 / width, 500 / height);
                canvas.width = Math.floor(width * ratio);
                canvas.height = Math.floor(height * ratio);
                canvas.getContext('2d').scale(ratio, ratio);
                canvas.getContext('2d').drawImage(saved, 0, 0, width, height);
            };
            window.addEventListener('resize', resizeCanvas);
            resizeCanvas();

            $('#clear_button').on("click", function () { signaturePad.clear(); });

            function updateSigningMode() {
                var digital = $('#role option:selected').data('digital') === 1;
                $('#signature-pad, #sign-digital-button, #terms-confirmation').toggle(digital);
            }
            $('#role').on('change', updateSigningMode);
            updateSigningMode();

            $('#sign-digital-button').on("click", function () {
                $('#sign-method').val('digital');
                $('#signature_output').val(signaturePad.toDataURL('image/png'));
            });

            $('#sign-form').on('submit', function () {
                if ($('#sign-method').val() === 'digital' && signaturePad.isEmpty()) {
                    alert(@json(trans('documents.general.signature_required')));
                    return false;
                }
                if ($('#sign-method').val() === 'digital' && $('#agree_terms').length && !$('#agree_terms').is(':checked')) {
                    alert(@json(trans('documents.general.agree_terms')));
                    $('#agree_terms').focus();
                    return false;
                }
                if ($('#sign-method').val() === 'digital') {
                    $('#signature_output').val(signaturePad.toDataURL('image/png'));
                }
            });

            $('#sign-printed-button').on("click", function () {
                $('#sign-method').val('printed');
                $('#signature_output').val('');
            });
        });
    </script>
@stop
