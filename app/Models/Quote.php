<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Quote extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_quote';
    protected $table = 'quotes';

    // Status constants
    const STATUS_SENT_SAME_DAY = 'Sent same day';
    const STATUS_SENT_2_3_DAYS = 'Sent within 2-3 days';
    const STATUS_SENT_4_PLUS_DAYS = 'Sent after 4+ days';

    // Currency constants
    const CURRENCY_EUR = 'EUR';
    const CURRENCY_USD = 'USD';
    const CURRENCY_MAD = 'MAD';

    protected $fillable = [
        'quote_number',
        'id_customer',
        'id_user',
        'date_quote',
        'valid_until',
        'status',
        'has_po',
        'currency',
        'payment_terms',
        'delivery_terms',
        'discount_notes',
        'total_amount',
        'total_ht',
        'reduction',
        'vat_rate',
        'vat',
        'total_ttc',
        'signature_name',
        'signature_title',
    ];

    protected $casts = [
        'date_quote' => 'date',
        'valid_until' => 'date',
        'status' => 'string',
        'has_po' => 'boolean',
        'currency' => 'string',
        'total_amount' => 'decimal:2',
        'total_ht' => 'decimal:2',
        'reduction' => 'decimal:2',
        'vat_rate' => 'decimal:4',
        'vat' => 'decimal:2',
        'total_ttc' => 'decimal:2',
        'payment_terms' => 'json',
        'delivery_terms' => 'json',
    ];

    public function getRouteKeyName()
    {
        return 'id_quote';
    }

    // Relationships with snapshots
    // Original customer relationship
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer');
    }

    // Customer snapshot relationship
    public function customerSnapshot()
    {
        return $this->hasOne(QuoteCustomer::class, 'id_quote');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'quote_products', 'id_quote', 'id_product')
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
        return $this->hasMany(QuoteProduct::class, 'id_quote');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'id_quote');
    }

    public function deliveryNotes()
    {
        return $this->hasMany(DeliveryNote::class, 'id_quote');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'id_quote');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    // Scopes
    public function scopeValid($query)
    {
        return $query->where('valid_until', '>=', Carbon::now());
    }

    public function scopeExpired($query)
    {
        return $query->where('valid_until', '<', Carbon::now());
    }

    // Number generation
    public static function generateQuoteNumber()
    {
        $prefix = 'SNXQT';
        $year = date('Y');
        
        $lastQuote = self::where('quote_number', 'like', $prefix . $year . '%')
            ->orderBy('id_quote', 'desc')
            ->first();

        $number = $lastQuote ? intval(substr($lastQuote->quote_number, -5)) + 1 : 1;
        return sprintf("%s%s%05d", $prefix, $year, $number);
    }

    // Financial calculations
    public function calculateTotalHT()
    {
        $this->load('quoteProducts');
        return $this->quoteProducts->sum('total_line_price');
    }

    public function calculateVAT($vatRate = 0.20)
    {
        return ($this->total_ht - $this->reduction) * $vatRate;
    }

    public function calculateTotalTTC()
    {
        return $this->total_ht - $this->reduction + $this->vat;
    }

    public function recalculateFinancials($vatRate = 0.20)
    {
        $this->total_ht = $this->calculateTotalHT();
        $this->vat = $this->calculateVAT($vatRate);
        $this->total_ttc = $this->calculateTotalTTC();
        $this->total_amount = $this->total_ttc;
        return $this->save();
    }

    // Status checks
    public function isValid()
    {
        return Carbon::now()->lte($this->valid_until);
    }

    public function hasPurchaseOrder()
    {
        return $this->has_po;
    }

    // Create snapshot
    public function createProductSnapshots()
    {
        foreach ($this->products as $product) {
            QuoteProduct::createWithSnapshot($this->id_quote, $product);
        }
    }

    // Validation rules
    public static function rules()
    {
        return [
            'id_customer' => 'required|exists:customers,id_customer',
            'id_user' => 'required|exists:users,id_user',
            'date_quote' => 'required|date',
            'valid_until' => 'required|date|after:date_quote',
            'status' => 'required|in:' . implode(',', [
                self::STATUS_SENT_SAME_DAY,
                self::STATUS_SENT_2_3_DAYS,
                self::STATUS_SENT_4_PLUS_DAYS
            ]),
            'currency' => 'required|in:' . implode(',', [
                self::CURRENCY_EUR,
                self::CURRENCY_USD,
                self::CURRENCY_MAD
            ]),
            'products' => 'required|array|min:1',
            'products.*.id_product' => 'required|exists:products,id_product',
            'products.*.quantity' => 'required|integer|min:1'
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quote) {
            if (empty($quote->quote_number)) {
                $quote->quote_number = self::generateQuoteNumber();
            }
            if (empty($quote->date_quote)) {
                $quote->date_quote = Carbon::now();
            }
            if (empty($quote->valid_until)) {
                $quote->valid_until = Carbon::now()->addMonths(1);
            }
        });

    // static::created(function ($quote) {
    //     // Create both product and customer snapshots
    //     $quote->createProductSnapshots();
    //     QuoteCustomer::createWithSnapshot($quote->id_quote, $quote->customer);
    // });
    }
}
