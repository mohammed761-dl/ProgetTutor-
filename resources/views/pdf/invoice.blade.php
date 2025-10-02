<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
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
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .customer-info, .invoice-info {
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
        .totals {
            margin-top: 20px;
            text-align: right;
        }
        .totals table {
            width: 300px;
            margin-left: auto;
        }
        .payment-info {
            margin-top: 30px;
            background-color: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
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
        <h1>INVOICE</h1>
        <h2>{{ $invoice->invoice_number }}</h2>
    </div>

    <div class="company-info">
        <h3>Senselix ERP</h3>
        <p>Your Company Address<br>
        City, State, ZIP<br>
        Phone: (000) 000-0000<br>
        Email: info@senselix.com</p>
    </div>

    <div class="invoice-details">
        <div class="customer-info">
            <h4>Bill To:</h4>
            @if($invoice->quote)
                <p><strong>{{ $invoice->quote->customer->company_name }}</strong><br>
                {{ $invoice->quote->customer->address }}<br>
                {{ $invoice->quote->customer->city }}, {{ $invoice->quote->customer->postal_code }}<br>
                {{ $invoice->quote->customer->country }}<br>
                Email: {{ $invoice->quote->customer->email }}<br>
                Phone: {{ $invoice->quote->customer->phone }}</p>
            @elseif($invoice->deliveryNote)
                <p><strong>{{ $invoice->deliveryNote->aro->purchaseOrder->customer->company_name }}</strong><br>
                {{ $invoice->deliveryNote->aro->purchaseOrder->customer->address }}<br>
                {{ $invoice->deliveryNote->aro->purchaseOrder->customer->city }}, {{ $invoice->deliveryNote->aro->purchaseOrder->customer->postal_code }}<br>
                {{ $invoice->deliveryNote->aro->purchaseOrder->customer->country }}<br>
                Email: {{ $invoice->deliveryNote->aro->purchaseOrder->customer->email }}<br>
                Phone: {{ $invoice->deliveryNote->aro->purchaseOrder->customer->phone }}</p>
            @endif
        </div>
        
        <div class="invoice-info">
            <h4>Invoice Details:</h4>
            <p><strong>Invoice #:</strong> {{ $invoice->invoice_number }}<br>
            <strong>Issue Date:</strong> {{ $invoice->issue_date ? \Carbon\Carbon::parse($invoice->issue_date)->format('M d, Y') : 'N/A' }}<br>
            <strong>Due Date:</strong> {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') : 'N/A' }}<br>
            <strong>Status:</strong> {{ $invoice->status }}<br>
            @if($invoice->quote)
                <strong>Quote #:</strong> {{ $invoice->quote->quote_number }}<br>
            @endif
            @if($invoice->deliveryNote)
                <strong>Delivery Note #:</strong> {{ $invoice->deliveryNote->dnp_number }}<br>
            @endif
            </p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product Code</th>
                <th>Description</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @if($invoice->products && $invoice->products->count() > 0)
                @foreach($invoice->products as $product)
                <tr>
                    <td>{{ $product->product->product_code }}</td>
                    <td>{{ $product->product->name }}</td>
                    <td>{{ $product->quantity }}</td>
                    <td>€{{ number_format($product->unit_price, 2) }}</td>
                    <td>€{{ number_format($product->quantity * $product->unit_price, 2) }}</td>
                </tr>
                @endforeach
            @elseif($invoice->quote && $invoice->quote->products)
                @foreach($invoice->quote->products as $product)
                <tr>
                    <td>{{ $product->product->product_code }}</td>
                    <td>{{ $product->product->name }}</td>
                    <td>{{ $product->quantity }}</td>
                    <td>€{{ number_format($product->unit_price, 2) }}</td>
                    <td>€{{ number_format($product->quantity * $product->unit_price, 2) }}</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td><strong>Subtotal:</strong></td>
                <td>€{{ number_format($invoice->total_excl_vat ?? 0, 2) }}</td>
            </tr>
            @if($invoice->reduction > 0)
            <tr>
                <td><strong>Reduction:</strong></td>
                <td>-€{{ number_format($invoice->reduction, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td><strong>VAT:</strong></td>
                <td>€{{ number_format($invoice->vat_amount ?? 0, 2) }}</td>
            </tr>
            <tr style="border-top: 2px solid #333;">
                <td><strong>Total:</strong></td>
                <td><strong>€{{ number_format($invoice->total_incl_vat ?? 0, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="payment-info">
        <h4>Payment Information:</h4>
        <p><strong>Payment Terms:</strong> {{ $invoice->payment_terms ?? 'Net 30 days' }}<br>
        <strong>Payment Method:</strong> {{ $invoice->payment_method ?? 'Bank Transfer' }}<br>
        @if($invoice->payment_reference)
            <strong>Payment Reference:</strong> {{ $invoice->payment_reference }}<br>
        @endif
        </p>
    </div>

    @if($invoice->notes)
    <div style="margin-top: 30px;">
        <h4>Notes:</h4>
        <p>{{ $invoice->notes }}</p>
    </div>
    @endif

    <div class="footer">
        <p>Generated on {{ \Carbon\Carbon::now()->format('M d, Y H:i:s') }} by {{ $invoice->creator->name ?? 'System' }}</p>
        <p>Thank you for your business!</p>
    </div>
</body>
</html>
