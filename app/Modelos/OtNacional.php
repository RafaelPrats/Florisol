<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class OtNacional extends Model
{
    protected $table = 'ot_nacional';
    protected $primaryKey = 'id_ot_nacional';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_detalle_caja_proyecto',
        'distribucion_pos',
        'id_variedad_dist',
        'unidades_dist',
        'total_tallos_dist',
        'id_variedad',
        'longitud_dist',
        'longitud',
        'tallos',
        'id_usuario',
        'fecha',
    ];

    public function detalle_caja_proyecto()
    {
        return $this->belongsTo('\yura\Modelos\DetalleCajaProyecto', 'id_detalle_caja_proyecto');
    }

    public function variedad_dist()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad_dist');
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function usuario()
    {
        return $this->belongsTo('\yura\Modelos\Usuario', 'id_usuario');
    }
}
