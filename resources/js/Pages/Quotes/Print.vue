<template> 
    <div class="print-container">
        <!-- Print Controls (hidden when printing) -->
        <div class="no-print print-controls">
            <div class="controls-wrapper">
                <button @click="printPage" class="btn btn-primary">
                    <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print Quote
                </button>
                <button @click="downloadPDF" class="btn btn-success">
                    <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download PDF
                </button>
                <button @click="goBack" class="btn btn-secondary">
                    <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Quotes
                </button>
            </div>
        </div>

        <!-- Professional Header with Logo and Company Info -->
        <div class="professional-header">
            <div class="header-content">
                <div class="logo-section">
                    <img :src="logoUrl" alt="SENSELIX Logo" class="company-logo" />
                </div>
                <div class="company-section">
                    <div class="company-details">
                        Your trusted partner in current and voltage acquisition.<br>
                        N° 3, Avenue Abi Lhassan Chadili,<br>
                        Tangier – Morocco<br>
                        Contact@senselix.com<br>
                        <a href="https://www.senselix.com" target="_blank" style="color:#B8860B; text-decoration:none;">
                            www.senselix.com
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Professional Quote Title -->
        <div class="quote-title-section">
            <h1>QUOTATION</h1>
        </div>

        <div class="quote-details-professional">
            <!-- Customer Information -->
            <div class="info-section customer-info">
                <div class="info-title">BILL TO</div>
                <div class="info-content">
                    <p class="company-name-text">{{ quote.customer?.company_name || 'N/A' }}</p>
                    <p v-if="quote.customer?.address">{{ quote.customer.address }}</p>
                    <p v-if="quote.customer?.city || quote.customer?.postal_code">
                        {{ quote.customer?.city }}{{ quote.customer?.postal_code ? ', ' + quote.customer.postal_code : '' }}
                    </p>
                    <p v-if="quote.customer?.country">{{ quote.customer.country }}</p>
                    <p v-if="quote.customer?.email"><strong>Email:</strong> {{ quote.customer.email }}</p>
                    <p v-if="quote.customer?.phone"><strong>Phone:</strong> {{ quote.customer.phone }}</p>
                </div>
            </div>

            <!-- Quote Information -->
            <div class="info-section quote-info">
                <div class="info-title">QUOTE DETAILS</div>
                <div class="info-content">
                    <p><strong>Quote #:</strong> {{ quote.quote_number }}</p>
                    <p><strong>Date:</strong> {{ formatDate(quote.date_quote) }}</p>
                    <p><strong>Valid Until:</strong> {{ formatDate(quote.valid_until) }}</p>
                    <p><strong>Status:</strong> {{ quote.status }}</p>
                    <p><strong>Currency:</strong> {{ quote.currency }}</p>
                    <p v-if="quote.user"><strong>Created By:</strong> {{ quote.user.name }}</p>
                </div>
            </div>
        </div>

        <!-- Professional Products Table -->
        <table class="products-table-professional">
            <thead>
                <tr>
                    <th style="width: 15%;text-align:center;">Product Code</th>
                    <th style="width: 40%;text-align:center;">Description</th>
                    <th style="width: 10%;text-align:center;">Qty</th>
                    <th style="width: 15%;text-align:center;">Unit Price</th>
                    <th style="width: 20%;text-align:center;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="!QuoteProduct || !QuoteProduct.length">
                    <td colspan="5" style="text-align:center;">No products found.</td>
                </tr>
                <tr v-else v-for="product in QuoteProduct" :key="product.id">
                    <td style="text-align: center;"><strong>{{ product.product_code || 'N/A' }}</strong></td>
                    <td style="text-align: center;"><strong>{{ product.name || 'N/A' }}</strong></td>
                    <td style="text-align: center;"><strong>{{ product.quantity || 0 }}</strong></td>
                    <td style="text-align: right;">{{ quote.currency }} {{ formatCurrency(product.unit_price || 0) }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ quote.currency }} {{ formatCurrency(product.total_line_price || 0) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Professional Totals -->
        <div class="totals-professional">
            <table class="totals-table-professional">
                <tbody>
                    <tr class="subtotal-row">
                        <td><strong>Subtotal {{ quote.currency }} :</strong></td>
                        <td style="text-align: right;"><strong>{{ formatCurrency(quote.total_amount) }}</strong></td>
                    </tr>
                    <tr v-if="quote.reduction && quote.reduction > 0">
                        <td><strong>Discount {{ quote.currency }} :</strong></td>
                        <td style="text-align: right;"><strong>- {{ formatCurrency(quote.reduction) }}</strong></td>
                    </tr>
                    <tr class="subtotal-row">
                        <td><strong>VAT ({{ getVATRate() }}%) :</strong></td>
                        <td style="text-align: right;"><strong>{{ quote.currency }} {{ formatCurrency(quote.vat) }}</strong></td>
                    </tr>
                    <tr class="total-row">
                        <td><strong>TOTAL :</strong></td>
                        <td style="text-align: right;"><strong>{{ quote.currency }} {{ formatCurrency(quote.total_ttc) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Professional Signature Section -->
        <div class="signature-section-professional">
            <div class="signature-grid">
                <div class="signature-item">
                    <strong>Authorized Representative</strong>
                    <div class="signature-line"></div>
                    <div class="signature-label">
                        {{ quote.signature_name || 'Authorized Signatory' }}
                        <span v-if="quote.signature_title"><br>{{ quote.signature_title }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Professional Footer -->
        <div class="footer-professional">
            <strong>Thank you for choosing Senselix !</strong><br>
            This quotation is valid until {{ formatDate(quote.valid_until) }}<br>
            Generated on {{ formatDate(new Date()) }} | 
            For questions, contact us at info@senselix.com | 
            Document ID: {{ quote.quote_number }}
        </div>

        <!-- Terms and Conditions -->
        <div v-if="quote.payment_terms || quote.delivery_terms || quote.discount_notes" style="margin-top: 30px;">
            <h3 style="color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px;">Terms & Conditions</h3>
            <div v-if="quote.payment_terms">
                <strong>Payment Terms:</strong>
                <p style="margin: 5px 0;">{{ quote.payment_terms }}</p>
            </div>
            <div v-if="quote.delivery_terms">
                <strong>Delivery Terms:</strong>
                <p style="margin: 5px 0;">{{ quote.delivery_terms }}</p>
            </div>
            <div v-if="quote.discount_notes">
                <strong>Discount Notes:</strong>
                <p style="margin: 5px 0;">{{ quote.discount_notes }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
/**
 * Quote Print Page
 * Display only - uses data provided by backend
 * No calculations performed in frontend
 */
import logoUrl from '../../assets/SenselixLogo.jpg'

const props = defineProps({
    quote: Object,
    QuoteProduct: Array
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatCurrency = (amount) => {
    if (!amount) return '0.00';
    return parseFloat(amount).toFixed(2);
};

const getVATRate = () => {
    if (props.quote.vat_rate !== undefined && props.quote.vat_rate !== null) {
        return Math.round(props.quote.vat_rate * 100 * 10) / 10;
    } else {
        // If no VAT rate is stored, check if we can calculate it from the totals
        if (props.quote.vat && props.quote.total_amount && props.quote.reduction) {
            const totalAfterReduction = props.quote.total_amount - props.quote.reduction;
            if (totalAfterReduction > 0) {
                const calculatedVatRate = props.quote.vat / totalAfterReduction;
                return Math.round(calculatedVatRate * 100 * 10) / 10;
            }
        }
        return 20.0; // Fallback default
    }
};

const printPage = () => {
    window.print();
};

const downloadPDF = () => {
    const pdfUrl = route('quotes.pdf', props.quote.id_quote);
    const link = document.createElement('a');
    link.href = pdfUrl;
    link.download = `${props.quote.quote_number}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

const goBack = () => {
    window.close();
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = route('quotes.index');
    }
};
</script>

<style scoped>
/* Print-specific styles */
@page {
    margin: 0.5in;
    @top-center { content: none; }
    @top-left { content: none; }
    @top-right { content: none; }
    @bottom-center { content: none; }
    @bottom-left { content: none; }
    @bottom-right { content: none; }
}

@media print {
    body * {
        visibility: hidden;
    }
    .print-container,
    .print-container * {
        visibility: visible;
    }
    .print-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .no-print {
        display: none !important;
    }
    
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        font-size: 12px !important;
    }
}

.print-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    font-family: Arial, sans-serif;
    background: white;
}

.print-controls {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.controls-wrapper {
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.btn-icon {
    width: 18px;
    height: 18px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-success {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

.btn-secondary {
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    color: #333;
}

.professional-header {
    width: 100%;
    margin-bottom: 30px;
    border-bottom: 3px solid #B8860B;
    padding-bottom: 15px;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.logo-section {
    flex: 0 0 40%;
    display: flex;
    align-items: center;
}

.company-logo {
    height: 70px;
    object-fit: contain;
    display: block;
}

.company-section {
    flex: 0 0 60%;
    text-align: right;
}

.company-details {
    font-size: 10px;
    color: #666;
    margin-top: 5px;
    line-height: 1.4;
}

.quote-title-section {
    text-align: center;
    background-color: #B8860B;
    color: white;
    padding: 15px;
    margin: 20px 0;
    border-radius: 5px;
}

.quote-title-section h1 {
    margin: 0;
    font-size: 24px;
}

.quote-details-professional {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

.info-section {
    padding: 15px;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
}

.info-title {
    font-weight: bold;
    color: #B8860B;
    font-size: 13px;
    margin-bottom: 10px;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 3px;
}

.info-content p {
    margin: 3px 0;
    font-size: 12px;
}

.company-name-text {
    font-weight: bold;
    font-size: 14px;
}

.products-table-professional {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

.products-table-professional th {
    background-color: #B8860B;
    color: white;
    padding: 10px 8px;
    text-align: left;
    font-weight: bold;
    border: 1px solid #B8860B;
}

.products-table-professional td {
    padding: 8px;
    border: 1px solid #dee2e6;
}

.products-table-professional tr:nth-child(even) {
    background-color: #f8f9fa;
}

.totals-professional {
    text-align: right;
    margin-top: 20px;
}

.totals-table-professional {
    width: 300px;
    margin-left: auto;
    border-collapse: collapse;
}

.totals-table-professional td {
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

.signature-section-professional {
    margin-top: 40px;
}

.signature-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 50px;
}

.signature-item {
    grid-column: 2;
    text-align: center;
    padding: 20px;
}

.signature-line {
    border-bottom: 2px solid #333;
    height: 40px;
    margin: 15px 0 5px 0;
}

.signature-label {
    font-size: 10px;
    margin-top: 5px;
}

.footer-professional {
    margin-top: 40px;
    text-align: center;
    font-size: 9px;
    color: #666;
    border-top: 1px solid #dee2e6;
    padding-top: 15px;
}
</style>
