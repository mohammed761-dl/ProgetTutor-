<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryNote extends Model
{
    use HasFactory;

    protected $table = 'delivery_notes';

    protected $primaryKey = 'id_dnp'; // Keep existing field name for compatibility

    public $incrementing = true;

    protected $fillable = [
        'dnp_number', // Keep existing field name for compatibility
        'id_aro',
        'planned_delivery_date',
        'actual_delivery_date',
        'status',
        'delivery_status', // Keep old field for backward compatibility
        'client_approved',
        'date_delivery',
        'customer_po_number',
        'incoterms',
        'delivery_address',
        'packaging_details',
        'created_by',
        'remarks',
    ];

    protected $casts = [
        'planned_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'date_delivery' => 'date',
        'client_approved' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->dnp_number)) {
                $model->dnp_number = $model->generateDeliveryNoteNumber();
            }
        });
    }

    public function generateDeliveryNoteNumber()
    {
        $year = Carbon::now()->year;

        // Get the last DN number for this year
        $lastDN = static::where('dnp_number', 'like', "SNX-DN-{$year}-%")
            ->orderBy('dnp_number', 'desc')
            ->first();

        if ($lastDN) {
            // Extract the sequence number from the last DN
            $parts = explode('-', $lastDN->dnp_number);
            $lastSequence = (int) end($parts);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }

        return sprintf('SNX-DN-%d-%05d', $year, $newSequence);
    }

    // Route model binding
    public function getRouteKeyName()
    {
        return 'id_dnp';
    }

    // Relationship with ARO
    public function aro()
    {
        return $this->belongsTo(ARO::class, 'id_aro', 'id_aro');
    }

    // Relationship with User (creator)
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id_user');
    }

    // Relationship with Products (many-to-many with pivot)
    public function products()
    {
        return $this->belongsToMany(Product::class, 'dnp_product', 'id_dnp', 'id_product')
            ->withPivot(['product_quantity_delivered', 'quantity_shipped', 'product_name', 'product_price'])
            ->withTimestamps();
    }

    // Status badge helper method
    public function getStatusBadge()
    {
        $status = $this->status ?? $this->delivery_status;

        return match ($status) {
            'Pending' => 'bg-yellow-100 text-yellow-800',
            'Delivered' => 'bg-green-100 text-green-800',
            'Partially Delivered' => 'bg-blue-100 text-blue-800',
            'Returned' => 'bg-orange-100 text-orange-800',
            'Cancelled' => 'bg-red-100 text-red-800',
            'Problem' => 'bg-yellow-100 text-yellow-800',
            'Blocked' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    // Get effective status (prefer new status field over old delivery_status)
    public function getStatusAttribute($value)
    {
        return $value ?? $this->attributes['delivery_status'] ?? 'Pending';
    }

    // Accessor for DN number (for compatibility)
    public function getDnNumberAttribute()
    {
        return $this->dnp_number;
    }

    // Accessor for DN ID (for compatibility)
    public function getIdDnAttribute()
    {
        return $this->id_dnp;
    }
}
