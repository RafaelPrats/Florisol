<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class AcumuladosAnno extends Model
{
    protected $table = 'acumulados_anno';
    protected $primaryKey = 'id_acumulados_anno';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_planta',
        'id_empresa',
        'semana',
    ];

    public function planta()
    {
        return $this->belongsTo('\yura\Modelos\Planta', 'id_planta');
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\Planta', 'id_empresa');
    }

    public function semana()
    {
        return getObjSemana($this->semana);
    }
}
