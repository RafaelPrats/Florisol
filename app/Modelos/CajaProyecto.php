<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class CajaProyecto extends Model
{
    protected $table = 'caja_proyecto';
    protected $primaryKey = 'id_caja_proyecto';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_proyecto',
        'cantidad',
        'tipo_caja',
    ];

    public function proyecto()
    {
        return $this->belongsTo('\yura\Modelos\Proyecto', 'id_proyecto');
    }

    public function detalles()
    {
        return $this->hasMany('\yura\Modelos\DetalleCajaProyecto', 'id_caja_proyecto');
    }

    public function marcaciones()
    {
        return $this->hasMany('\yura\Modelos\CajaProyectoMarcacion', 'id_caja_proyecto');
    }
}
