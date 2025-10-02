<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyInfo extends Model
{
    /** @use HasFactory<\Database\Factories\CompanyInfoFactory> */
    use HasFactory;

    protected $table = 'company_info';

    protected $primaryKey = 'id_company_info';

    public $incrementing = false;

    protected $fillable = [
        'id_company_info',
        'name',
        'address',
        'phone',
        'email',
        'website',
        'warranty_duration',
        'support_info',
        'bank_name',
        'swift_bic',
        'account_number',
        'iban',
        'authorized_name',
        'authorized_title',
        'signature_email',
        'signature_phone',
        'general_conditions_url',
        'vat_number',
    ];

    protected $casts = [
        'id_company_info' => 'integer',
        'warranty_duration' => 'integer',
    ];

    // Singleton pattern - ensure only one record exists
    public static function getInstance()
    {
        return static::firstOrCreate(
            ['id_company_info' => 1],
            [
                'name' => 'Default Company Name',
                'address' => 'Default Address',
                'phone' => 'Default Phone',
                'email' => 'default@company.com',
                'warranty_duration' => 12,
                'support_info' => 'Default Support Info',
                'bank_name' => 'Default Bank',
                'swift_bic' => 'DEFAULT',
                'account_number' => '0000000000',
                'iban' => 'DEFAULT000000000000000000',
                'authorized_name' => 'Default Authorized Name',
                'authorized_title' => 'Default Title',
                'signature_email' => 'signature@company.com',
                'signature_phone' => 'Default Signature Phone',
                'vat_number' => 'DEFAULT000000',
            ]
        );
    }
}
