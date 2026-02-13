<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;
use yura\Modelos\Personal;


class PersonalDetalle extends Model
{protected $table = 'personal_detalle';
    protected $primaryKey = 'id_personal_detalle';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_personal_detalle',
        'id_personal',
        'estado',
        'id_departamento',
        'id_estado_civil',
        'id_causa_desviculacion',
        'telef',
        'cargas_familiares',
        'lugar_residencia',
        'direccion',
        'correo',
        'discapacidad', //char 1: S, N
        'porcentaje_discapacidad',
        'id_cargo',
        'fecha_desvinculacion',
        'fecha_ingreso',
        'id_tipo_rol',
        'id_tipo_pago',
        'numero_cuenta',
        'sueldo',
        'id_banco',
        'id_grado_instruccion',
        'id_nacionalidad',
        'id_sucursal',
        'id_grupo',
        'id_grupo_interno',
        'id_area',
        'id_actividad',
        'id_mano_obra',
        'id_plantilla',
        'id_tipo_cuenta',
        'id_relacion_laboral',
        'id_detalle_contrato',
        'n_afiliacion',
        'id_seguro'
    ];

    public function causa_desvinculacion()
    {
        return $this->belongsTo('\yura\Modelos\CausaDesvinculacion', 'id_causa_desvinculacion');
    }

    public function personal()
    {
        return $this->belongsTo('\yura\Modelos\Personal', 'id_personal');
    }


}
