<?php

// filepath: app/Models/Customer.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory;

    protected $primaryKey = 'id_customer';

    public $incrementing = true;

    protected $fillable = [
        'company_name',
        'contact_name',
        'email',
        'phone',
        'address',
        'performance_flag',
        'vat_number',
    ];

    protected $casts = [
        'performance_flag' => 'string',
    ];

    // ✅ Route model binding method (only one needed)
    public function getRouteKeyName()
    {
        return 'id_customer';
    }

    // Relationships
    public function quotes()
    {
        return $this->hasMany(Quote::class, 'id_customer', 'id_customer');
    }

    /**
     * Get purchase orders through quotes
     */
    public function purchaseOrders()
    {
        return $this->hasManyThrough(
            PurchaseOrder::class,
            Quote::class,
            'id_customer', // Foreign key on quotes
            'id_quote', // Foreign key on purchase_orders
            'id_customer', // Local key on customers
            'id_quote' // Local key on quotes
        );
    }

    /**
     * Get delivery notes through quotes
     */
    public function deliveryNotes()
    {
        return $this->hasManyThrough(
            DeliveryNote::class,
            Quote::class,
            'id_customer', // Foreign key on quotes
            'id_quote', // Foreign key on delivery_notes
            'id_customer', // Local key on customers
            'id_quote' // Local key on quotes
        );
    }

    /**
     * Get invoices through quotes
     */
    public function invoices()
    {
        return $this->hasManyThrough(
            Invoice::class,
            Quote::class,
            'id_customer', // Foreign key on quotes
            'id_quote', // Foreign key on invoices
            'id_customer', // Local key on customers
            'id_quote' // Local key on quotes
        );
    }
}
