<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class Especificaciones extends Model
{
    protected $table = 'especificaciones';
    protected $primaryKey = 'id_especificaciones';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_cliente ',
        'id_variedad',
        'tipo_caja',
        'ramos_x_caja',
        'tallos_x_ramo',
        'longitud',
    ];

    public function cliente()
    {
        return $this->belongsTo('\yura\Modelos\Cliente', 'id_cliente');
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }
}
