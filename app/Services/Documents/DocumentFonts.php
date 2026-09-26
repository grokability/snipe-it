<?php

namespace App\Services\Documents;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use TCPDF;
use TCPDF_FONTS;

class DocumentFonts
{
    public const FAMILIES = ['dejavusans' => 'DejaVu Sans', 'dejavuserif' => 'DejaVu Serif', 'dejavusansmono' => 'DejaVu Sans Mono', 'custom' => 'Custom TTF'];

    public function store(UploadedFile $file): array
    {
        $bytes = file_get_contents($file->getRealPath());
        $valid = strlen($bytes) >= 12 && substr($bytes, 0, 4) === "\x00\x01\x00\x00";
        $tables = [];
        if ($valid) {
            $count = unpack('n', substr($bytes, 4, 2))[1];
            $valid = $count > 0 && $count <= 128 && strlen($bytes) >= 12 + $count * 16;
            for ($i = 0; $valid && $i < $count; $i++) {
                $entry = substr($bytes, 12 + $i * 16, 16);
                $range = unpack('Noffset/Nlength', substr($entry, 8));
                $valid = strlen($bytes) >= $range['offset'] + $range['length'];
                $tables[] = substr($entry, 0, 4);
            }
            $valid = $valid && ! array_diff(['head', 'hhea', 'hmtx', 'maxp', 'cmap', 'name', 'loca', 'glyf'], $tables);
        }
        if (! $valid) {
            throw ValidationException::withMessages(['custom_font' => trans('documents.custom.invalid_font')]);
        }
        $directory = 'private_uploads/document-fonts/'.Str::uuid();
        $disk = Storage::disk('local');
        $disk->put($directory.'/custom.ttf', $bytes);
        try {
            $family = TCPDF_FONTS::addTTFfont($disk->path($directory.'/custom.ttf'), 'TrueTypeUnicode', '', 32, $disk->path($directory).'/');
            if (! $family) {
                throw new \RuntimeException('Invalid font');
            }
        } catch (\Throwable $exception) {
            $disk->deleteDirectory($directory);
            throw ValidationException::withMessages(['custom_font' => trans('documents.custom.invalid_font')]);
        }

        return ['directory' => $directory, 'name' => $file->getClientOriginalName()];
    }

    public function directory(array $config): ?string
    {
        $directory = $config['custom_font']['directory'] ?? '';
        if (! preg_match('~\Aprivate_uploads/document-fonts/[a-f0-9-]{36}\z~', $directory)) {
            return null;
        }

        return Storage::disk('local')->exists($directory.'/custom.php') ? $directory : null;
    }

    public function register(TCPDF $pdf, array $config): string
    {
        $family = $config['font_family'] ?? 'dejavusans';
        if ($family === 'custom' && ($directory = $this->directory($config))) {
            foreach (['', 'B', 'I', 'BI'] as $style) {
                $pdf->AddFont('custom', $style, Storage::disk('local')->path($directory.'/custom.php'));
            }

            return 'custom';
        }

        return array_key_exists($family, self::FAMILIES) && $family !== 'custom' ? $family : 'dejavusans';
    }

    public function webFont(array $config): ?string
    {
        if (($config['font_family'] ?? '') === 'custom' && ($directory = $this->directory($config))) {
            return base64_encode(Storage::disk('local')->get($directory.'/custom.ttf'));
        }

        return null;
    }
}
