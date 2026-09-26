@php
    $layout = app(\App\Services\Documents\DocumentLayout::class);
    $header = $layout->headerConfig($document);
    $footer = $layout->footerConfig($document);
    $snapshot = $document->templateVersion->snapshot;
    $font = app(\App\Services\Documents\DocumentFonts::class)->webFont($header);
    $family = ['dejavusans' => 'DejaVu Sans, sans-serif', 'dejavuserif' => 'DejaVu Serif, serif', 'dejavusansmono' => 'DejaVu Sans Mono, monospace', 'custom' => 'DocumentCustom, sans-serif'][$header['font_family']] ?? 'sans-serif';
    $rtl = in_array(substr($snapshot['language'] ?? 'en', 0, 2), ['fa', 'ar', 'he']);
@endphp
<!DOCTYPE html>
<html lang="{{ $snapshot['language'] ?? 'en' }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $document->number }}</title>
    <style nonce="{{ csrf_token() }}">
        @if ($font)
        @font-face { font-family: DocumentCustom; src: url(data:font/ttf;base64,{{ $font }}) format('truetype'); }
        @endif
        * { box-sizing: border-box; }
        body { margin: 0; background: #e9ecef; color: #111; font-family: {!! $family !!}; font-size: {{ (float) $header['font_size'] }}pt; line-height: {{ (float) $header['line_height'] }}; }
        .print-tools { padding: 14px; background: #fff; font: 14px sans-serif; }
        .print-tools button, .print-tools a { display: inline-block; margin: 4px; padding: 8px 12px; }
        .sheet { max-width: {{ ($snapshot['orientation'] ?? 'P') === 'L' ? 297 : 210 }}mm; margin: 20px auto; background: white; padding: 15mm; }
        .document-header { font-size: {{ (float) $header['header_size'] }}pt; margin-bottom: 12px; }
        .document-body { text-align: {{ $header['alignment'] }}; overflow-wrap: anywhere; }
        .document-footer { font-size: {{ (float) $footer['font_size'] }}pt; text-align: {{ $footer['alignment'] }}; margin-top: 12px; }
        table { width: 100%; border-collapse: collapse; } td, th { vertical-align: top; padding: 4px; }
        .print-frame > thead > tr > td, .print-frame > tbody > tr > td, .print-frame > tfoot > tr > td { padding: 0; }
        table[border] { table-layout: fixed; }
        table[border] td, table[border] th { border: 1px solid #777; }
        .signature-block { break-inside: avoid; margin-top: 12px; }
        h1, h2, h3 { break-after: avoid; } pre { white-space: pre-wrap; } img { max-width: 100%; }
        @page {
            size: {{ $snapshot['page_size'] ?? 'A4' }} {{ ($snapshot['orientation'] ?? 'P') === 'L' ? 'landscape' : 'portrait' }};
            margin: 8mm {{ (float) $header['margin_right'] }}mm 8mm {{ (float) $header['margin_left'] }}mm;
            @if ($footer['show_footer'] && $footer['show_pages'])
            @bottom-right { content: "{{ trans('documents.custom.page') }} " counter(page) " / " counter(pages); font-size: {{ (float) $footer['font_size'] }}pt; }
            @endif
        }
        @media print {
            body { background: white; }
            .print-tools { display: none; }
            .sheet { padding: 0; margin: 0; max-width: none; }
            .document-header { min-height: {{ (float) $header['margin_top'] - 8 }}mm; margin: 0; }
            .document-footer { min-height: {{ (float) $header['margin_bottom'] - 8 }}mm; margin: 0; padding-top: 4mm; }
            .print-frame > thead { display: table-header-group; }
            .print-frame > tfoot { display: table-footer-group; break-inside: avoid; }
            .footer-text { position: fixed; bottom: 0; left: 0; right: 0; }
        }
        @media screen and (max-width: 600px) { .sheet { padding: 16px; margin: 8px; overflow-x: auto; } }
    </style>
</head>
<body>
    <nav class="print-tools">
        <button id="print-document" type="button">{{ trans('documents.custom.web_print') }}</button>
        @if (!$isPreview)
            <a href="{{ route('documents.pdf', $document) }}">{{ trans('documents.general.download_pdf') }}</a>
        @endif
        <span>{{ trans($isPreview ? 'documents.custom.sample_preview' : 'documents.custom.browser_print_help') }}</span>
    </nav>
    <main class="sheet">
        <table class="print-frame"><thead><tr><td>
            <header class="document-header">@if ($header['show_header']){!! $layout->header($document) !!}@endif</header>
        </td></tr></thead><tfoot><tr><td>
            <footer class="document-footer"><div class="footer-text">@if ($footer['show_footer']){!! $layout->text($footer['text'], $document) !!}@endif</div></footer>
        </td></tr></tfoot><tbody><tr><td>
            <article class="document-body">{!! $layout->body($document) !!}</article>
        </td></tr></tbody></table>
    </main>
    <script nonce="{{ csrf_token() }}">document.getElementById('print-document').addEventListener('click', async function () { await document.fonts.ready; window.print(); });</script>
</body>
</html>
