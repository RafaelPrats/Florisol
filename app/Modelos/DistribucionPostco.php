<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DistribucionPostco extends Model
{
    protected $table = 'distribucion_postco';
    protected $primaryKey = 'id_distribucion_postco';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_postco',
        'id_item',
        'longitud',
        'unidades',
    ];

    public function postco()
    {
        return $this->belongsTo('\yura\Modelos\Postco', 'id_postco');
    }

    public function item()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_item');
    }
}
