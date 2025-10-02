<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * AroProduct Model
 * 
 * Represents individual products within an ARO (Acknowledgment of Receipt Order).
 * This model tracks what was actually received for each product from the original quote,
 * including quantities, remarks, and pricing information.
 * 
 * Key Features:
 * - Links ARO to specific quote products
 * - Tracks actual quantity received vs. ordered
 * - Maintains audit trail of delivery discrepancies
 * - Supports custom remarks for each received item
 */
class AroProduct extends Model
{
    /** @use HasFactory<\Database\Factories\AroProductFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'aro_product';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id_aro',              // ARO ID
        'quote_product_id',    // Reference to original quote product
        'quantity_received',   // Actual quantity received
        'remarks'              // Notes about the received item
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'unit_price_agreed' => 'decimal:2',
        'total_line_price' => 'decimal:2',
    ];

    /**
     * Get the ARO that owns this product.
     */
    public function aro()
    {
        return $this->belongsTo(ARO::class, 'id_aro');
    }

    /**
     * Get the original quote product that this ARO product references.
     */
    public function quoteProduct()
    {
        return $this->belongsTo(QuoteProduct::class, 'quote_product_id');
    }

    /**
     * Get the base product information through the quote product.
     */
    public function product()
    {
        return $this->hasManyThrough(
            Product::class,
            QuoteProduct::class,
            'id',                    // Foreign key on quote_products table
            'id_product',            // Foreign key on the final model
            'quote_product_id',      // Local key on this model
            'id_product'             // Local key on the intermediary model
        );
    }

    /**
     * Get the confirmed quantity received.
     * 
     * @return int The quantity received
     */
    public function quantity_confirmed()
    {
        return $this->quantity_received;
    }

    /**
     * Get the unit price from the quote product.
     * 
     * @return float The unit price
     */
    public function unit_price()
    {
        return $this->quoteProduct->unit_price;
    }
}
