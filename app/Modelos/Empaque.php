<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class Empaque extends Model
{
    protected $table = 'empaque';
    protected $primaryKey = 'id_empaque';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_empresa',
        'nombre',
        'fecha_registro',
        'estado',
    ];

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }
}