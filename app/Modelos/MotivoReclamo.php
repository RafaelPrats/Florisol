<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class MotivoReclamo extends Model
{
    protected $table = 'motivo_reclamo';
    protected $primaryKey = 'id_motivo_reclamo';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado',
    ];
}
