<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceProduct extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceProductFactory> */
    use HasFactory;

    protected $table = 'invoice_product';

    protected $fillable = [
        'id_invoice',
        'id_product',
        'quantity',
        'price_final',
        'total_line_price',
    ];

    protected $casts = [
        'price_final' => 'decimal:2',
        'total_line_price' => 'decimal:2',
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'id_invoice');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product');
    }
}
