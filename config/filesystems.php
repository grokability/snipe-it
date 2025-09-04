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
            'root'   => env('PUBLIC_AWS_BUCKET_ROOT'),
            'visibility' => 'public'
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
            'root'   => env('PRIVATE_AWS_BUCKET_ROOT'),
            'visibility' => 'private'
        ],

        'rackspace' => [
            'driver'    => 'rackspace',
            'username'  => env('RACKSPACE_USERNAME'),
            'key'       => env('RACKSPACE_KEY'),
            'container' => env('RACKSPACE_CONTAINER'),
            'endpoint'  => 'https://identity.api.rackspacecloud.com/v2.0/',
            'region'    => env('RACKSPACE_REGION'),
            'url_type'  => env('RACKSPACE_URL_TYPE'),
        ],

        'backup' => [
            'driver' => env('PRIVATE_FILESYSTEM_DISK', 'local'),
            'root' => storage_path('app'),
        ],

    ],


];

// copy the selected PUBLIC_FILESYSTEM_DISK's configuration to the 'public' key for easy use
// (by default, the PUBLIC_FILESYSTEM DISK is 'local_public', in the public/uploads directory)
$config['disks']['public'] = $config['disks'][env('PUBLIC_FILESYSTEM_DISK','local_public')];

// This is used to determine which files to accept, and also to populate the language strings for the upload-file blade
$config['allowed_upload_extensions_array']  = [
    'avif',
    'doc',
    'doc',
    'docx',
    'docx',
    'gif',
    'ico',
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

return $config;