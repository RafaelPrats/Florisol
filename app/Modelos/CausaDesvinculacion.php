<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class CausaDesvinculacion extends Model
{
    protected $table = 'causa_desvinculacion';
    protected $primaryKey = 'id_causa_desvinculacion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado',
        'fecha_registro',
    ];
}
