<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    use HasFactory;

    protected $fillable = ['panel_count'];

    public function panelPrices()
    {
        return $this->hasMany(PanelVariantPrice::class);
    }

//    public function inverterPrices()
//    {
//        return $this->hasMany(InverterVariantPrice::class);
//    }

    public function inverterPrice()
    {
        return $this->hasOne(InverterVariantPrice::class);
    }

    public function storagePrices()
    {
        return $this->hasMany(StorageVariantPrice::class);
    }
}
