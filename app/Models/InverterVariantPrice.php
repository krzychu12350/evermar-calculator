<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InverterVariantPrice extends Model
{
    use HasFactory;

    protected $fillable = ['variant_id', 'price'];

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }
}
