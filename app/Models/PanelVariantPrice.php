<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PanelVariantPrice extends Model
{
    use HasFactory;

    protected $fillable = ['variant_id','panel_model_id','install_type','price_per_panel'];

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }

    public function panelModel()
    {
        return $this->belongsTo(PanelModel::class);
    }
}
