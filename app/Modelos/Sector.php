<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Sector extends Model
{
    protected $table = 'sector';
    protected $primaryKey = 'id_sector';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_sector',
        'nombre',   // unico
        'fecha_registro',
        'estado',
        'descripcion',
        'id_empresa',
    ];

    public function modulos()
    {
        return $this->hasMany('\yura\Modelos\Modulo', 'id_sector');
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }

    public function modulos_activos()
    {
        return $this->hasMany('\yura\Modelos\Modulo', 'id_sector')->where('estado', '=', 1);
    }

    public function getAreaTotal()
    {
        return DB::table('modulo')
            ->select(DB::raw('sum(area) as cant'))
            ->where('estado', '=', 1)
            ->where('id_sector', '=', $this->id_sector)
            ->get()[0]->cant;
    }
}