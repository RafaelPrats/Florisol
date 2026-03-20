<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ApiStoreCajas extends Model
{
    protected $table = 'api_store_cajas';
    protected $primaryKey = 'id_api_store_cajas';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'documento ',
        'fecha',
    ];

    public function inventarios()
    {
        return $this->hasMany('\yura\Modelos\InventarioRecepcion', 'id_api_store_cajas');
    }
}
