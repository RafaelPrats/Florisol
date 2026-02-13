<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Aplicacion extends Model
{
    protected $table = 'aplicacion';
    protected $primaryKey = 'id_aplicacion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'dia_ini',
        'semana_ini',
        'repeticiones',
        'veces_x_semana',
        'poda_siembra', // T podas y siembras; S solo siembras; P solo podas
        'estado',
        'litro_x_cama',
        'tipo',
        'id_aplicacion_matriz',
        'id_empresa',
    ];

    public function aplicacion_matriz()
    {
        return $this->belongsTo('\yura\Modelos\AplicacionMatriz', 'id_aplicacion_matriz');
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }

    public function parametros()
    {
        return $this->hasMany('\yura\Modelos\ParametroAplicacion', 'id_aplicacion');
    }

    public function variedades()
    {
        return $this->hasMany('\yura\Modelos\AplicacionVariedad', 'id_aplicacion');
    }

    public function getTipo()
    {
        $tipos = [
            'S' => 'Sanidad',
            'C' => 'Cultural',
        ];
        return $tipos[$this->tipo];
    }
}
