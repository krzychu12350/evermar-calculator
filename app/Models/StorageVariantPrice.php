<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorageVariantPrice extends Model
{
    use HasFactory;

    protected $fillable = ['variant_id', 'capacity_kwh', 'price'];

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }
}
