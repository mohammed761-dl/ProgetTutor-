<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quote - {{ $quote['quote_number'] }}</title>
    <style>
        /**
         * PDF QUOTE TEMPLATE
         * 
         * PURPOSE: Generate PDF files for quote download
         * DATA SOURCE: QuoteController@downloadPdf method
         * CONSISTENCY: EXACT same data structure as Print.vue
         * 
         * IMPORTANT NOTES:
         * 1. NO CALCULATIONS: All values come pre-calculated from the controller
         * 2. SNAPSHOT DATA: Uses quote_customers and quote_products tables for historical accuracy
         * 3. VAT RATE: Displays the actual stored VAT rate from the database
         * 4. ARRAY ACCESS: Uses $quote['field'] syntax (not object syntax) for consistency
         */
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 15px;
            color: #333;
        }
        
        /* Header with logo and company info */
        .header {
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 3px solid #B8860B;
            padding-bottom: 15px;
        }
        
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .logo-cell {
            width: 40%;
            vertical-align: top;
        }
        
        .company-cell {
            width: 60%;
            text-align: right;
            vertical-align: top;
        }
        
        .logo-box {
            width: 100px;
            height: 60px;
            background-color: #B8860B;
            color: white;
            text-align: center;
            line-height: 60px;
            font-weight: bold;
            font-size: 14px;
            border-radius: 5px;
        }
        
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #B8860B;
            margin: 0;
        }
        
        .company-details {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
        
        /* Quote title section */
        .quote-title {
            text-align: center;
            background-color: #B8860B;
            color: white;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .quote-title h1 {
            margin: 0;
            font-size: 24px;
        }
        
        .quote-title h2 {
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        
        /* Quote details section - customer and quote info */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .details-table td {
            width: 50%;
            vertical-align: top;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        
        .info-title {
            font-weight: bold;
            color: #B8860B;
            font-size: 13px;
            margin-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 3px;
        }
        
        /* Products table - displays all quote products */
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .products-table th {
            background-color: #B8860B;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #B8860B;
        }
        
        .products-table td {
            padding: 8px;
            border: 1px solid #dee2e6;
        }
        
        .products-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        /* Financial totals section */
        .totals-container {
            text-align: right;
            margin-top: 20px;
        }
        
        .totals-table {
            width: 300px;
            margin-left: auto;
            border-collapse: collapse;
        }
        
        .totals-table td {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
        }
        
        .subtotal-row {
            background-color: #f8f9fa;
        }
        
        .total-row {
            background-color: #B8860B;
            color: white;
            font-weight: bold;
        }
        
        /* Terms and conditions section */
        .terms-section {
            margin-top: 30px;
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
        }
        
        .terms-title {
            font-weight: bold;
            color: #856404;
            margin-bottom: 10px;
        }
        
        /* Signature section */
        .signature-table {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
        }
        
        .signature-table td {
            width: 50%;
            text-align: center;
            padding: 20px;
            vertical-align: top;
        }
        
        .signature-line {
            border-bottom: 2px solid #333;
            height: 40px;
            margin: 15px 0 5px 0;
        }
        
        /* Footer section */
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <!-- 
        PDF QUOTE TEMPLATE
        ===================
        
        PURPOSE: Generate PDF files for quote download
        DATA SOURCE: QuoteController@downloadPdf method
        CONSISTENCY: EXACT same data structure as Print.vue
        
        IMPORTANT NOTES:
        1. NO CALCULATIONS: All values come pre-calculated from the controller
        2. SNAPSHOT DATA: Uses quote_customers and quote_products tables for historical accuracy
        3. VAT RATE: Displays the actual stored VAT rate from the database
        4. ARRAY ACCESS: Uses $quote['field'] syntax (not object syntax) for consistency
    -->

    <!-- Header with Logo and Company Info -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <div class="logo-box">SENSELIX</div>
                    <div style="font-size: 9px; color: #666; margin-top: 5px;">Professional ERP Solutions</div>
                </td>
                <td class="company-cell">
                    <h1 class="company-name">SENSELIX ERP</h1>
                    <div class="company-details">
                        Business Solutions & Technology<br>
                        123 Business Street, Suite 100<br>
                        City, State 12345<br>
                        Phone: +1 (555) 123-4567<br>
                        Email: info@senselix.com<br>
                        Web: www.senselix.com
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Quote Title Section -->
    <div class="quote-title">
        <h1>QUOTATION</h1>
        <h2>{{ $quote['quote_number'] }}</h2>
    </div>

    <!-- Quote Details Section - Customer and Quote Information -->
    <table class="details-table">
        <tr>
            <!-- Customer Information (BILL TO) -->
            <td>
                <div class="info-title">BILL TO</div>
                <strong>{{ $quote['customer']['company_name'] ?? 'N/A' }}</strong><br>
                {{ $quote['customer']['address'] ?? 'N/A' }}<br>
                {{ $quote['customer']['city'] ?? '' }}{{ $quote['customer']['postal_code'] ? ', ' . $quote['customer']['postal_code'] : '' }}<br>
                {{ $quote['customer']['country'] ?? '' }}<br>
                <strong>Email:</strong> {{ $quote['customer']['email'] ?? 'N/A' }}<br>
                <strong>Phone:</strong> {{ $quote['customer']['phone'] ?? 'N/A' }}
            </td>
            <!-- Quote Information -->
            <td>
                <div class="info-title">QUOTE DETAILS</div>
                <strong>Quote #:</strong> {{ $quote['quote_number'] }}<br>
                <strong>Date:</strong> {{ $quote['date_quote'] ? \Carbon\Carbon::parse($quote['date_quote'])->format('M d, Y') : 'N/A' }}<br>
                <strong>Valid Until:</strong> {{ $quote['valid_until'] ? \Carbon\Carbon::parse($quote['valid_until'])->format('M d, Y') : 'N/A' }}<br>
                <strong>Status:</strong> {{ $quote['status'] }}<br>
                <strong>Currency:</strong> {{ $quote['currency'] }}<br>
                <strong>Created By:</strong> {{ $quote['user']['name'] ?? 'System' }}
            </td>
        </tr>
    </table>

    <!-- Products Table - Displays all quote products with quantities and prices -->
    <table class="products-table">
        <thead>
            <tr>
                <th style="width: 15%;">Product Code</th>
                <th style="width: 40%;">Description</th>
                <th style="width: 10%;">Qty</th>
                <th style="width: 15%;">Unit Price</th>
                <th style="width: 20%;">Total</th>
            </tr>
        </thead>
        <tbody>
            <!-- 
                PRODUCTS LOOP
                =============
                Data Source: $products array from QuoteController@downloadPdf
                Structure: Each product has id, product_code, name, description, quantity, unit_price, total_line_price
                Note: total_line_price is pre-calculated in the controller (quantity × unit_price)
            -->
            @foreach($products as $product)
            <tr>
                <td><strong>{{ $product['product_code'] ?? 'N/A' }}</strong></td>
                <td>{{ $product['name'] ?? 'N/A' }}</td>
                <td style="text-align: center;"><strong>{{ $product['quantity'] ?? 0 }}</strong></td>
                <td style="text-align: right;">{{ $quote['currency'] }} {{ number_format($product['unit_price'] ?? 0, 2) }}</td>
                <td style="text-align: right; font-weight: bold;">{{ $quote['currency'] }} {{ number_format($product['total_line_price'] ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Financial Totals Section -->
    <div class="totals-container">
        <table class="totals-table">
            <!-- 
                FINANCIAL TOTALS
                =================
                IMPORTANT: All values come pre-calculated from the controller
                NO CALCULATIONS performed in this template
                
                Data Source: $quote array from QuoteController@downloadPdf
                - total_amount: Subtotal before reduction
                - reduction: Discount amount
                - vat_rate: Stored VAT rate (decimal: 0.10 for 10%)
                - vat: Pre-calculated VAT amount
                - total_ttc: Final total including VAT
            -->
            
            <!-- Subtotal row -->
            <tr class="subtotal-row">
                <td><strong>Subtotal:</strong></td>
                <td style="text-align: right;"><strong>{{ $quote['currency'] }} {{ number_format($quote['total_amount'] ?? 0, 2) }}</strong></td>
            </tr>
            
            <!-- Discount row (only shown if reduction > 0) -->
            @if(($quote['reduction'] ?? 0) > 0)
            <tr style="color: #dc3545;">
                <td><strong>Discount:</strong></td>
                <td style="text-align: right;"><strong>-{{ $quote['currency'] }} {{ number_format($quote['reduction'] ?? 0, 2) }}</strong></td>
            </tr>
            @endif
            
            <!-- VAT row - displays actual stored VAT rate -->
            <tr class="subtotal-row">
                <td><strong>VAT ({{ number_format(($quote['vat_rate'] ?? 0.20) * 100, 1) }}%):</strong></td>
                <td style="text-align: right;"><strong>{{ $quote['currency'] }} {{ number_format($quote['vat'] ?? 0, 2) }}</strong></td>
            </tr>
            
            <!-- Final total row -->
            <tr class="total-row">
                <td><strong>TOTAL:</strong></td>
                <td style="text-align: right;"><strong>{{ $quote['currency'] }} {{ number_format($quote['total_ttc'] ?? 0, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <!-- Terms and Conditions Section -->
    @if($quote['payment_terms'] || $quote['delivery_terms'] || $quote['discount_notes'])
    <div class="terms-section">
        <div class="terms-title">Terms & Conditions</div>
        @if($quote['payment_terms'])
        <p><strong>Payment Terms:</strong> {{ $quote['payment_terms'] }}</p>
        @endif
        @if($quote['delivery_terms'])
        <p><strong>Delivery Terms:</strong> {{ $quote['delivery_terms'] }}</p>
        @endif
        @if($quote['discount_notes'])
        <p><strong>Discount Notes:</strong> {{ $quote['discount_notes'] }}</p>
        @endif
    </div>
    @endif

    <!-- Signature Section -->
    <table class="signature-table">
        <tr>
            <!-- Customer signature area -->
            <td>
                <strong>Customer Acceptance</strong>
                <div class="signature-line"></div>
                <div style="font-size: 10px; margin-top: 5px;">Signature & Date</div>
            </td>
            <!-- Company representative signature area -->
            <td>
                <strong>Authorized Representative</strong>
                <div class="signature-line"></div>
                <div style="font-size: 10px; margin-top: 5px;">
                    {{ $quote['signature_name'] ?? 'Authorized Signatory' }}
                    @if($quote['signature_title'])
                    <br>{{ $quote['signature_title'] }}
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- Footer Section -->
    <div class="footer">
        <strong>Thank you for choosing Senselix ERP!</strong><br>
        This quotation is valid until {{ $quote['valid_until'] ? \Carbon\Carbon::parse($quote['valid_until'])->format('M d, Y') : 'N/A' }}<br><br>
        Generated on {{ \Carbon\Carbon::now()->format('M d, Y') }} | 
        For questions, contact us at info@senselix.com | 
        Document ID: {{ $quote['quote_number'] }}
    </div>
</body>
</html>
