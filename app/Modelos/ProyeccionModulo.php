<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ProyeccionModulo extends Model
{
    protected $table = 'proyeccion_modulo';
    protected $primaryKey = 'id_proyeccion_modulo';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_modulo',
        'id_semana',
        'fecha_registro',
        'estado',
        'id_variedad',
        'tipo', // Poda, Siembra, Cerrado
        'curva',
        'semana_poda_siembra',
        'poda_siembra', // numero de poda o 0 si es siembra
        'plantas_iniciales',
        'desecho',
        'tallos_planta',
        'tallos_ramo',
        'fecha_inicio',
        'id_empresa',
    ];

    public function modulo()
    {
        return $this->belongsTo('\yura\Modelos\Modulo', 'id_modulo');
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }

    public function semana()
    {
        return $this->belongsTo('\yura\Modelos\Semana', 'id_semana');
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function restaurar_proyecciones()
    {
        $sum_semana = intval($this->semana_poda_siembra) + intval(count(explode('-', $this->curva)));
        $sem_next_proy = getSemanaByDateVariedad(opDiasFecha('+', $sum_semana * 7, $this->fecha_inicio), $this->id_variedad);

        $next = ProyeccionModulo::All()
            ->where('estado', 1)
            ->where('id_modulo', $this->id_modulo)
            ->where('fecha_inicio', '>', $this->fecha_inicio);

        if (count($next) > 0) {
            if ($sem_next_proy != '') {
                $proy = new ProyeccionModulo();
                $proy->id_modulo = $this->id_modulo;
                $proy->id_semana = $sem_next_proy->id_semana;
                $proy->id_variedad = $this->id_variedad;
                $proy->tipo = 'P';
                $proy->curva = $this->curva;
                $proy->semana_poda_siembra = $this->semana_poda_siembra;
                $proy->poda_siembra = $this->poda_siembra + 1;
                $proy->plantas_iniciales = $this->plantas_iniciales != '' ? $this->plantas_iniciales : 0;
                $proy->desecho = $this->desecho;
                $proy->tallos_planta = $this->tallos_planta != '' ? $this->tallos_planta : 0;
                $proy->tallos_ramo = $sem_next_proy->tallos_ramo_poda != '' ? $sem_next_proy->tallos_ramo_poda : 0;
                $proy->fecha_inicio = $sem_next_proy->fecha_final;

                $proy->save();
            }
        }

        foreach ($next as $proy) {
            $proy->delete();
        }
    }

    public function last_ciclo()
    {
        $last_ciclo = Ciclo::All()
            ->where('estado', 1)
            ->where('id_modulo', $this->id_modulo)
            ->sortByDesc('fecha_inicio')
            ->first();

        return $last_ciclo;
    }
}
