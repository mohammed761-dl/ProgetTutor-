<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_po';
    protected $table = 'purchase_orders';

    // Status constants
    const STATUS_PENDING = 'Pending';
    const STATUS_APPROVED = 'Approved';
    const STATUS_DELIVERED = 'Delivered';
    const STATUS_CANCELLED = 'Cancelled';

    protected $fillable = [
        'po_number',
        'id_customer',
        'id_quote',
        'created_by',
        'status',
        'planned_delivery_date',
        'actual_delivery_date',
        'remarks',
        'pdf_path',
    ];

    protected $casts = [
        'planned_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'status' => 'string',
        'remarks' => 'string',
    ];

    public function getRouteKeyName()
    {
        return 'id_po';
    }

    // Relationships with snapshots
    public function quote()
    {
        return $this->belongsTo(Quote::class, 'id_quote');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'po_products', 'id_po', 'quote_product_id')
            ->withPivot([
                'product_code',
                'name',
                'description',
                'technical_specs',
                'commercial_terms',
                'payment_terms',
                'min_delivery_day',
                'max_delivery_day',
                'quantity',
                'unit_price',
                'total_line_price'
            ])
            ->withTimestamps();
    }

    public function aros()
{
    return $this->hasMany(\App\Models\ARO::class, 'id_po', 'id_po');
}
    public function poProducts()
    {
        return $this->hasMany(PoProduct::class, 'id_po');
    }

    public function poCustomer()
    {
        return $this->hasOne(PoCustomer::class, 'id_po');
    }

    public function deliveryNotes()
    {
        return $this->hasMany(DeliveryNote::class, 'id_po');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    // Number generation
    public static function generatePONumber()
    {
        $prefix = 'SNX-PO-' . date('Y') . '-';
        $lastPO = self::where('po_number', 'like', $prefix . '%')
            ->orderBy('id_po', 'desc')
            ->first();

        $number = $lastPO ? intval(substr($lastPO->po_number, -5)) + 1 : 1;
        return $prefix . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    // Status methods
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isDelivered()
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    // Create snapshots
    public function createSnapshots()
    {
        // Create customer snapshot
        PoCustomer::createWithSnapshot($this->id_po, $this->customer);

        // Create product snapshots
        foreach ($this->quote->quoteProducts as $quoteProduct) {
            PoProduct::createWithSnapshot($this->id_po, $quoteProduct);
        }
    }

    // PDF URL accessor
    public function getPdfUrlAttribute()
    {
        return $this->pdf_path ? asset('storage/' . $this->pdf_path) : null;
    }

    // Validation rules
    public static function rules()
    {
        return [
            'id_quote' => 'required|exists:quotes,id_quote',
            'id_customer' => 'required|exists:customers,id_customer',
            'created_by' => 'required|exists:users,id_user',
            'status' => 'required|in:' . implode(',', [
                self::STATUS_PENDING,
                self::STATUS_APPROVED,
                self::STATUS_DELIVERED,
                self::STATUS_CANCELLED
            ]),
            'planned_delivery_date' => 'required|date|after:today',
            'actual_delivery_date' => 'nullable|date|after:planned_delivery_date',
            'remarks' => 'nullable|string'
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($po) {
            if (empty($po->po_number)) {
                $po->po_number = self::generatePONumber();
            }
            if (empty($po->status)) {
                $po->status = self::STATUS_PENDING;
            }
        });

        static::created(function ($po) {
            $po->createSnapshots();
            
            // Update quote has_po flag
            if ($po->quote) {
                $po->quote->update(['has_po' => true]);
            }
        });
    }
}
