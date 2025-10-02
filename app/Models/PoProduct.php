<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * PoProduct Model
 * 
 * Represents products within a Purchase Order (PO).
 * This model stores a snapshot of product information at the time of PO creation,
 * including pricing, quantities, and delivery terms.
 * 
 * Key Features:
 * - Links to PurchaseOrder via id_po
 * - Links to QuoteProduct via quote_product_id for reference
 * - Stores product snapshot data for historical accuracy
 * - Supports bulk creation from QuoteProduct data
 */
class PoProduct extends Model
{
    /** @use HasFactory<\Database\Factories\PoProductFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'po_products';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id_po',                    // Purchase Order ID
        'quote_product_id',         // Reference to original quote product
        'product_code',             // Product code/sku
        'name',                     // Product name
        'description',              // Product description
        'technical_specs',          // Technical specifications
        'commercial_terms',         // Commercial terms and conditions
        'payment_terms',            // Payment terms
        'min_delivery_day',         // Minimum delivery time in days
        'max_delivery_day',         // Maximum delivery time in days
        'quantity',                 // Quantity ordered
        'unit_price'                // Unit price at time of PO
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'product_unit_price' => 'decimal:2',
        'unit_price_at_time' => 'decimal:2',
        'total_line_price' => 'decimal:2',
    ];

    /**
     * Get the purchase order that owns this product.
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'id_po');
    }

    /**
     * Get the original quote product that this PO product is based on.
     */
    public function quoteProduct()
    {
        return $this->belongsTo(QuoteProduct::class, 'quote_product_id');
    }

    /**
     * Get the base product information.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product');
    }

    /**
     * Create a new PO product with snapshot data from a QuoteProduct.
     * 
     * This method ensures that PO products maintain historical accuracy
     * by capturing the state of quote products at the time of PO creation.
     * 
     * @param int $poId The ID of the purchase order
     * @param QuoteProduct $quoteProduct The quote product to snapshot
     * @return PoProduct The created PO product instance
     */
    public static function createWithSnapshot($poId, QuoteProduct $quoteProduct)
    {
        return self::create([
            'id_po' => $poId,
            'quote_product_id' => $quoteProduct->id,
            'product_code' => $quoteProduct->product_code,
            'name' => $quoteProduct->name,
            'description' => $quoteProduct->description,
            'technical_specs' => $quoteProduct->technical_specs,
            'commercial_terms' => $quoteProduct->commercial_terms,
            'payment_terms' => $quoteProduct->payment_terms,
            'min_delivery_day' => $quoteProduct->min_delivery_day,
            'max_delivery_day' => $quoteProduct->max_delivery_day,
            'quantity' => $quoteProduct->quantity,
            'unit_price' => $quoteProduct->unit_price,
        ]);
    }
}
