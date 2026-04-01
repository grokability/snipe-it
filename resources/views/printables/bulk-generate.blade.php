<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $printable->name }} — {{ trans_choice('button.generate_printable', $assets->count()) }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 0;
            background: #fff;
            color: #333;
        }

        .printable-toolbar {
            background: #f4f4f4;
            border-bottom: 1px solid #ddd;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .printable-toolbar h1 {
            font-size: 18px;
            margin: 0;
        }

        .printable-toolbar .meta {
            font-size: 12px;
            color: #666;
        }

        .printable-toolbar .actions a,
        .printable-toolbar .actions button {
            margin-left: 8px;
        }

        .printable-asset-page {
            padding: 20px;
            max-width: 960px;
            margin: 0 auto;
        }

        @media print {
            .printable-toolbar {
                display: none !important;
            }

            .printable-asset-page {
                padding: 0;
                max-width: none;
                page-break-after: always;
            }

            .printable-asset-page:last-child {
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>
    <div class="printable-toolbar hidden-print">
        <div>
            <h1>{{ $printable->name }}</h1>
            <span class="meta">{{ trans_choice('button.generate_printable', $assets->count()) }} ({{ $assets->count() }})</span>
        </div>
        <div class="actions">
            <a href="{{ route('hardware.index') }}" class="btn btn-default btn-sm">
                &larr; {{ trans('general.back') }}
            </a>
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="fas fa-print fa-fw"></i>
                {{ trans('admin/printables/general.print') }}
            </button>
        </div>
    </div>

    {!! $rendered !!}
</body>
</html>
