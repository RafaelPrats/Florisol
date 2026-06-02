<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class MotivoBaja extends Model
{
    protected $table = 'motivo_baja';
    protected $primaryKey = 'id_motivo_baja';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado',
    ];
}
