<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentFactory> */
    use HasFactory;

    protected $primaryKey = 'id_document';

    public $incrementing = true;

    protected $fillable = [
        'file_name',
        'file_path',
        'type',
        'description',
        'documentable_id',
        'documentable_type',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    // Polymorphic relationship
    public function documentable()
    {
        return $this->morphTo();
    }
}
