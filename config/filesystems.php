<?php

$config = [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('PRIVATE_FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path(),
        ],

        // This applies the LOCAL public only, not S3/FTP/etc
        'local_public' => [
            'driver' => 'local',
            'root' => public_path('uploads'),
            'url' => env('APP_URL').'/uploads',
            'visibility' => 'public',
        ],

        's3_public' => [
            'driver' => 's3',
            'key' => env('PUBLIC_AWS_ACCESS_KEY_ID'),
            'secret' => env('PUBLIC_AWS_SECRET_ACCESS_KEY'),
            'region' => env('PUBLIC_AWS_DEFAULT_REGION'),
            'bucket' => env('PUBLIC_AWS_BUCKET'),
            'url' => env('PUBLIC_AWS_URL'),
            'endpoint' => env('PUBLIC_AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('PUBLIC_AWS_PATH_STYLE'),
            'root' => env('PUBLIC_AWS_BUCKET_ROOT'),
            // No 'visibility' key: Admins who want public reads should grant them
            // via a bucket policy and set PUBLIC_AWS_URL to the public endpoint. See #19670.
        ],

        's3_private' => [
            // This bucket (if different than the 's3' bucket above) can be
            // configured within AWS to *never* permit public documents
            // For security reasons, its best to use separate buckets for
            // public and private documents in S3
            'driver' => 's3',
            'key' => env('PRIVATE_AWS_ACCESS_KEY_ID'),
            'secret' => env('PRIVATE_AWS_SECRET_ACCESS_KEY'),
            'region' => env('PRIVATE_AWS_DEFAULT_REGION'),
            'bucket' => env('PRIVATE_AWS_BUCKET'),
            'url' => env('PRIVATE_AWS_URL'),
            'endpoint' => env('PRIVATE_AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('PRIVATE_AWS_PATH_STYLE'),
            'root' => env('PRIVATE_AWS_BUCKET_ROOT'),
            // No 'visibility' key: sending an ACL header (even
            // ACL: private) is rejected on buckets with Object
            // Ownership = Bucket owner enforced, which has been the
            // default for buckets created since April 2023. Buckets
            // configured that way are already private by default via
            // the bucket policy, so this ACL was redundant. See #19670.
        ],

        'digitalocean_private' => [
            'driver' => 's3',
            'key' => env('DIGITALOCEAN_SPACES_KEY'),
            'secret' => env('DIGITALOCEAN_SPACES_SECRET'),
            'endpoint' => env('DIGITALOCEAN_SPACES_ENDPOINT'),
            'region' => env('DIGITALOCEAN_SPACES_REGION'),
            'bucket' => env('DIGITALOCEAN_SPACES_BUCKET'),
            'visibility' => 'private'
        ],

        'digitalocean' => [
            'driver' => 's3',
            'key' => env('DIGITALOCEAN_SPACES_KEY'),
            'secret' => env('DIGITALOCEAN_SPACES_SECRET'),
            'endpoint' => env('DIGITALOCEAN_SPACES_ENDPOINT'),
            'region' => env('DIGITALOCEAN_SPACES_REGION'),
            'bucket' => env('DIGITALOCEAN_SPACES_BUCKET'),
            'visibility' => 'public'
        ],


        'rackspace' => [
            'driver' => 'rackspace',
            'username' => env('RACKSPACE_USERNAME'),
            'key' => env('RACKSPACE_KEY'),
            'container' => env('RACKSPACE_CONTAINER'),
            'endpoint' => 'https://identity.api.rackspacecloud.com/v2.0/',
            'region' => env('RACKSPACE_REGION'),
            'url_type' => env('RACKSPACE_URL_TYPE'),
        ],

        'backup' => [
            'driver' => env('BACKUP_FILESYSTEM_DRIVER', 'local'),
            'key' => env('PRIVATE_AWS_ACCESS_KEY_ID'),
            'secret' => env('PRIVATE_AWS_SECRET_ACCESS_KEY'),
            'region' => env('PRIVATE_AWS_DEFAULT_REGION'),
            'bucket' => env('PRIVATE_AWS_BUCKET'),
            // Root defaults differ by driver so the same disk config
            // does the right thing on both. On local, backups live
            // under storage_path("app")/backups (matches the pre-S3
            // shape and the spatie backup name). On s3, the root
            // becomes an S3 key prefix, and defaulting it to a local
            // filesystem path (storage_path("app") = "/Users/..." or
            // "/var/www/...") puts every backup behind a leading-slash
            // prefix that Flysystem's S3 adapter can't list back
            // (upload preserves the leading /, listContents strips
            // it, so the write/read round-trip breaks). Empty string
            // = bucket root, which is what admins actually want.
            // Override in either direction via BACKUP_FILESYSTEM_ROOT.
            'root' => env('BACKUP_FILESYSTEM_ROOT', env('BACKUP_FILESYSTEM_DRIVER', 'local') === 's3' ? '' : storage_path('app')),
            // No 'visibility' key: same reasoning as s3_private above.
            // On the default local driver this key does nothing anyway.
            // On the s3 driver it would send ACL: private, which buckets
            // with Object Ownership = Bucket owner enforced reject with
            // AccessControlListNotSupported. See #19670.
        ],

    ],

];

// copy the selected PUBLIC_FILESYSTEM_DISK's configuration to the 'public' key for easy use
// (by default, the PUBLIC_FILESYSTEM DISK is 'local_public', in the public/uploads directory)
$config['disks']['public'] = $config['disks'][env('PUBLIC_FILESYSTEM_DISK', 'local_public')];

// When PUBLIC_S3_PROXY is enabled, all "public" uploads are served through the application
// instead of being accessed directly from S3. This allows using a single private S3 bucket
// for all storage, with the app proxying requests for public files (images, logos, avatars).
// No 'visibility' key: the proxy works because the URL points at the app's
// /storage-proxy route, not because of any per-object ACL, so we drop the
// visibility that would otherwise send ACL: private and be rejected by
// buckets configured with Object Ownership = Bucket owner enforced.
if (env('PUBLIC_S3_PROXY', false)) {
    $config['disks']['public']['url'] = env('APP_URL').'/storage-proxy';
}

// This is used to determine which files to accept, and also to populate the language strings for the upload-file blade
$config['allowed_upload_extensions_array'] = [
    'avif',
    'csv',
    'doc',
    'docx',
    'gif',
    'ico',
    'jfif',
    'jpeg',
    'jpg',
    'json',
    'key',
    'lic',
    'mov',
    'mp3',
    'mp4',
    'odp',
    'ods',
    'odt',
    'ogg',
    'pdf',
    'png',
    'rar',
    'rtf',
    'svg',
    'txt',
    'wav',
    'webm',
    'webp',
    'xls',
    'xlsx',
    'xml',
    'zip',
];

// https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/MIME_types/Common_types
$config['allowed_upload_mimetypes_array'] = [
    'application/json',
    'application/msword',
    'application/pdf',
    // text/csv is the RFC 4180 mime. Some browsers report CSVs as
    // application/csv or application/vnd.ms-excel instead depending on
    // OS registration. Accepting all three covers real-world uploads
    // without users hitting mysterious "wrong type" rejections.
    'application/csv',
    'text/csv',
    'application/vnd.ms-excel',
    'application/vnd.oasis.opendocument.presentation',
    'application/vnd.oasis.opendocument.spreadsheet',
    'application/vnd.oasis.opendocument.text',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/x-rar-compressed',
    'application/zip',
    'audio/*',
    'image/*',
    'text/plain',
    'text/rtf',
    'text/xml',
    'video/*',
];

$config['allowed_upload_mimetypes'] = implode(',', $config['allowed_upload_mimetypes_array']);
$config['allowed_upload_extensions_for_validator'] = implode(',', $config['allowed_upload_extensions_array']);
$config['allowed_upload_extensions'] = '.'.implode(', .', $config['allowed_upload_extensions_array']);

// Strict subset of the upload allowlist that is safe to return with
// Content-Disposition: inline. The extension gates entry; the server-detected
// MIME must also match one of the values below before StorageHelper::allowSafeInline
// hands the response back inline. Anything else falls through to an attachment
// response so an uploaded XML/HTML/XSLT can't be rendered as active content in the
// Snipe-IT origin. Keep this list narrower than allowed_upload_extensions_array on
// purpose: we accept broader file types for storage than we're willing to render.
$config['allowed_inline_display'] = [
    'avif' => ['image/avif'],
    'gif' => ['image/gif'],
    'jpg' => ['image/jpeg'],
    'jpeg' => ['image/jpeg'],
    'mov' => ['video/quicktime'],
    'mp3' => ['audio/mpeg', 'audio/mp3'],
    'mp4' => ['video/mp4'],
    'ogg' => ['audio/ogg', 'video/ogg', 'application/ogg'],
    'pdf' => ['application/pdf'],
    'png' => ['image/png'],
    'svg' => ['image/svg+xml'],
    'wav' => ['audio/wav', 'audio/x-wav', 'audio/wave', 'audio/vnd.wave'],
    'webm' => ['video/webm', 'audio/webm'],
    'webp' => ['image/webp'],
];

return $config;
