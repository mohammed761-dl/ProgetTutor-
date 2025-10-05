<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'id_admin';

    protected $table = 'admins';

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'status' => 'boolean',
    ];

    public function getRouteKeyName()
    {
        return 'id_admin';
    }

    // Validation rules
    public static function rules($id = null)
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,'.$id.',id_admin',
            'password' => $id ? 'nullable|min:8' : 'required|min:8',
            'status' => 'boolean',
        ];
    }

    // Status check methods
    public function isActive()
    {
        return $this->status === true;
    }
}
