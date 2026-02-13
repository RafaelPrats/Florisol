<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class Cama extends Model
{
    protected $table = 'cama';
    protected $primaryKey = 'id_cama';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',   // unico
        'fecha_registro',
        'estado',
        'area_trabajo', // PLANTAS MADRES, ENRAIZAMIENTO, CONFINAMIENTO
        'id_empresa',
    ];

    public function ciclos()
    {
        return $this->hasMany('\yura\Modelos\CicloCama', 'id_cama');
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }

    public function ciclo_actual()
    {
        foreach ($this->ciclos as $c)
            if ($c->activo == 1)
                return $c;
        return '';
    }
}
