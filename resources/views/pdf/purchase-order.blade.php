<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order - {{ $purchaseOrder->po_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .company-info {
            margin-bottom: 20px;
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
        }
        .total-section {
            margin-top: 20px;
            text-align: right;
        }
        .signature-section {
            margin-top: 50px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Purchase Order</h1>
        <h2>{{ $purchaseOrder->po_number }}</h2>
    </div>

    <div class="info-grid">
        <div class="company-info">
            <h3>From:</h3>
            <p><strong>{{ config('app.company_name', 'Your Company Name') }}</strong></p>
            <p>{{ config('app.company_address', 'Your Company Address') }}</p>
            <p>Email: {{ config('app.company_email', 'contact@company.com') }}</p>
            <p>Phone: {{ config('app.company_phone', '+1234567890') }}</p>
        </div>

        <div class="company-info">
            <h3>To:</h3>
            <p><strong>{{ $purchaseOrder->poCustomer->company_name }}</strong></p>
            <p>{{ $purchaseOrder->poCustomer->contact_name }}</p>
            <p>{{ $purchaseOrder->poCustomer->address }}</p>
            <p>Email: {{ $purchaseOrder->poCustomer->email }}</p>
            <p>Phone: {{ $purchaseOrder->poCustomer->phone }}</p>
            <p>VAT: {{ $purchaseOrder->poCustomer->vat_number }}</p>
        </div>
    </div>

    <div class="info-section">
        <p><strong>Date Created:</strong> {{ $purchaseOrder->created_at->format('M d, Y') }}</p>
        <p><strong>Status:</strong> {{ $purchaseOrder->status }}</p>
        @if($purchaseOrder->planned_delivery_date)
            <p><strong>Planned Delivery:</strong> {{ \Carbon\Carbon::parse($purchaseOrder->planned_delivery_date)->format('M d, Y') }}</p>
        @endif
        @if($purchaseOrder->actual_delivery_date)
            <p><strong>Actual Delivery:</strong> {{ \Carbon\Carbon::parse($purchaseOrder->actual_delivery_date)->format('M d, Y') }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->pivot->quantity }}</td>
                    <td>{{ number_format($product->pivot->unit_price, 2) }}</td>
                    <td>{{ number_format($product->pivot->unit_price * $product->pivot->quantity, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        @php
            $subtotal = $purchaseOrder->products->sum(function($product) {
                return $product->pivot->unit_price * $product->pivot->quantity;
            });
            $vat = $subtotal * 0.20; // Assuming 20% VAT
            $total = $subtotal + $vat;
        @endphp
        <p><strong>Subtotal:</strong> {{ number_format($subtotal, 2) }}</p>
        <p><strong>VAT (20%):</strong> {{ number_format($vat, 2) }}</p>
        <p><strong>Total:</strong> {{ number_format($total, 2) }}</p>
    </div>

    @if($purchaseOrder->remarks)
        <div class="info-section">
            <h3>Remarks:</h3>
            <p>{{ $purchaseOrder->remarks }}</p>
        </div>
    @endif

    <div class="signature-section">
        <div>
            <p>Created By:</p>
            <p>{{ $purchaseOrder->creator->name }}</p>
            <p>{{ now()->format('M d, Y') }}</p>
        </div>
        <div>
            <p>Customer Approval:</p>
            <p>_________________________</p>
            <p>Date: _________________</p>
        </div>
    </div>
</body>
</html>
