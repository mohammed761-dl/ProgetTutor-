<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'id_user';
    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => 'string'
    ];

    // Role constants
    const ROLE_CEO = 'CEO';
    const ROLE_COMMERCIAL = 'Commercial';
    const ROLE_VIEWER = 'Viewer';

    public function getRouteKeyName()
    {
        return 'id_user';
    }

    // Relationships
    public function quotes()
    {
        return $this->hasMany(Quote::class, 'id_user');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'created_by');
    }

    public function deliveryNotes()
    {
        return $this->hasMany(DeliveryNote::class, 'created_by');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }

    // Role checking methods
    public function isCEO()
    {
        return $this->role === self::ROLE_CEO;
    }

    public function isCommercial()
    {
        return $this->role === self::ROLE_COMMERCIAL;
    }

    public function isViewer()
    {
        return $this->role === self::ROLE_VIEWER;
    }

    // Validation rules
    public static function rules($id = null)
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id . ',id_user',
            'password' => $id ? 'nullable|min:8' : 'required|min:8',
            'role' => 'required|in:' . implode(',', [self::ROLE_CEO, self::ROLE_COMMERCIAL, self::ROLE_VIEWER])
        ];
    }
}
