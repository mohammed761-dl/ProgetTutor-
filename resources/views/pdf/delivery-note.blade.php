<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delivery Note - {{ $deliveryNote->dnp_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .company-info {
            margin-bottom: 20px;
        }
        .delivery-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .customer-info, .delivery-info {
            width: 45%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
            border: 1px solid #ddd;
            padding: 20px;
            text-align: center;
            height: 80px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>DELIVERY NOTE</h1>
        <h2>{{ $deliveryNote->dnp_number }}</h2>
    </div>

    <div class="company-info">
        <h3>Senselix ERP</h3>
        <p>Your Company Address<br>
        City, State, ZIP<br>
        Phone: (000) 000-0000<br>
        Email: info@senselix.com</p>
    </div>

    <div class="delivery-details">
        <div class="customer-info">
            <h4>Deliver To:</h4>
            <p><strong>{{ $deliveryNote->aro?->purchaseOrder?->customer?->company_name ?? 'Unknown Customer' }}</strong><br>
            {{ $deliveryNote->aro?->purchaseOrder?->customer?->address ?? 'N/A' }}<br>
            {{ $deliveryNote->aro?->purchaseOrder?->customer?->city ?? 'N/A' }}, {{ $deliveryNote->aro?->purchaseOrder?->customer?->postal_code ?? 'N/A' }}<br>
            {{ $deliveryNote->aro?->purchaseOrder?->customer?->country ?? 'N/A' }}<br>
            Email: {{ $deliveryNote->aro?->purchaseOrder?->customer?->email ?? 'N/A' }}<br>
            Phone: {{ $deliveryNote->aro?->purchaseOrder?->customer?->phone ?? 'N/A' }}</p>
        </div>
        
        <div class="delivery-info">
            <h4>Delivery Details:</h4>
            <p><strong>Delivery Note #:</strong> {{ $deliveryNote->dnp_number }}<br>
            <strong>ARO #:</strong> {{ $deliveryNote->aro?->aro_number ?? 'N/A' }}<br>
            <strong>PO #:</strong> {{ $deliveryNote->aro?->purchaseOrder?->po_number ?? 'N/A' }}<br>
            <strong>Planned Delivery:</strong> {{ $deliveryNote->planned_delivery_date ? \Carbon\Carbon::parse($deliveryNote->planned_delivery_date)->format('M d, Y') : 'N/A' }}<br>
            <strong>Actual Delivery:</strong> {{ $deliveryNote->actual_delivery_date ? \Carbon\Carbon::parse($deliveryNote->actual_delivery_date)->format('M d, Y') : 'Pending' }}<br>
            <strong>Status:</strong> {{ $deliveryNote->status ?? $deliveryNote->delivery_status }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product Code</th>
                <th>Description</th>
                <th>Quantity Delivered</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @if($deliveryNote->products && $deliveryNote->products->count() > 0)
                @foreach($deliveryNote->products as $product)
                <tr>
                    <td>{{ $product->product_code }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->quantity_shipped }}</td>
                    <td>€{{ number_format($product->unit_price ?? 0, 2) }}</td>
                    <td>€{{ number_format(($product->quantity_shipped * ($product->unit_price ?? 0)), 2) }}</td>
                </tr>
                @endforeach
            @elseif($deliveryNote->aro && $deliveryNote->aro->aroProducts)
                @foreach($deliveryNote->aro->aroProducts as $aroProduct)
                <tr>
                    <td>{{ $aroProduct->quoteProduct?->product?->product_code ?? 'N/A' }}</td>
                    <td>{{ $aroProduct->quoteProduct?->product?->name ?? 'N/A' }}</td>
                    <td>{{ $aroProduct->quantity_received }}</td>
                    <td>€{{ number_format($aroProduct->quoteProduct?->unit_price ?? 0, 2) }}</td>
                    <td>€{{ number_format(($aroProduct->quantity_received * ($aroProduct->quoteProduct?->unit_price ?? 0)), 2) }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5" class="text-center">No products available</td>
                </tr>
            @endif
        </tbody>
    </table>

    @if($deliveryNote->notes)
    <div style="margin-top: 30px;">
        <h4>Delivery Notes:</h4>
        <p>{{ $deliveryNote->notes }}</p>
    </div>
    @endif

    <div class="signature-section">
        <div class="signature-box">
            <h4>Delivered By:</h4>
            <div style="margin-top: 40px;">
                <div style="border-top: 1px solid #000; display: inline-block; width: 200px;"></div>
                <p style="font-size: 10px; margin: 5px 0;">Signature & Date</p>
            </div>
        </div>
        
        <div class="signature-box">
            <h4>Received By:</h4>
            <div style="margin-top: 40px;">
                <div style="border-top: 1px solid #000; display: inline-block; width: 200px;"></div>
                <p style="font-size: 10px; margin: 5px 0;">Signature & Date</p>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Generated on {{ \Carbon\Carbon::now()->format('M d, Y H:i:s') }} by {{ $deliveryNote->createdBy->name ?? 'System' }}</p>
    </div>
</body>
</html>
