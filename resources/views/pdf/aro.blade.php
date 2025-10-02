<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ARO - {{ $aro->aro_number }}</title>
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
        .aro-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .customer-info, .aro-info {
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
        <h1>ACKNOWLEDGMENT OF RECEIPT OF ORDER (ARO)</h1>
        <h2>{{ $aro->aro_number }}</h2>
    </div>

    <div class="company-info">
        <h3>Senselix ERP</h3>
        <p>Your Company Address<br>
        City, State, ZIP<br>
        Phone: (000) 000-0000<br>
        Email: info@senselix.com</p>
    </div>

    <div class="aro-details">
        <div class="customer-info">
            <h4>Customer:</h4>
            <p><strong>{{ $aro->purchaseOrder?->customer?->company_name ?? 'Unknown Customer' }}</strong><br>
            {{ $aro->purchaseOrder?->customer?->address ?? 'N/A' }}<br>
            {{ $aro->purchaseOrder?->customer?->city ?? 'N/A' }}, {{ $aro->purchaseOrder?->customer?->postal_code ?? 'N/A' }}<br>
            {{ $aro->purchaseOrder?->customer?->country ?? 'N/A' }}<br>
            Email: {{ $aro->purchaseOrder?->customer?->email ?? 'N/A' }}<br>
            Phone: {{ $aro->purchaseOrder?->customer?->phone ?? 'N/A' }}</p>
        </div>
        
        <div class="aro-info">
            <h4>ARO Details:</h4>
            <p><strong>ARO #:</strong> {{ $aro->aro_number }}<br>
            <strong>PO #:</strong> {{ $aro->purchaseOrder?->po_number ?? 'N/A' }}<br>
            <strong>Quote #:</strong> {{ $aro->purchaseOrder?->quote?->quote_number ?? 'N/A' }}<br>
            <strong>Date:</strong> {{ $aro->date_aro ? \Carbon\Carbon::parse($aro->date_aro)->format('M d, Y') : 'N/A' }}<br>
            <strong>Status:</strong> {{ $aro->status }}<br>
            <strong>Created By:</strong> {{ $aro->creator?->name ?? 'System' }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product Code</th>
                <th>Description</th>
                <th>Quantity Ordered</th>
                <th>Quantity Received</th>
                <th>Unit Price</th>
                <th>Total</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($aro->aroProducts as $aroProduct)
            <tr>
                <td>{{ $aroProduct->quoteProduct?->product?->product_code ?? 'N/A' }}</td>
                <td>{{ $aroProduct->quoteProduct?->product?->name ?? 'N/A' }}</td>
                <td>{{ $aroProduct->quoteProduct?->quantity ?? 0 }}</td>
                <td>{{ $aroProduct->quantity_received }}</td>
                <td>€{{ number_format($aroProduct->quoteProduct?->unit_price ?? 0, 2) }}</td>
                <td>€{{ number_format(($aroProduct->quantity_received * ($aroProduct->quoteProduct?->unit_price ?? 0)), 2) }}</td>
                <td>{{ $aroProduct->remarks ?? '' }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="5" class="text-right"><strong>Total Amount:</strong></td>
                <td colspan="2"><strong>€{{ number_format($totalAmount, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    @if($aro->remarks)
    <div style="margin-top: 30px;">
        <h4>Remarks:</h4>
        <p>{{ $aro->remarks }}</p>
    </div>
    @endif

    <div style="margin-top: 30px;">
        <h4>Terms and Conditions:</h4>
        <ul>
            <li>This ARO confirms receipt and acceptance of your purchase order.</li>
            <li>Delivery will be made as per the planned delivery date mentioned above.</li>
            <li>Any changes to this order must be communicated in writing.</li>
            <li>Payment terms as per original agreement.</li>
        </ul>
    </div>

    <div class="footer">
        <p>Generated on {{ \Carbon\Carbon::now()->format('M d, Y H:i:s') }} by {{ $aro->creator->name ?? 'System' }}</p>
    </div>
</body>
</html>
