<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PanelModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'manufacturer',
        'power_watt',
    ];

    public function panelVariantPrices()
    {
        return $this->hasMany(PanelVariantPrice::class);
    }
}
