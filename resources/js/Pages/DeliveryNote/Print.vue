<!-- filepath: resources/js/Pages/DeliveryNote/Print.vue -->

<script setup>
import { onMounted } from 'vue';
import { Printer } from 'lucide-vue-next';

// Props from the backend
const props = defineProps({
    deliveryNote: {
        type: Object,
        required: true
    }
});

// Auto-print when component mounts
onMounted(() => {
    window.print();
});

// Format currency
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};

// Format date
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const printPage = () => {
    window.print();
};

const downloadPDF = () => {
    const pdfUrl = route('delivery-notes.pdf', props.deliveryNote.id_dnp);
    const link = document.createElement('a');
    link.href = pdfUrl;
    link.download = `${props.deliveryNote.dn_number}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

const goBack = () => {
    window.close();
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = route('delivery-notes.index');
    }
};
</script>

<template>
    <div class="print-container">
        <!-- Print Controls (hidden when printing) -->
        <div class="no-print print-controls">
            <div class="controls-wrapper">
                <button @click="printPage" class="btn btn-primary">
                    <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print Delivery Note
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
                    Back to Delivery Notes
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

        <!-- Professional Delivery Note Title -->
        <div class="delivery-title-section">
            <h1>DELIVERY NOTE</h1>
            <h2>{{ deliveryNote.dn_number }}</h2>
        </div>

        <div class="delivery-details-professional">
            <!-- From (Company) Information -->
            <div class="info-section company-info">
                <div class="info-title">FROM</div>
                <div class="info-content">
                    <p class="company-name-text">{{ deliveryNote.company?.name || 'SENSELIX ERP' }}</p>
                    <p v-if="deliveryNote.company?.address">{{ deliveryNote.company.address }}</p>
                    <p v-if="deliveryNote.company?.phone"><strong>Phone:</strong> {{ deliveryNote.company.phone }}</p>
                    <p v-if="deliveryNote.company?.email"><strong>Email:</strong> {{ deliveryNote.company.email }}</p>
                </div>
            </div>

            <!-- To (Customer) Information -->
            <div class="info-section customer-info">
                <div class="info-title">TO</div>
                <div class="info-content">
                    <p class="company-name-text">{{ deliveryNote.customer?.company_name || 'N/A' }}</p>
                    <p v-if="deliveryNote.customer?.contact_name">{{ deliveryNote.customer.contact_name }}</p>
                    <p v-if="deliveryNote.customer?.address">{{ deliveryNote.customer.address }}</p>
                    <p v-if="deliveryNote.customer?.phone"><strong>Phone:</strong> {{ deliveryNote.customer.phone }}</p>
                    <p v-if="deliveryNote.customer?.email"><strong>Email:</strong> {{ deliveryNote.customer.email }}</p>
                </div>
            </div>
        </div>

        <div class="delivery-details-professional">
            <!-- Delivery Information -->
            <div class="info-section delivery-info">
                <div class="info-title">DELIVERY INFORMATION</div>
                <div class="info-content">
                    <p><strong>Delivery Date:</strong> {{ formatDate(deliveryNote.date_delivery) }}</p>
                    <p><strong>Planned Delivery:</strong> {{ formatDate(deliveryNote.planned_delivery_date) }}</p>
                    <p><strong>Actual Delivery:</strong> {{ deliveryNote.actual_delivery_date ? formatDate(deliveryNote.actual_delivery_date) : 'Not delivered yet' }}</p>
                    <p><strong>Status:</strong> {{ deliveryNote.status }}</p>
                    <p v-if="deliveryNote.incoterms"><strong>Incoterms:</strong> {{ deliveryNote.incoterms }}</p>
                </div>
            </div>

            <!-- Delivery Address -->
            <div class="info-section address-info">
                <div class="info-title">DELIVERY ADDRESS</div>
                <div class="info-content">
                    <p>{{ deliveryNote.delivery_address || 'Same as customer address' }}</p>
                    <div v-if="deliveryNote.packaging_details" style="margin-top: 10px;">
                        <p><strong>Packaging Details:</strong></p>
                        <p>{{ deliveryNote.packaging_details }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Professional Products Table -->
        <table class="products-table-professional">
            <thead>
                <tr>
                    <th style="width: 15%;">Product Code</th>
                    <th style="width: 35%;">Product Name</th>
                    <th style="width: 20%;">Description</th>
                    <th style="width: 15%;">Qty</th>
                    <th style="width: 15%;">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="!deliveryNote.products || !deliveryNote.products.length">
                    <td colspan="5" style="text-align:center;">No products found.</td>
                </tr>
                <tr v-else v-for="product in deliveryNote.products" :key="product.id">
                    <td><strong>{{ product.product_code || 'N/A' }}</strong></td>
                    <td>{{ product.product_name || 'N/A' }}</td>
                    <td>{{ product.description || 'N/A' }}</td>
                    <td style="text-align: center;"><strong>{{ product.quantity || 0 }}</strong></td>
                    <td style="text-align: center;">{{ product.status || 'Pending' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Notes Section -->
        <div v-if="deliveryNote.notes" class="notes-section">
            <h3>Notes</h3>
            <p>{{ deliveryNote.notes }}</p>
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
                    <strong>Delivered By</strong>
                    <div class="signature-line"></div>
                    <div class="signature-label">Driver/Courier Signature & Date</div>
                </div>
            </div>
        </div>

        <!-- Professional Footer -->
        <div class="footer-professional">
            <strong>Thank you for choosing Senselix!</strong><br>
            Generated on {{ formatDate(new Date()) }} | 
            For questions, contact us at info@senselix.com | 
            Document ID: {{ deliveryNote.dn_number }}
        </div>
    </div>
</template>

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

.delivery-title-section {
    text-align: center;
    background-color: #B8860B;
    color: white;
    padding: 15px;
    margin: 20px 0;
    border-radius: 5px;
}

.delivery-title-section h1 {
    margin: 0;
    font-size: 24px;
}

.delivery-title-section h2 {
    margin: 5px 0 0 0;
    font-size: 14px;
}

.delivery-details-professional {
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

.notes-section {
    margin: 20px 0;
    padding: 15px;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
}

.notes-section h3 {
    color: #B8860B;
    font-size: 14px;
    margin: 0 0 10px 0;
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
