<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class GradoInstruccion extends Model
{
    protected $table = 'grado_instruccion';
    protected $primaryKey = 'id_grado_instruccion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado',
        'fecha_registro',
    ];
}
