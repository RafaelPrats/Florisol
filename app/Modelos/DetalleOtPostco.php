<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DetalleOtPostco extends Model
{
    protected $table = 'detalle_ot_postco';
    protected $primaryKey = 'id_detalle_ot_postco';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_ot_postco ',
        'unidades',
        'id_item',
    ];

    public function ot_postco()
    {
        return $this->belongsTo('\yura\Modelos\OtPostco', 'id_ot_postco ');
    }

    public function item()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_item');
    }
}
