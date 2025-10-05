<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order {{ $purchaseOrder->po_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px 40px;
        }
        
        .header {
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-info {
            float: left;
            width: 50%;
        }
        
        .po-info {
            float: right;
            width: 45%;
            text-align: right;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 10px 0 5px 0;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }
        
        .info-table {
            width: 100%;
            margin-bottom: 10px;
        }
        
        .info-table td {
            padding: 3px 8px;
            vertical-align: top;
        }
        
        .info-table .label {
            font-weight: bold;
            width: 150px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 4px 6px;
            text-align: left;
            font-size: 12px;
        }
        
        .items-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .items-table .text-center {
            text-align: center;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        .total-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-draft { background-color: #6c757d; color: white; }
        .status-pending { background-color: #ffc107; color: #212529; }
        .status-approved { background-color: #17a2b8; color: white; }
        .status-ordered { background-color: #007bff; color: white; }
        .status-received { background-color: #28a745; color: white; }
        .status-cancelled { background-color: #dc3545; color: white; }
        
        .notes {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 6px 10px;
            margin: 5px 0;
            font-size: 12px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }
        
        @page {
            margin: 20mm;
        }
    </style>
</head>
<body>
    <!-- Company Logo -->
    <div style="text-align: left; margin-bottom: 10px;">
        @php
            $logoPath = public_path('uploads/logo.jpg');
            $logoExists = file_exists($logoPath);
            $logoBase64 = $logoExists ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath)) : '';
        @endphp
        
        @if($logoExists)
            <img src="{{ $logoBase64 }}" 
                 alt="Company Logo" 
                 style="max-width: 100%; height: auto; max-height: 120px;">
        @endif
    </div>
    
    <!-- Header -->
    <div class="header clearfix">
        <div class="company-info">
            <h1 style="margin: 0; font-size: 24px;">PURCHASE ORDER</h1>
        </div>
        
        <div class="po-info">
            <h2 style="margin: 0; font-size: 20px;">{{ $purchaseOrder->po_number }}</h2>
            <p style="margin: 5px 0 0 0;">
                <strong>Date:</strong> {{ now()->format('F j, Y') }}
            </p>
        </div>
    </div>

    <!-- Purchase Order Details -->
    <div class="section-title">Purchase Order Details</div>
    <table class="info-table">
        <tr>
            <td class="label">PO Number:</td>
            <td>{{ $purchaseOrder->po_number }}</td>
            <td class="label">Order Date:</td>
            <td>{{ $purchaseOrder->order_date ? $purchaseOrder->order_date->format('F j, Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Expected Delivery:</td>
            <td>{{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('F j, Y') : 'N/A' }}</td>
            <td class="label">Created By:</td>
            <td>{{ $purchaseOrder->creator->first_name ?? 'N/A' }} {{ $purchaseOrder->creator->last_name ?? '' }}</td>
        </tr>
    </table>

    <!-- Supplier Information -->
    @if($purchaseOrder->supplier)
        <div class="section-title">Supplier Information</div>
        <table class="info-table">
            <tr>
                <td class="label" style="width: 15%;">Supplier:</td>
                <td style="width: 35%;">{{ $purchaseOrder->supplier->name }}</td>
                <td class="label" style="width: 15%;">City:</td>
                <td style="width: 35%;">{{ $purchaseOrder->supplier->city ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Address:</td>
                <td>{{ $purchaseOrder->supplier->address ?? '' }}</td>
                <td class="label">Country:</td>
                <td>{{ $purchaseOrder->supplier->country ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Zipcode:</td>
                <td>{{ $purchaseOrder->supplier->zip ?? '' }}</td>
                <td></td>
                <td></td>
            </tr>
        </table>
    @endif

    <!-- Items -->
    <div class="section-title">Items ({{ $purchaseOrder->items->count() }})</div>
    @if($purchaseOrder->items->count() > 0)
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 35%;">Model Name</th>
                    <th style="width: 10%;" class="text-center">Qty</th>
                    <th style="width: 15%;" class="text-right">Unit Price</th>
                    <th style="width: 15%;" class="text-right">Total Price</th>
                    <th style="width: 20%;">Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseOrder->items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $item->model_name }}</strong>
                            @if($item->assetModel && $item->assetModel->model_number)
                                <br><small style="color: #666;">Model: {{ $item->assetModel->model_number }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">{{ $snipeSettings->default_currency }} {{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">{{ $snipeSettings->default_currency }} {{ number_format($item->total_price, 2) }}</td>
                        <td>{{ $item->notes ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="4" class="text-right"><strong>TOTAL:</strong></td>
                    <td class="text-right"><strong>{{ $snipeSettings->default_currency }} {{ number_format($purchaseOrder->total_amount, 2) }}</strong></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    @else
        <p>No items found.</p>
    @endif

    <!-- Notes -->
    @if($purchaseOrder->notes)
        <div class="notes">
            <strong>Notes:</strong> {{ $purchaseOrder->notes }}
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>
            This purchase order was generated on {{ now()->format('F j, Y \a\t g:i A') }} by {{ Auth::user()->first_name ?? 'System' }} {{ Auth::user()->last_name ?? '' }}.
        </p>
    </div>

    <!-- Signature Fields -->
    <div style="margin-top: 50px; page-break-inside: avoid;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 50%; padding: 20px; vertical-align: top;">
                    <div style="border-bottom: 2px solid #000; height: 60px; margin-bottom: 10px;"></div>
                    <p style="margin: 0; font-weight: bold; text-align: center;">Client</p>
                    <p style="margin: 5px 0 0 0; text-align: center; font-size: 12px;">Signature & Date</p>
                </td>
                <td style="width: 50%; padding: 20px; vertical-align: top;">
                    <div style="border-bottom: 2px solid #000; height: 60px; margin-bottom: 10px;"></div>
                    <p style="margin: 0; font-weight: bold; text-align: center;">Management</p>
                    <p style="margin: 5px 0 0 0; text-align: center; font-size: 12px;">Signature & Date</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
