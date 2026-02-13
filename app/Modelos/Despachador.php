<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class Despachador extends Model
{
    protected $table = 'despachador';
    protected $primaryKey = 'id_despachador';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado',
    ];
}
