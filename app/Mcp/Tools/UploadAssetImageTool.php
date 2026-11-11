<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Http\Requests\ImageUploadRequest;
use App\Mcp\Traits\HasMcpScope;
use App\Models\Asset;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Throwable;

/**
 * Attach an image to an asset. Accepts base64-encoded image bytes so an
 * AI client that handled a dragged-in image from the user can forward it
 * through this tool.
 *
 * Reuses the HTTP ImageUploadRequest::handleImages() path exactly, so
 * the same resize, re-encode, EXIF strip, SVG sanitization, and
 * delete-old-first safety rules apply. If a future server-side image
 * sanitization fix lands, the MCP tool picks it up for free.
 *
 * Size ceiling is 5MB base64 (~3.75MB decoded) to keep the JSON payload
 * sane on the MCP transport. Larger attachments should land via the
 * HTTP API instead.
 */
#[Name('upload_asset_image')]
#[Title('Upload Asset Image')]
#[Description('Attach an image to a Snipe-IT asset. The image is accepted as base64-encoded bytes plus a MIME type. The same sanitization path as the HTTP upload endpoint is used (resize, re-encode, EXIF strip, SVG scrub). Replaces any existing image on the asset.')]
class UploadAssetImageTool extends Tool
{
    use HasMcpScope;

    private const MAX_BASE64_BYTES = 5_000_000;

    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/avif',
        'image/svg+xml',
        'image/vnd.microsoft.icon',
        'image/x-icon',
    ];

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        $request->validate([
            'asset_tag' => 'nullable|string|max:191',
            'id' => 'nullable|integer',
            'image_base64' => 'required|string|max:'.self::MAX_BASE64_BYTES,
            'mime_type' => 'required|string|in:'.implode(',', self::ALLOWED_MIME_TYPES),
            'filename' => 'nullable|string|max:191',
        ]);

        if (! $request->filled('asset_tag') && ! $request->filled('id')) {
            return Response::make(Response::error(trans('mcp.id_or_asset_tag_required')));
        }

        $asset = $request->filled('asset_tag')
            ? Asset::where('asset_tag', $request->get('asset_tag'))->first()
            : Asset::find($request->get('id'));

        if (! $asset || ! Gate::allows('update', $asset)) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.asset')])));
        }

        $decoded = base64_decode($request->get('image_base64'), true);
        if ($decoded === false || $decoded === '') {
            return Response::make(Response::error(trans('mcp.upload_image_decode_failed')));
        }

        // Write to a tempfile so we can wrap it as a Symfony UploadedFile
        // and hand it to ImageUploadRequest::handleImages, reusing the
        // sanitization + resize + save path from the HTTP upload route.
        // Register for tearDown-style cleanup on PHP shutdown so the
        // temp file does not linger past the request.
        $tempPath = tempnam(sys_get_temp_dir(), 'mcp-img-');
        if ($tempPath === false) {
            return Response::make(Response::error(trans('mcp.upload_image_tempfile_failed')));
        }
        register_shutdown_function(static function () use ($tempPath) {
            if (is_file($tempPath)) {
                @unlink($tempPath);
            }
        });

        $bytesWritten = file_put_contents($tempPath, $decoded);
        if ($bytesWritten === false || $bytesWritten === 0) {
            return Response::make(Response::error(trans('mcp.upload_image_tempfile_failed')));
        }

        $filename = $request->get('filename', 'image.'.$this->extensionForMime($request->get('mime_type')));

        try {
            $uploadedFile = new UploadedFile(
                $tempPath,
                $filename,
                $request->get('mime_type'),
                null,
                true
            );

            $imageRequest = new ImageUploadRequest;
            $imageRequest->files->set('image', $uploadedFile);

            // handleImages() returns the same instance it was handed
            // (SnipeModel on the base-class signature, Asset in
            // practice). Ignoring the return keeps $asset narrowed to
            // Asset so the downstream property access stays typed.
            $imageRequest->handleImages($asset);

            if (! $asset->save()) {
                return Response::make(Response::error(trans('mcp.upload_image_save_failed', [
                    'error' => $asset->getErrors()->first(),
                ])));
            }
        } catch (Throwable $e) {
            return Response::make(Response::error(trans('mcp.upload_image_save_failed', [
                'error' => $e->getMessage(),
            ])));
        }

        return Response::make(
            Response::text(trans('mcp.upload_image_success', ['asset_tag' => $asset->asset_tag]))
        )->withStructuredContent([
            'success' => true,
            'message' => trans('mcp.upload_image_success', ['asset_tag' => $asset->asset_tag]),
            'asset_tag' => $asset->asset_tag,
            'image' => $asset->image,
            'image_url' => $asset->image ? url('uploads/assets/'.$asset->image) : null,
        ]);
    }

    private function extensionForMime(string $mime): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/avif' => 'avif',
            'image/svg+xml' => 'svg',
            'image/vnd.microsoft.icon', 'image/x-icon' => 'ico',
            default => 'bin',
        };
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'asset_tag' => $schema->string()->description('Asset tag of the asset to attach the image to. One of asset_tag or id is required.'),
            'id' => $schema->number()->description('Numeric asset ID. One of asset_tag or id is required.'),
            'image_base64' => $schema->string()->description('Base64-encoded image bytes. Max ~5MB base64 (~3.75MB decoded). Larger files should be uploaded via the HTTP API instead.')->required(),
            'mime_type' => $schema->string()->description('MIME type of the uploaded image. Allowed: image/jpeg, image/png, image/gif, image/webp, image/avif, image/svg+xml, image/vnd.microsoft.icon, image/x-icon.')->required(),
            'filename' => $schema->string()->description('Optional original filename. The stored file is renamed automatically; this is mainly for mime detection help.'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the image was attached')->required(),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'asset_tag' => $schema->string()->description('Asset tag of the updated asset'),
            'image' => $schema->string()->description('Stored filename of the newly attached image'),
            'image_url' => $schema->string()->description('Public URL to the stored image'),
        ];
    }
}
