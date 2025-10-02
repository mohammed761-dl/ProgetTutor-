<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoCustomer extends Model
{
    use HasFactory;

    protected $table = 'po_customers';

    protected $fillable = [
        'id_po',
        'id_customer',
        'company_name',
        'contact_name',
        'email',
        'phone',
        'address',
        'vat_number',
        'performance_flag',
    ];

    // Relationships
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'id_po', 'id_po');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id_customer');
    }

    // Snapshot creation
    public static function createWithSnapshot($poId, Customer $customer)
    {
        return self::create([
            'id_po' => $poId,
            'id_customer' => $customer->id_customer,
            'company_name' => $customer->company_name,
            'contact_name' => $customer->contact_name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'address' => $customer->address,
            'vat_number' => $customer->vat_number,
            'performance_flag' => $customer->performance_flag,
        ]);
    }
}
