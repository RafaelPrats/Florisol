<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Propuesta extends Model
{
    protected $table = 'propuesta';
    protected $primaryKey = 'id_propuesta';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'imagen',
        'precio',
        'nombre',
        'packing',
    ];

    public function detalles()
    {
        return $this->hasMany('\yura\Modelos\DetallePropuesta', 'id_propuesta');
    }

    public function colores()
    {
        return $this->hasMany('\yura\Modelos\ColorPropuesta', 'id_propuesta');
    }

    public function seasons()
    {
        return $this->hasMany('\yura\Modelos\SeasonPropuesta', 'id_propuesta');
    }

    public function clientes()
    {
        return $this->hasMany('\yura\Modelos\ClientePropuesta', 'id_propuesta');
    }

    public function cajas()
    {
        return $this->hasMany('\yura\Modelos\CajaPropuesta', 'id_propuesta');
    }

    public function getTotalTallos()
    {
        return DB::table('detalle_propuesta')
            ->select(DB::raw('sum(unidades) as cant'))
            ->where('id_propuesta', $this->id_propuesta)
            ->get()[0]->cant;
    }

    public function getPrecio()
    {
        return DB::table('detalle_propuesta')
            ->select(DB::raw('sum(precio * unidades) as cant'))
            ->where('id_propuesta', $this->id_propuesta)
            ->get()[0]->cant;
    }
}
