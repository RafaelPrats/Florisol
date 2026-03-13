<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class CajaProyectoMarcacion extends Model
{
    protected $table = 'caja_proyecto_marcacion';
    protected $primaryKey = 'id_caja_proyecto_marcacion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_caja_proyecto',
        'id_dato_exportacion',
        'valor',
    ];

    public function caja_proyecto()
    {
        return $this->belongsTo('\yura\Modelos\CajaProyecto', 'id_caja_proyecto');
    }

    public function dato_exportacion()
    {
        return $this->belongsTo('\yura\Modelos\DatosExportacion', 'id_dato_exportacion');
    }
}
