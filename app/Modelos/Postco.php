<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Postco extends Model
{
    protected $table = 'postco';
    protected $primaryKey = 'id_postco';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_variedad',
        'fecha',
        'longitud',
        'ramos',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function distribuciones()
    {
        return $this->hasMany('\yura\Modelos\DistribucionPostco', 'id_postco');
    }

    public function ordenes_trabajo()
    {
        return $this->hasMany('\yura\Modelos\OtPostco', 'id_postco');
    }

    public function clientes()
    {
        return $this->hasMany('\yura\Modelos\PostcoClientes', 'id_postco');
    }

    public function getRamosOt()
    {
        $r = DB::table('ot_postco')
            ->select(DB::raw('sum(ramos) as cantidad'))
            ->where('id_postco', $this->id_postco)
            ->get()[0]->cantidad;
        return $r > 0 ? $r : 0;
    }

    public function getRamosOtByCliente($cliente)
    {
        return DB::table('ot_postco')
            ->select(DB::raw('sum(ramos) as cantidad'))
            ->where('id_postco', $this->id_postco)
            ->where('id_cliente', $cliente)
            ->get()[0]->cantidad;
    }

    public function getRamosByCliente($cliente)
    {
        return DB::table('postco_clientes')
            ->select(DB::raw('sum(cantidad) as cantidad'))
            ->where('id_postco', $this->id_postco)
            ->where('id_cliente', $cliente)
            ->get()[0]->cantidad;
    }

    public function getRamosOa()
    {
        $r = DB::table('oa_postco')
            ->select(DB::raw('sum(ramos) as cantidad'))
            ->where('id_postco', $this->id_postco)
            ->get()[0]->cantidad;
        return $r > 0 ? $r : 0;
    }
}
