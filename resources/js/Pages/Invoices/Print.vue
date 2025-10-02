<template>
  <div class="print-container">
    <!-- Print Controls (hidden when printing) -->
    <div class="no-print print-controls">
      <div class="controls-wrapper">
        <button @click="printPage" class="btn btn-primary">
          <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
          </svg>
          Print Invoice
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
          Back to Invoices
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

    <!-- Professional Invoice Title -->
    <div class="invoice-title-section">
      <h1>INVOICE</h1>
      <h2>{{ invoice.invoice_number }}</h2>
    </div>

    <div class="invoice-details-professional">
      <!-- Customer Information -->
      <div class="info-section customer-info">
        <div class="info-title">BILL TO</div>
        <div class="info-content">
          <p class="company-name-text">{{ invoice.customer_name || 'N/A' }}</p>
          <p v-if="invoice.customer_address">{{ invoice.customer_address }}</p>
          <p v-if="invoice.customer_vat"><strong>VAT:</strong> {{ invoice.customer_vat }}</p>
          <p v-if="invoice.customer_email"><strong>Email:</strong> {{ invoice.customer_email }}</p>
          <p v-if="invoice.customer_phone"><strong>Phone:</strong> {{ invoice.customer_phone }}</p>
        </div>
      </div>

      <!-- Invoice Information -->
      <div class="info-section invoice-info">
        <div class="info-title">INVOICE DETAILS</div>
        <div class="info-content">
          <p><strong>Invoice #:</strong> {{ invoice.invoice_number }}</p>
          <p><strong>Date:</strong> {{ formatDate(invoice.issue_date) }}</p>
          <p><strong>Status:</strong> {{ invoice.status }}</p>
          <p><strong>Due Date:</strong> {{ formatDate(invoice.due_date) }}</p>
        </div>
      </div>
    </div>

    <!-- Professional Products Table -->
    <table class="products-table-professional">
      <thead>
        <tr>
          <th style="width: 15%;">Product Code</th>
          <th style="width: 30%;">Product Name</th>
          <th style="width: 15%;">Unit Price</th>
          <th style="width: 10%;">Qty</th>
          <th style="width: 15%;">Total (excl. VAT)</th>
          <th style="width: 15%;">VAT</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="!products || !products.length">
          <td colspan="6" style="text-align:center;">No products found.</td>
        </tr>
        <tr v-else v-for="product in products" :key="product.id">
          <td><strong>{{ product.product_code || 'N/A' }}</strong></td>
          <td>{{ product.product_name || 'N/A' }}</td>
          <td style="text-align: right;">{{ formatCurrency(product.unit_price || 0) }}</td>
          <td style="text-align: center;"><strong>{{ product.quantity || 0 }}</strong></td>
          <td style="text-align: right;">{{ formatCurrency(product.total_ht || 0) }}</td>
          <td style="text-align: right;">{{ formatCurrency(product.vat_amount || 0) }}</td>
        </tr>
      </tbody>
      <tfoot>
        <tr class="total-row">
          <td colspan="5" style="text-align: right;"><strong>Grand Total:</strong></td>
          <td style="text-align: right;"><strong>{{ formatCurrency(invoice.grand_total || 0) }}</strong></td>
        </tr>
      </tfoot>
    </table>

    <!-- Notes Section -->
    <div v-if="invoice.notes" class="notes-section">
      <h3>Notes</h3>
      <p>{{ invoice.notes }}</p>
    </div>

    <!-- Professional Signature Section -->
    <div class="signature-section-professional">
      <div class="signature-grid">
        <div class="signature-item">
          <strong>Customer Signature</strong>
          <div class="signature-line"></div>
          <div class="signature-label">Customer Signature & Date</div>
        </div>
        <div class="signature-item">
          <strong>Authorized Representative</strong>
          <div class="signature-line"></div>
          <div class="signature-label">Authorized Signatory</div>
        </div>
      </div>
    </div>

    <!-- Professional Footer -->
    <div class="footer-professional">
      <strong>Thank you for your business!</strong><br>
      Generated on {{ formatDate(new Date()) }} | 
      For questions, contact us at info@senselix.com | 
      Document ID: {{ invoice.invoice_number }}
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  invoice: Object,
  products: Array
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

const printPage = () => {
  window.print();
};

const downloadPDF = () => {
  const pdfUrl = route('invoices.pdf', props.invoice.id_invoice);
  const link = document.createElement('a');
  link.href = pdfUrl;
  link.download = `${props.invoice.invoice_number}.pdf`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

const goBack = () => {
  window.close();
  if (window.history.length > 1) {
    window.history.back();
  } else {
    window.location.href = route('invoices.index');
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

.invoice-title-section {
  text-align: center;
  background-color: #B8860B;
  color: white;
  padding: 15px;
  margin: 20px 0;
  border-radius: 5px;
}

.invoice-title-section h1 {
  margin: 0;
  font-size: 24px;
}

.invoice-title-section h2 {
  margin: 5px 0 0 0;
  font-size: 14px;
}

.invoice-details-professional {
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

.total-row {
  background-color: #B8860B;
  color: white;
  font-weight: bold;
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
