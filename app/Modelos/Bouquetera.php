<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class Bouquetera extends Model
{
    protected $table = 'bouquetera';
    protected $primaryKey = 'id_bouquetera';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'fecha_registro',
        'id_planta',
        'id_variedad',
        'tallos',
        'precio',
        'fecha',
        'id_empresa',
        'exportada',
    ];

    public function planta()
    {
        return $this->belongsTo('\yura\Modelos\Planta', 'id_planta');
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function id_empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }
}
