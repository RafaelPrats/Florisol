<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ArmadoPostco extends Model
{
    protected $table = 'armado_postco';
    protected $primaryKey = 'id_armado_postco';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_postco',
        'ramos',
        'id_cliente',
    ];

    public function postco()
    {
        return $this->belongsTo('\yura\Modelos\Postco', 'id_postco');
    }

    public function cliente()
    {
        return $this->belongsTo('\yura\Modelos\Cliente', 'id_cliente');
    }
}
