<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class SalidasGuarde extends Model
{
    protected $table = 'salidas_guarde';
    protected $primaryKey = 'id_salidas_guarde';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_guarde',
        'fecha',
        'cantidad',
    ];

    public function guarde()
    {
        return $this->belongsTo('\yura\Modelos\Guarde', 'id_guarde');
    }
}
