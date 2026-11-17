<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use App\Http\Traits\ConvertsBase64ToFiles;
use App\Rules\AllowedUploadExtension;
use enshrined\svgSanitize\Sanitizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class UploadFileRequest extends Request
{
    use ConvertsBase64ToFiles;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // AllowedUploadExtension replaces Laravel's `mimes:` rule because
        // `mimes:` content-sniffs, reverse-maps the detected MIME to a
        // single extension, and rejects anything the guesser can't map,
        // even when the client extension is on the allowlist. That was
        // rejecting legitimate uploads (empty .txt, INI-shaped text,
        // Windows-sniffed .csv reporting octet-stream) with a generic
        // "check the form below" error. See issues #12460 and #10387.
        return [
            'file.*' => [
                'bail',
                'required',
                'file',
                new AllowedUploadExtension(config('filesystems.allowed_upload_extensions_array')),
                'max:'.Helper::file_upload_max_size(),
            ],
        ];
    }

    /**
     * Alias the legacy `image` audit field to `file[0]` so the single
     * `file.*` rule validates it. Works because prepareForValidation
     * runs before Laravel's first allFiles() call, so the FileBag
     * mutation lands in convertedFiles on first access.
     */
    protected function prepareForValidation(): void
    {
        if ($this->files->has('image') && ! $this->files->has('file')) {
            $this->files->set('file', [$this->files->get('image')]);
            $this->files->remove('image');
        }
    }

    /**
     * Sanitizes (if needed) and Saves a file to the appropriate location
     * Returns the 'short' (storage-relative) filename.
     */
    public function handleFile(string $dirname, string $name_prefix, $file): string
    {
        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            throw new RuntimeException('invalid upload');
        }

        $this->ensureExtensionAllowed($file);

        $extension = strtolower((string) $file->getClientOriginalExtension());
        $file_name = $name_prefix.'-'.str_random(8).'-'.str_slug(basename($file->getClientOriginalName(), '.'.$extension)).'.'.$extension;

        // Sanitize when the stored extension is .svg OR the content
        // sniffs as SVG. Gating on getMimeType() alone used to let an
        // SVG prefixed with an XML comment through, because finfo
        // sniffs that content as text/plain while the file still
        // lands on disk as .svg and is served inline as
        // image/svg+xml. Keeping both signals catches the known
        // bypass and any real SVG bytes uploaded under a non-.svg
        // extension that a future serve path might render.
        if ($extension === 'svg' || $file->getMimeType() === 'image/svg+xml') {
            $uploaded_file = $this->handleSVG($file);
        } else {
            $uploaded_file = file_get_contents($file);
        }

        try {
            Storage::put($dirname.$file_name, $uploaded_file);
        } catch (\Exception $e) {
            Log::debug($e);
        }

        return $file_name;
    }

    /**
     * Run the AllowedUploadExtension rule against the file.
     */
    private function ensureExtensionAllowed(UploadedFile $file): void
    {
        $rule = new AllowedUploadExtension(config('filesystems.allowed_upload_extensions_array'));
        $failed = null;
        $rule->validate('file', $file, function ($message) use (&$failed) {
            $failed = $message;
        });

        if ($failed !== null) {
            throw new RuntimeException('rejected upload: '.$failed);
        }
    }

    public function handleSVG($file)
    {
        $sanitizer = new Sanitizer;
        $dirtySVG = file_get_contents($file->getRealPath());

        return $sanitizer->sanitize($dirtySVG);
    }

    /**
     * Get the validation error messages that apply to the request, but
     * replace the attribute name with the name of the file that was attempted and failed
     * to make it clearer to the user which file is the bad one.
     */
    public function attributes(): array
    {
        $attributes = [];

        if (($this->file) && (is_array($this->file))) {

            for ($i = 0; $i < count($this->file); $i++) {

                try {

                    if ($this->file[$i]) {
                        $attributes['file.'.$i] = $this->file[$i]->getClientOriginalName();
                    }

                } catch (\Exception $e) {
                    $attributes['file.'.$i] = 'Invalid file';
                }

            }
        }

        return $attributes;

    }
}
