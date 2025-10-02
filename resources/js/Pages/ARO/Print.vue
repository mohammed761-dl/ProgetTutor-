<template>
    <div class="print-container">
        <!-- Print Controls (hidden when printing) -->
        <div class="no-print print-controls">
            <div class="controls-wrapper">
                <button @click="printPage" class="btn btn-primary">
                    <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print ARO
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
                    Back to AROs
                </button>
            </div>
        </div>

        <!-- Professional Header with Logo and Company Info -->
        <div class="professional-header">
            <div class="header-content">
                <div class="logo-section">
                    <div class="logo-box">SENSELIX</div>
                    <div class="logo-tagline">Professional ERP Solutions</div>
                </div>
                <div class="company-section">
                    <h1 class="company-name">SENSELIX ERP</h1>
                    <div class="company-details">
                        Business Solutions & Technology<br>
                        123 Business Street, Suite 100<br>
                        City, State 12345<br>
                        Phone: +1 (555) 123-4567<br>
                        Email: info@senselix.com<br>
                        Web: www.senselix.com
                    </div>
                </div>
            </div>
        </div>

        <!-- Professional ARO Title -->
        <div class="quote-title-section">
            <h1>ACKNOWLEDGMENT OF RECEIPT ORDER</h1>
            <h2>{{ aro.aro_number || 'N/A' }}</h2>
        </div>

        <div class="quote-details-professional">
            <!-- Customer Information -->
            <div class="info-section customer-info">
                <div class="info-title">CUSTOMER INFORMATION</div>
                <div class="info-content">
                    <p class="company-name-text">{{ aro.purchase_order?.customer?.company_name || 'N/A' }}</p>
                    <p v-if="aro.purchase_order?.customer?.address">{{ aro.purchase_order.customer.address }}</p>
                    <p v-if="aro.purchase_order?.customer?.city || aro.purchase_order?.customer?.postal_code">
                        {{ aro.purchase_order.customer.city }}{{ aro.purchase_order.customer.postal_code ? ', ' + aro.purchase_order.customer.postal_code : '' }}
                    </p>
                    <p v-if="aro.purchase_order?.customer?.country">{{ aro.purchase_order.customer.country }}</p>
                    <p v-if="aro.purchase_order?.customer?.email"><strong>Email:</strong> {{ aro.purchase_order.customer.email }}</p>
                    <p v-if="aro.purchase_order?.customer?.phone"><strong>Phone:</strong> {{ aro.purchase_order.customer.phone }}</p>
                </div>
            </div>

            <!-- ARO Information -->
            <div class="info-section quote-info">
                <div class="info-title">ARO DETAILS</div>
                <div class="info-content">
                    <p><strong>ARO #:</strong> {{ aro.aro_number || 'N/A' }}</p>
                    <p><strong>Date:</strong> {{ formatDate(aro.date_aro) }}</p>
                    <p><strong>Status:</strong> {{ aro.status || 'N/A' }}</p>
                    <p><strong>PO Number:</strong> {{ aro.purchase_order?.po_number || 'N/A' }}</p>
                    <p><strong>PO Status:</strong> {{ aro.purchase_order?.status || 'N/A' }}</p>
                    <p v-if="aro.creator?.name"><strong>Created By:</strong> {{ aro.creator.name }}</p>
                </div>
            </div>
        </div>

        <!-- Professional Products Table -->
        <table class="products-table-professional">
            <thead>
                <tr>
                    <th style="width: 15%;">Product Code</th>
                    <th style="width: 30%;">Product Name</th>
                    <th style="width: 25%;">Description</th>
                    <th style="width: 10%;">Qty Ordered</th>
                    <th style="width: 10%;">Qty Received</th>
                    <th style="width: 10%;">Unit Price</th>
                    <th style="width: 15%;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="!aro.purchase_order?.products || !aro.purchase_order.products.length">
                    <td colspan="7" style="text-align:center;">No products found.</td>
                </tr>
                <tr v-else v-for="(product, idx) in aro.purchase_order.products" :key="product.id || idx">
                    <td><strong>{{ product.product_code || 'N/A' }}</strong></td>
                    <td>{{ product.name || 'N/A' }}</td>
                    <td>{{ product.description || 'N/A' }}</td>
                    <td style="text-align: center;"><strong>{{ product.quantity || 0 }}</strong></td>
                    <td style="text-align: center;"><strong>{{ product.quantity_received || 0 }}</strong></td>
                    <td style="text-align: right;">{{ formatCurrency(product.unit_price || 0) }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ formatCurrency((product.quantity || 0) * (product.unit_price || 0)) }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="6" style="text-align: right;"><strong>TOTAL:</strong></td>
                    <td style="text-align: right;"><strong>{{ formatCurrency(calculateTotal()) }}</strong></td>
                </tr>
            </tfoot>
        </table>

        <!-- Remarks Section -->
        <div v-if="aro.remarks" class="remarks-section">
            <h3>Remarks</h3>
            <p>{{ aro.remarks }}</p>
        </div>

        <!-- Professional Signature Section -->
        <div class="signature-section-professional">
            <div class="signature-grid">
                <div class="signature-item">
                    <strong>Received By</strong>
                    <div class="signature-line"></div>
                    <div class="signature-label">Customer Signature & Date</div>
                </div>
                <div class="signature-item">
                    <strong>Authorized Representative</strong>
                    <div class="signature-line"></div>
                    <div class="signature-label">
                        {{ aro.creator?.name || 'Authorized Signatory' }}
                        <span v-if="aro.creator?.title"><br>{{ aro.creator.title }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Professional Footer -->
        <div class="footer-professional">
            <strong>Thank you for your business!</strong><br>
            This document acknowledges the receipt of goods for PO: {{ aro.purchase_order?.po_number || 'N/A' }}<br><br>
            Generated on {{ formatDate(new Date()) }} | 
            For questions, contact us at info@senselix.com | 
            Document ID: {{ aro.aro_number || 'N/A' }}
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    aro: Object
});

