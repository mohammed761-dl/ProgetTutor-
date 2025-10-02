<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DnpProduct extends Model
{
    /** @use HasFactory<\Database\Factories\DnpProductFactory> */
    use HasFactory;

    protected $table = 'dnp_products';

    protected $fillable = [
        'id_dnp',
        'aro_product_id',
        'po_product_id',
        'id_quote_product',
        'product_code',
        'name',
        'description',
        'technical_specs',
        'quantity_shipped',
        'unit_price',
        'serial_numbers',
        'tracking_code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity_shipped' => 'integer',
        'unit_price' => 'decimal:2',
        'total_line_price' => 'decimal:2',
    ];

    /**
     * Get the delivery note that this product belongs to
     */
    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class, 'id_dnp', 'id_dnp');
    }

    /**
     * Get the original ARO product entry that this delivery note product was created from
     */
    public function aroProduct()
    {
        return $this->belongsTo(AroProduct::class, 'aro_product_id', 'id');
    }
}
