<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DetalleOaPostco extends Model
{
    protected $table = 'detalle_oa_postco';
    protected $primaryKey = 'id_detalle_oa_postco';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_oa_postco ',
        'unidades',
        'id_item',
    ];

    public function oa_postco()
    {
        return $this->belongsTo('\yura\Modelos\OaPostco', 'id_oa_postco ');
    }

    public function item()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_item');
    }
}
