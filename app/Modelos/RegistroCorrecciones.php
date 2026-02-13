<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class RegistroCorrecciones extends Model
{
    protected $table = 'registro_correcciones';
    protected $primaryKey = 'id_registro_correcciones';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'fecha',
        'descripcion',
    ];

    public function usuario()
    {
        return $this->belongsTo('\yura\Modelos\Usuario', 'id_usuario');
    }
}
