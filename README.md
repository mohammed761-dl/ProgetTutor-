# 🏢 **Senselix ERP - Professional Business Management Platform**

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Vue.js](https://img.shields.io/badge/Vue.js-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white)
![Inertia.js](https://img.shields.io/badge/Inertia.js-000000?style=for-the-badge&logo=inertia&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)

A comprehensive **Enterprise Resource Planning (ERP)** system built with modern web technologies for efficient business management, featuring quote management, purchase orders, delivery tracking, and invoice generation.

## 📋 **Table of Contents**

- [What This Project Does](#-what-this-project-does)
- [Key Features](#-key-features)
- [System Architecture](#-system-architecture)
- [What You Need to Install](#-what-you-need-to-install)
- [How to Run Locally (Step by Step)](#-how-to-run-locally-step-by-step)
- [How to Run with Docker (Step by Step)](#-how-to-run-with-docker-step-by-step)
- [API Documentation](#-api-documentation)
- [Troubleshooting](#-troubleshooting)
- [Contributing](#-contributing)
- [Authors & License](#-authors--license)

## 🎯 **What This Project Does**

Senselix ERP is a **comprehensive business management system** with four main modules:

1. **Quote Management** - Create, manage, and track customer quotes with professional print views
2. **Purchase Order System** - Convert quotes to POs with automated workflow management
3. **Delivery Tracking** - ARO (Acknowledgment of Receipt) and delivery note management
4. **Invoice Generation** - Automated invoice creation with PDF generation capabilities

Each module maintains data integrity through snapshot-based pivot tables, ensuring historical accuracy and complete audit trails.

## ✨ **Key Features**

### 🔐 **Authentication & Security**
- Role-based access control (CEO, Commercial, Viewer)
- Secure session management
- CSRF protection
- Admin and user authentication systems

### 🔄 **Business Process Management**
- **Quote-to-Cash Workflow**: Complete business process from quote to invoice
- **Real-time Status Tracking**: Monitor progress through each stage
- **Automated Calculations**: VAT, discounts, and totals computed automatically
- **Professional Print Views**: Ready-to-print documents with consistent branding

### 🗄️ **Data Management**
- **Snapshot Architecture**: Historical data preservation through pivot tables
- **Audit Trails**: Complete tracking of all business transactions
- **Data Integrity**: Master data changes don't affect existing documents
- **Flexible Relationships**: Efficient data queries and reporting

### 🎨 **User Experience**
- **Modern UI/UX**: Clean, professional interface built with Tailwind CSS
- **Responsive Design**: Works seamlessly on all devices
- **Print Optimization**: Professional document layouts for printing
- **Real-time Updates**: Instant feedback and status changes

## 🏗️ **System Architecture**

### **Technology Stack**
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend      │    │   Backend       │    │   Database      │
│   (Vue.js +     │◄──▶│   (Laravel)     │◄──▶│   (MySQL)       │
│   Inertia.js)   │    │   API Layer     │    │   + Pivot       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Print Views   │    │   Business      │    │   Data          │
│   (PDF Ready)   │    │   Logic Layer   │    │   Snapshots     │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### **Data Flow Architecture**
```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│  Products  │◄───┤   Quote     │◄───┤  Customer   │
│  (Master)  │    │  Products   │    │ (Snapshot)  │
└─────────────┘    └─────────────┘    └─────────────┘
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│ Purchase    │    │     ARO     │    │  Delivery   │
│  Orders     │    │  Products   │    │   Notes     │
└─────────────┘    └─────────────┘    └─────────────┘
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│  Invoice    │    │  Complete   │    │  Audit      │
│ Generation  │    │  Workflow   │    │  Trail      │
└─────────────┘    └─────────────┘    └─────────────┘
```

### **Snapshot-Based Data Flow**

The system implements a snapshot-based architecture to maintain data integrity and historical accuracy:

1. **Admin Side**
   - Product Management
   - Customer Management
   - User Management
   - Master Data Control

2. **Document Flow**
   ```
   Quote -> PO -> ARO -> Delivery Note -> Invoice
   ```
   Each step creates independent snapshots to preserve data integrity.

3. **Data Independence**
   - Each document maintains its own copy of relevant data
   - Changes to master data don't affect existing documents
   - Complete historical accuracy for reporting and auditing

## 📦 **What You Need to Install**

### **System Requirements**
- **PHP**: 8.1 or higher
- **Composer**: Latest version
- **Node.js**: 16.0 or higher
- **MySQL**: 8.0 or higher
- **Git**: Latest version

### **Development Tools**
- **VS Code** (recommended) with Laravel and Vue.js extensions
- **Postman** or **Insomnia** for API testing
- **MySQL Workbench** or **phpMyAdmin** for database management

## 🚀 **How to Run Locally (Step by Step)**

### **1. Clone the Repository**
```bash
git clone https://github.com/CamelsTech/Laravel-senselix-ERP.git
cd Laravel-senselix-ERP
```

### **2. Install Backend Dependencies**
```bash
composer install
cp .env.example .env
php artisan key:generate
```

### **3. Configure Database**
```bash
# Edit .env file with your database credentials
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=senselix_erp
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### **4. Run Database Migrations**
```bash
php artisan migrate:fresh --seed
```

### **5. Install Frontend Dependencies**
```bash
npm install
```

### **6. Build Frontend Assets**
```bash
npm run dev
# or for production
npm run build
```

### **7. Start Development Server**
```bash
php artisan serve
```

### **8. Access the Application**
Open your browser and navigate to: `http://localhost:8000`

## 🐳 **How to Run with Docker (Step by Step)**

### **1. Docker Setup**
```bash
# Create docker-compose.yml file
docker-compose up -d
```

### **2. Install Dependencies**
```bash
docker-compose exec app composer install
docker-compose exec app npm install
```

### **3. Environment Configuration**
```bash
docker-compose exec app cp .env.example .env
docker-compose exec app php artisan key:generate
```

### **4. Database Setup**
```bash
docker-compose exec app php artisan migrate:fresh --seed
```

### **5. Build Assets**
```bash
docker-compose exec app npm run build
```

### **6. Access Application**
Navigate to: `http://localhost:8000`

## 📚 **API Documentation**

### **Core Endpoints**

#### **Quotes Management**
```http
GET    /Quote                    # List all quotes
POST   /Quote                    # Create new quote
GET    /Quote/{id}              # Get quote details
PUT    /Quote/{id}              # Update quote
DELETE /Quote/{id}              # Delete quote
GET    /Quote/{id}/print        # Print quote view
GET    /Quote/{id}/pdf          # Download PDF
```

#### **Purchase Orders**
```http
GET    /po                       # List all POs
POST   /po                       # Create new PO
GET    /po/{id}                 # Get PO details
PUT    /po/{id}                 # Update PO
DELETE /po/{id}                 # Delete PO
GET    /po/{id}/pdf             # Download PO PDF
```

#### **ARO Management**
```http
GET    /aro                      # List all AROs
POST   /aro                      # Create new ARO
GET    /aro/{id}                # Get ARO details
PUT    /aro/{id}                # Update ARO
DELETE /aro/{id}                # Delete ARO
GET    /aro/{id}/print          # Print ARO view
GET    /aro/{id}/pdf            # Download ARO PDF
```

#### **Delivery Notes**
```http
GET    /delivery-notes           # List all delivery notes
POST   /delivery-notes           # Create new delivery note
GET    /delivery-notes/{id}      # Get delivery note details
PUT    /delivery-notes/{id}      # Update delivery note
DELETE /delivery-notes/{id}      # Delete delivery note
GET    /delivery-notes/{id}/print # Print delivery note
```

#### **Invoices**
```http
GET    /invoices                 # List all invoices
POST   /invoices                 # Create new invoice
GET    /invoices/{id}            # Get invoice details
PUT    /invoices/{id}            # Update invoice
DELETE /invoices/{id}            # Delete invoice
GET    /invoices/{id}/print      # Print invoice view
GET    /invoices/{id}/pdf        # Download invoice PDF
```

## 📖 **User Guide**

### **1. Creating a Quote**
1. Navigate to Quotes > Create New Quote
2. Fill in customer information
3. Add products from the catalog
   - Products are snapshot at this point
   - Any future changes to product master data won't affect this quote
4. Set commercial terms
5. Save and generate PDF if needed

### **2. Converting Quote to Purchase Order**
1. From Quote details, click "Convert to PO"
2. System creates a PO with:
   - Snapshot of quote data
   - Link to original quote
   - Independent product data
3. Update PO-specific fields
4. Save and send to customer

### **3. Creating ARO**
1. Select related PO
2. System creates ARO with:
   - Snapshot of PO data
   - Independent product records
   - Delivery planning fields
3. Update quantities and dates
4. Confirm and process

### **4. Managing Delivery Notes**
1. Select related ARO
2. System creates Delivery Note with:
   - Snapshot of ARO products
   - Independent quantities
   - Shipping details
3. Add tracking numbers and serial numbers
4. Process delivery

### **5. Generating Invoices**
1. Select related Delivery Note(s)
2. System creates Invoice with:
   - Snapshot of delivered products
   - Actual delivered quantities
   - Final pricing
3. Add payment terms
4. Generate and send to customer

## 🔍 **Data Tracking Features**

- **Product Changes**
  - All product versions are preserved
  - Changes don't affect existing documents
  - Full history available for audit

- **Price Management**
  - Historical price tracking
  - Price changes don't affect existing quotes
  - Special pricing by customer/volume

- **Document Relationships**
  - Clear links between related documents
  - Easy tracking of document flow
  - Full audit trail

## 🛠️ **Technical Details**

### **Models Structure**

- **Base Models**
  - Products
  - Customers
  - Users
  - Companies

- **Document Models**
  - Quote & QuoteProduct
  - PurchaseOrder & PoProduct
  - ARO & AroProduct
  - DeliveryNote & DnpProduct
  - Invoice & InvoiceProduct

### **Data Independence**

Each document type maintains its own product data:

```php
// Example: DeliveryNote products
public function products()
{
    return $this->hasMany(DnpProduct::class, 'id_dnp', 'id_dnp');
}
```

### **Snapshot Creation**

Products are snapshotted at each stage:

```php
// Example: Creating delivery note products
foreach ($aro->products as $aroProduct) {
    $deliveryNote->products()->create([
        'aro_product_id' => $aroProduct->id,
        'product_code' => $aroProduct->product_code,
        'name' => $aroProduct->name,
        'description' => $aroProduct->description,
        'technical_specs' => $aroProduct->technical_specs,
        'quantity_shipped' => $aroProduct->quantity_confirmed,
        'unit_price' => $aroProduct->unit_price_agreed,
    ]);
}
```

## 🔧 **Troubleshooting**

### **Common Issues & Solutions**

#### **1. Database Connection Issues**
```bash
# Check database service
sudo systemctl status mysql

# Verify credentials in .env
php artisan tinker
DB::connection()->getPdo();
```

#### **2. Frontend Build Issues**
```bash
# Clear npm cache
npm cache clean --force

# Remove node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
```

#### **3. Laravel Issues**
```bash
# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Regenerate autoload files
composer dump-autoload
```

#### **4. Permission Issues**
```bash
# Set proper permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## 🤝 **Contributing**

1. **Fork** the repository
2. **Create** a feature branch (`git checkout -b feature/AmazingFeature`)
3. **Commit** your changes (`git commit -m 'Add some AmazingFeature'`)
4. **Push** to the branch (`git push origin feature/AmazingFeature`)
5. **Open** a Pull Request

### **Development Guidelines**
- Follow PSR-12 coding standards
- Write meaningful commit messages
- Include tests for new features
- Update documentation as needed

## 👥 **Authors & License**

### **Development Team**
- **Lead Developer**: Mohammed Cherkaoui
- **Email**: mohammedcherkaoui761@gmail.com
- **Phone**: +212612932333

### **License**
This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

### **Acknowledgments**
- Laravel team for the amazing framework
- Vue.js community for the reactive frontend
- Tailwind CSS for the utility-first CSS framework
- All contributors who helped make this project possible

---

## 🌟 **Why Choose Senselix ERP?**

✅ **Professional Grade**: Built with enterprise-level technologies  
✅ **Scalable Architecture**: Grows with your business needs  
✅ **Data Integrity**: Snapshot-based system ensures accuracy  
✅ **Complete Workflow**: End-to-end business process management  
✅ **Modern UI/UX**: Professional, responsive interface  
✅ **Open Source**: Free to use and modify  
✅ **Active Development**: Regular updates and improvements  

**Ready to streamline your business operations?** 🚀

---

*Built with ❤️ by the Senselix Development Team*
