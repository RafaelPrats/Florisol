<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ColorPropuesta extends Model
{
    protected $table = 'color_propuesta';
    protected $primaryKey = 'id_color_propuesta';
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
