<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ClientePropuesta extends Model
{
    protected $table = 'cliente_propuesta';
    protected $primaryKey = 'id_cliente_propuesta';
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
