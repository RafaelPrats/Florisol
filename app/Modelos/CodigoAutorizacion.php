<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class CodigoAutorizacion extends Model
{
    protected $table = 'codigo_autorizacion';
    protected $primaryKey = 'id_codigo_autorizacion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'valor',
    ];
}
