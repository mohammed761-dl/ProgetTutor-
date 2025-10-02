<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteProduct extends Model
{
    use HasFactory;

    protected $table = 'quote_products';

    protected $fillable = [
        'id_quote',
        'id_product',
        'product_code',
        'name',
        'description',
        'technical_specs',
        'commercial_terms',
        'payment_terms',
        'min_delivery_day',
        'max_delivery_day',
        'availability_yrs',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'technical_specs' => 'json',
        'commercial_terms' => 'json',
        'payment_terms' => 'json',
        'unit_price' => 'decimal:2',
        'total_line_price' => 'decimal:2',
        'min_delivery_day' => 'integer',
        'max_delivery_day' => 'integer',
        'availability_yrs' => 'integer'
    ];

    // Base relationships
    public function quote()
    {
        return $this->belongsTo(Quote::class, 'id_quote');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product');
    }

    // Snapshot-based relationships
    public function poProducts()
    {
        return $this->hasMany(PoProduct::class, 'quote_product_id');
    }

    public function aroProducts()
    {
        return $this->hasMany(AroProduct::class, 'quote_product_id');
    }

    public function dnpProducts()
    {
        return $this->hasMany(DnpProduct::class, 'id_quote_product');
    }

    public function invoiceProducts()
    {
        return $this->hasMany(InvoiceProduct::class, 'quote_product_id');
    }

    // Snapshot creation
    public static function createWithSnapshot($quoteId, Product $product, $quantity = 1)
    {
        return self::create([
            'id_quote' => $quoteId,
            'id_product' => $product->id_product,
            'product_code' => $product->product_code,
            'name' => $product->name,
            'description' => $product->description,
            'technical_specs' => $product->technical_specs,
            'commercial_terms' => $product->commercial_terms,
            'payment_terms' => $product->payment_terms,
            'min_delivery_day' => $product->min_delivery_day,
            'max_delivery_day' => $product->max_delivery_day,
            'availability_yrs' => $product->availability_yrs,
            'quantity' => $quantity,
            'unit_price' => $product->unit_price
        ]);
    }

    // Validation rules
    public static function rules()
    {
        return [
            'id_quote' => 'required|exists:quotes,id_quote',
            'id_product' => 'required|exists:products,id_product',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0'
        ];
    }

    // Automatic total calculation
    protected static function boot()
    {
        parent::boot();
    }
}
