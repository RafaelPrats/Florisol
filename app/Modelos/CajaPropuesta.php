<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class CajaPropuesta extends Model
{
    protected $table = 'caja_propuesta';
    protected $primaryKey = 'id_caja_propuesta';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_propuesta',
        'nombre',
    ];

    public function propuesta()
    {
        return $this->belongsTo('\yura\Modelos\Propuesta', 'id_propuesta');
    }
}
