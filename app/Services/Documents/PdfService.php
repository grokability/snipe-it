<?php

namespace App\Services\Documents;

use App\Models\Document;
use TCPDF;

class PdfService
{
    public function generate(Document $document): string
    {
        $layout = app(DocumentLayout::class);
        $snapshot = $document->templateVersion->snapshot;
        $header = $layout->headerConfig($document);
        $footer = $layout->footerConfig($document);
        $pdf = new class($snapshot['orientation'] ?? 'P', 'mm', $snapshot['page_size'] ?? 'A4', true, 'UTF-8', false) extends TCPDF
        {
            public string $headerHtml = '';

            public string $footerHtml = '';

            public string $documentFont = 'dejavusans';

            public array $headerOptions = [];

            public array $footerOptions = [];

            public function Header(): void
            {
                $this->SetY(8);
                $this->SetFont($this->documentFont, '', $this->headerOptions['header_size']);
                $this->writeHTML($this->headerHtml, true, false, true, false, '');
            }

            public function Footer(): void
            {
                $this->SetY(-($this->headerOptions['margin_bottom'] - 3));
                $this->SetFont($this->documentFont, '', $this->footerOptions['font_size']);
                $this->writeHTML($this->footerHtml, true, false, true, false, strtoupper(substr($this->footerOptions['alignment'], 0, 1)));
                if ($this->footerOptions['show_pages']) {
                    $this->Cell(0, 5, trans('documents.custom.page').' '.$this->getAliasNumPage().' / '.$this->getAliasNbPages(), 0, false, 'R');
                }
            }
        };
        $pdf->SetCreator('Snipe-IT Document Engine');
        $pdf->SetTitle($document->number);
        $pdf->documentFont = app(DocumentFonts::class)->register($pdf, $header);
        $pdf->headerOptions = $header;
        $pdf->footerOptions = $footer;
        $pdf->headerHtml = $layout->header($document, true);
        $pdf->footerHtml = $layout->text($footer['text'], $document);
        $pdf->setPrintHeader((bool) $header['show_header']);
        $pdf->setPrintFooter((bool) $footer['show_footer']);
        $pdf->SetFont($pdf->documentFont, '', $header['font_size'], '', true);
        $pdf->setCellHeightRatio((float) $header['line_height']);
        $pdf->SetMargins($header['margin_left'], $header['margin_top'], $header['margin_right']);
        $pdf->SetAutoPageBreak(true, $header['margin_bottom']);
        $pdf->setRTL(in_array(substr((string) ($snapshot['language'] ?? 'en'), 0, 2), ['fa', 'ar', 'he'], true));
        $pdf->AddPage();
        $alignment = $header['alignment'] === 'start' ? '' : strtoupper(substr($header['alignment'], 0, 1));
        $pdf->writeHTML($layout->body($document, true), true, false, true, false, $alignment);

        return $pdf->Output($document->number.'.pdf', 'S');
    }
}
