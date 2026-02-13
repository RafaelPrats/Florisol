<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class SemanaEmpresa extends Model
{
    protected $table = 'semana_empresa';
    protected $primaryKey = 'id_semana_empresa';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_empresa',
        'id_semana',
        'plantas_iniciales',
        'densidad',
    ];

    public function semana()
    {
        return $this->belongsTo('\yura\Modelos\Semana', 'id_semana');
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }
}
