<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ProcesoQueue extends Model
{
    protected $table = 'proceso_queue';
    protected $primaryKey = 'id_proceso_queue';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'numero',
        'total_proceso',
        'descripcion',
        'id_usuario',
    ];

    public function usuario()
    {
        return $this->belongsTo('\yura\Modelos\Usuario', 'id_usuario');
    }
}
