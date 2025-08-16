<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PanelVariantPrice extends Model
{
    use HasFactory;

    protected $fillable = ['variant_id', 'model', 'install_type', 'price_per_panel'];

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }
}
