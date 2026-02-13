<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class AplicacionMatriz extends Model
{
    protected $table = 'aplicacion_matriz';
    protected $primaryKey = 'id_aplicacion_matriz';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'tipo', //Sanidad; Cultural
    ];

    public function aplicaciones()
    {
        return $this->hasMany('\yura\Modelos\Aplicacion', 'id_aplicacion_matriz');
    }

    public function mezclas()
    {
        return $this->hasMany('\yura\Modelos\AplicacionMezcla', 'id_aplicacion_matriz');
    }
}
