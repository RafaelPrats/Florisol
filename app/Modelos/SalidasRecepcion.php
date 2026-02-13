<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class SalidasRecepcion extends Model
{
    protected $table = 'salidas_recepcion';
    protected $primaryKey = 'id_salidas_recepcion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_variedad',
        'cantidad',
        'basura',
        'fecha',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }
}
