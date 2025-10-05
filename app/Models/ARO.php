<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ARO (Acknowledgment of Receipt Order) Model
 *
 * Represents an acknowledgment document that confirms the receipt of goods
 * from a purchase order. This model tracks delivery status and maintains
 * a record of what was actually received vs. what was ordered.
 *
 * Key Features:
 * - Auto-generates ARO numbers in format SNX-ARO-YYYY-XXXXX
 * - Links to PurchaseOrder for order reference
 * - Tracks delivery status (Pending, Delivered, Partially Delivered, Cancelled)
 * - Maintains audit trail with creator and timestamps
 * - Supports multiple products per ARO
 */
class ARO extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'aros';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'id_aro';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'aro_number',        // Auto-generated ARO number
        'id_po',            // Purchase Order ID
        'date_aro',         // Date of ARO creation
        'status',           // Delivery status
        'created_by',       // User who created the ARO
        'remarks',          // Additional notes
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date_aro' => 'date',
        'status' => 'string',
    ];

    /**
     * Boot method to set up model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate ARO number on creation
        static::creating(function ($aro) {
            if (! $aro->aro_number) {
                $aro->aro_number = static::generateAroNumber();
            }
        });
    }

    /**
     * Generate ARO number in format SNX-ARO-YYYY-XXXXX
     *
     * @return string The generated ARO number
     */
    public static function generateAroNumber()
    {
        $currentYear = date('Y');
        $prefix = "SNX-ARO-{$currentYear}-";

        // Get the last ARO number for this year
        $lastAro = static::where('aro_number', 'LIKE', $prefix.'%')
            ->orderBy('aro_number', 'desc')
            ->first();

        if ($lastAro) {
            // Extract the number part and increment
            $lastNumber = (int) substr($lastAro->aro_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            // First ARO of the year
            $newNumber = 1;
        }

        return $prefix.str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'id_aro';
    }

    /**
     * Get the purchase order that this ARO acknowledges.
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'id_po', 'id_po');
    }

    /**
     * Get the ARO products (items received).
     */
    public function aroProducts()
    {
        return $this->hasMany(AroProduct::class, 'id_aro', 'id_aro');
    }

    /**
     * Get the user who created this ARO.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id_user');
    }

    /**
     * Get the delivery notes associated with this ARO.
     */
    public function deliveryNotes()
    {
        return $this->hasMany(DeliveryNote::class, 'id_aro', 'id_aro');
    }

    /**
     * Get the documents associated with this ARO.
     */
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Get the customer through the purchase order.
     */
    public function getCustomerAttribute()
    {
        return $this->purchaseOrder?->customer;
    }

    /**
     * Get the CSS class for status badge styling.
     */
    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            'Pending' => 'bg-yellow-100 text-yellow-800',
            'Delivered' => 'bg-green-100 text-green-800',
            'Partially Delivered' => 'bg-blue-100 text-blue-800',
            'Cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
