<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class SeasonPropuesta extends Model
{
    protected $table = 'season_propuesta';
    protected $primaryKey = 'id_season_propuesta';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_propuesta',
        'nombre',
    ];

    public function propuesta()
    {
        return $this->belongsTo('\yura\Modelos\Propuesta', 'id_propuesta');
    }
}
