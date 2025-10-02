<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_product';
    protected $table = 'products';

    // Status constants
    const STATUS_ACTIVE = 'Active';
    const STATUS_EOL = 'EOL';
    const STATUS_ARCHIVED = 'Archived';

    protected $fillable = [
        'product_code',
        'name',
        'description',
        'technical_specs',
        'commercial_terms',
        'payment_terms',
        'image_url',
        'min_delivery_day',
        'max_delivery_day',
        'availability_yrs',
        'status',
        'unit_price',
    ];

    protected $casts = [
        'technical_specs' => 'string',
        'commercial_terms' => 'string',
        'payment_terms' => 'string',
        'unit_price' => 'decimal:2',
        'min_delivery_day' => 'integer',
        'max_delivery_day' => 'integer',
        'availability_yrs' => 'integer',
        'status' => 'string'
    ];

    public function getRouteKeyName()
    {
        return 'id_product';
    }

    // Relationships with snapshots
    public function quotes()
    {
        return $this->belongsToMany(Quote::class, 'quote_products', 'id_product', 'id_quote')
            ->withPivot([
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
                'total_line_price'
            ])
            ->withTimestamps();
    }

    public function quoteProducts()
    {
        return $this->hasMany(QuoteProduct::class, 'id_product');
    }

    public function purchaseOrders()
    {
        return $this->hasManyThrough(
            PurchaseOrder::class,
            QuoteProduct::class,
            'id_product',
            'id_quote',
            'id_product',
            'id_quote'
        );
    }

    public function deliveryNotes()
    {
        return $this->hasManyThrough(
            DeliveryNote::class,
            QuoteProduct::class,
            'id_product',
            'id_quote',
            'id_product',
            'id_quote'
        );
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function invoices()
    {
        return $this->hasManyThrough(
            Invoice::class,
            QuoteProduct::class,
            'id_product',
            'id_quote',
            'id_product',
            'id_quote'
        );
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeEol($query)
    {
        return $query->where('status', self::STATUS_EOL);
    }

    public function scopeArchived($query)
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    // Status checks
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isEol()
    {
        return $this->status === self::STATUS_EOL;
    }

    public function isArchived()
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    // Validation rules
    public static function rules($id = null)
    {
        return [
            'product_code' => 'required|string|unique:products,product_code,' . $id . ',id_product',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'technical_specs' => 'required|json',
            'commercial_terms' => 'required|json',
            'payment_terms' => 'required|json',
            'min_delivery_day' => 'required|integer|min:0',
            'max_delivery_day' => 'required|integer|gt:min_delivery_day',
            'availability_yrs' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'status' => 'required|in:' . implode(',', [self::STATUS_ACTIVE, self::STATUS_EOL, self::STATUS_ARCHIVED])
        ];
    }

    // Create snapshot data
    public function createSnapshot()
    {
        return [
            'product_code' => $this->product_code,
            'name' => $this->name,
            'description' => $this->description,
            'technical_specs' => $this->technical_specs,
            'commercial_terms' => $this->commercial_terms,
            'payment_terms' => $this->payment_terms,
            'min_delivery_day' => $this->min_delivery_day,
            'max_delivery_day' => $this->max_delivery_day,
            'availability_yrs' => $this->availability_yrs,
            'unit_price' => $this->unit_price,
            'snapshot_date' => now()
        ];
    }
}
