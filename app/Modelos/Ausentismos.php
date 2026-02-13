<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class Ausentismos extends Model
{
    protected $table = 'ausentismos';
    protected $primaryKey = 'id_ausentismo';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado',
        'fecha_registro',
    ];
}