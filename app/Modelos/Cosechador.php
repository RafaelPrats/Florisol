<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class Cosechador extends Model
{
    protected $table = 'cosechador';
    protected $primaryKey = 'id_cosechador';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_empresa',
        'nombre',
        'estado',
    ];

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\Configuracion_empresa', 'empresa');
    }
}
