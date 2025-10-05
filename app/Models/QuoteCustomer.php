<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteCustomer extends Model
{
    use HasFactory;

    protected $table = 'quote_customers';

    protected $fillable = [
        'id_quote',
        'id_customer',
        'company_name',
        'contact_name',
        'email',
        'phone',
        'address',
        'vat_number',
        'performance_flag',
    ];

    protected $casts = [
        'address' => 'json',
        'performance_flag' => 'string',
    ];

    // Base relationships
    public function quote()
    {
        return $this->belongsTo(Quote::class, 'id_quote');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer');
    }

    // Snapshot creation
    public static function createWithSnapshot($quoteId, Customer $customer)
    {
        return self::create([
            'id_quote' => $quoteId,
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

    // Validation rules
    public static function rules()
    {
        return [
            'id_quote' => 'required|exists:quotes,id_quote',
            'id_customer' => 'required|exists:customers,id_customer',
            'company_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:50',
            'address' => 'required|json',
            'vat_number' => 'required|string|max:50',
        ];
    }
}
