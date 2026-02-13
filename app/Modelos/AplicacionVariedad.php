<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class AplicacionVariedad extends Model
{
    protected $table = 'aplicacion_variedad';
    protected $primaryKey = 'id_aplicacion_variedad';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_aplicacion',
        'id_variedad',
    ];

    public function aplicacion()
    {
        return $this->belongsTo('\yura\Modelos\Aplicacion', 'id_aplicacion');
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }
}