// Helper functions
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

const calculateTotal = () => {
    if (props.aro?.purchase_order?.products && props.aro.purchase_order.products.length) {
        return props.aro.purchase_order.products.reduce((total, product) => {
            const quantity = parseFloat(product.quantity) || 0;
            const unitPrice = parseFloat(product.unit_price) || 0;
            return total + (quantity * unitPrice);
        }, 0);
    }
    return 0;
};

// Actions
const printPage = () => {
    const style = document.createElement('style');
    style.textContent = `
        @page { 
            margin: 0.5in; 
            size: A4;
            @top-center { content: ""; }
            @top-left { content: ""; }
            @top-right { content: ""; }
            @bottom-center { content: ""; }
            @bottom-left { content: ""; }
            @bottom-right { content: ""; }
        }
        @media print {
            html, body { margin: 0 !important; padding: 0 !important; }
        }
    `;
    document.head.appendChild(style);
    window.print();
    setTimeout(() => {
        document.head.removeChild(style);
    }, 1000);
};

const downloadPDF = () => {
    const pdfUrl = `/aro/${props.aro.id_aro}/pdf`;
    const link = document.createElement('a');
    link.href = pdfUrl;
    link.download = `ARO-${props.aro.aro_number}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

const goBack = () => {
    // Navigate back to ARO index
    window.location.href = '/aro';
};
</script>

<style scoped>
/* Importing the same styles as Quotes/Print.vue */
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

/* Using the same styles as in Quotes/Print.vue for consistency */
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

.btn:active {
    transform: translateY(0);
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

/* Professional Header Styling */
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
}

.logo-box {
    width: 100px;
    height: 60px;
    background-color: #B8860B;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
    border-radius: 5px;
    margin-bottom: 5px;
}

.logo-tagline {
    font-size: 9px;
    color: #666;
    margin-top: 5px;
}

.company-section {
    flex: 0 0 60%;
    text-align: right;
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
    line-height: 1.4;
}

/* Professional Title Styling */
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

.quote-title-section h2 {
    margin: 5px 0 0 0;
    font-size: 14px;
}

/* Information Sections */
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

/* Products Table */
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

/* Remarks Section */
.remarks-section {
    margin: 20px 0;
    padding: 15px;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
}

.remarks-section h3 {
    color: #B8860B;
    font-size: 14px;
    margin: 0 0 10px 0;
}

/* Signature Section */
.signature-section-professional {
    margin-top: 40px;
}

.signature-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 50px;
}

.signature-item {
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

/* Footer */
.footer-professional {
    margin-top: 40px;
    text-align: center;
    font-size: 9px;
    color: #666;
    border-top: 1px solid #dee2e6;
    padding-top: 15px;
}
</style>
