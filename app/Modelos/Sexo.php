<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class Sexo extends Model
{
    protected $table = 'sexo';
    protected $primaryKey = 'id_sexo';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado',
        'fecha_registro',
    ];

    public function personales()
    {
        return $this->hasMany('\yura\Modelos\Personal', 'id_sexo');
    }
}
