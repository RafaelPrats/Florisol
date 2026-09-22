<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class FincaProveedor extends Model
{
    protected $table = 'finca_proveedor';
    protected $primaryKey = 'id_finca_proveedor';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'nombre',
        'estado',
        'telefonos',
    ];
}
