<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ResumenFechas extends Model
{
    protected $table = 'resumen_fechas';
    protected $primaryKey = 'id_resumen_fechas';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_variedad',
        'fecha',
        'semana',
        'mes',
        'anno',
        'tallos_comprados',
        'tallos_desechados',
        'tallos_recibidos',
        'last_update',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }
}
