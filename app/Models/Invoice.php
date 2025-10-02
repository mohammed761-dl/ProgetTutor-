<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_invoice';
    public $incrementing = true;

    // ✅ Add route key name for proper model binding
    public function getRouteKeyName()
    {
        return 'id_invoice';
    }

    protected $fillable = [
        'invoice_number',
        'id_quote',
        'status',
        'issue_date',
        'due_date',
        'date_invoice',
        'payment_status',
        'date_payment',
        'currency',
        'payment_terms',
        'sub_total',
        'tax_total',
        'discount_total',
        'grand_total',
        'total_excl_vat',
        'vat_amount',
        'total_incl_vat',
        'supplier_vat_number',
        'supplier_iso_certification',
        'created_by',
        'remarks',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'datetime',
        'due_date' => 'datetime',
        'sub_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    // Auto-generate invoice number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            // Generate invoice number
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }

            // Get quote data
            $quote = $invoice->quote;
            
            // Copy quote customer snapshot data
            $customerSnapshot = $quote->customerSnapshot;
            $invoice->customer_name = $customerSnapshot->company_name;
            $invoice->customer_vat = $customerSnapshot->vat_number;
            $invoice->customer_address = $customerSnapshot->address;
            $invoice->customer_contact_person = $customerSnapshot->contact_name;
            $invoice->customer_email = $customerSnapshot->email;
            $invoice->customer_phone = $customerSnapshot->phone;

            // Copy quote products
            foreach ($quote->quoteProducts as $quoteProduct) {
                $invoice->quoteProducts()->attach($quoteProduct->id, [
                    'product_code' => $quoteProduct->product_code,
                    'name' => $quoteProduct->name,
                    'description' => $quoteProduct->description,
                    'quantity_invoiced' => $quoteProduct->quantity,
                    'unit_price' => $quoteProduct->unit_price,
                    'total_ht' => $quoteProduct->total_line_price,
                    'vat_amount' => $quoteProduct->total_line_price * 0.20, // 20% VAT
                    'reduction' => 0,
                    'line_total' => $quoteProduct->total_line_price * 1.20 // Including VAT
                ]);
            }
        });
    }

    private static function generateInvoiceNumber()
    {
        $year = Carbon::now()->year;
        $prefix = "SNX-INV-{$year}-";

        // Get the latest invoice number for this year
        $lastInvoice = self::where('invoice_number', 'LIKE', $prefix.'%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->invoice_number, -5));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix.str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class, 'delivery_note_id', 'id_dnp');
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class, 'id_quote', 'id_quote');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id_user');
    }

    public function customer()
    {
        return $this->quote->customer();
    }

    public function quoteProducts()
    {
        return $this->belongsToMany(QuoteProduct::class, 'invoice_quote_product', 'invoice_id', 'quote_product_id')
            ->withPivot('product_name', 'unit_price', 'quantity_invoiced', 'total_ht', 'vat_amount', 'reduction', 'line_total')
            ->withTimestamps();
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    // Helper methods
    public function isEditable()
    {
        return in_array($this->status, ['Draft', 'Unpaid']);
    }

    public function isOverdue()
    {
        return $this->status === 'Unpaid' && $this->due_date < Carbon::now();
    }

    public function calculateTotals()
    {
        $subTotal = 0;
        $taxTotal = 0;
        $discountTotal = 0;

        foreach ($this->quoteProducts as $quoteProduct) {
            $subTotal += $quoteProduct->pivot->total_ht;
            $taxTotal += $quoteProduct->pivot->vat_amount;
            $discountTotal += $quoteProduct->pivot->reduction;
        }

        $this->sub_total = $subTotal;
        $this->tax_total = $taxTotal;
        $this->discount_total = $discountTotal;
        $this->grand_total = $subTotal + $taxTotal - $discountTotal;
    }
}
