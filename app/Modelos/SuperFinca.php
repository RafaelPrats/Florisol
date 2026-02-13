<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class SuperFinca extends Model
{
    protected $table = 'super_finca';
    protected $primaryKey = 'id_super_finca';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado',
    ];

    public function fincas()
    {
        return $this->hasMany('\yura\Modelos\ConfiguracionEmpresa', 'id_super_finca');
    }
}