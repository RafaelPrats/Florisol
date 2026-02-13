<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;
use yura\Modelos\PersonalDetalle;

class Personal extends Model
{

    protected $table = 'personal';
    protected $primaryKey = 'id_personal';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'apellido',
        'cedula_identidad',
        'id_sexo',
        'fecha_nacimiento',
        'id_nacionalidad',
    ];

    public function detalles()
    {
        return $this->hasMany('\yura\Modelos\PersonalDetalle', 'id_personal');
    }

    public function getDetalleActivo(){
        return PersonalDetalle::All()
            ->where('id_personal', $this->id_personal)
            ->where('estado', 1);
    }

    public function getDetalleActivoPersonal(){
        return Personal::All()
            ->where('id_personal', $this->id_personal)
            ->where('estado', 1)
            ->first();
    }


    public function getDetalleActivoDesin(){
        return PersonalDetalle::All()
            ->where('id_personal', $this->id_personal)
            ->where('estado', 1)
            ->first();
    }
    public function getDetalleInactivo(){
        return PersonalDetalle::All()
            ->where('id_personal', $this->id_personal)
            ->where('estado', 0);
    }
   


    public function scopeBuscarpor($query,$estado,$busqueda_personal) {
    	if ( ($estado) && ($busqueda_personal)  ) {
    		return $query->where($estado,'like','%'.$busqueda_personal.'%')->get();
        }else
        return [];
    }

    public function scopeBuscarporFecha($query, $busqueda_fecha) {
    	if ($busqueda_fecha)  {
    		return $query->where('like','%'.$busqueda_fecha.'%')->get();
        }else
        return [];
    }

  public function eliminarPersonal()
    {
        foreach ($this->detalles as $d) {
            $d->delete();
        }
    }

}

