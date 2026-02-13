<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class Guarde extends Model
{
    protected $table = 'guarde';
    protected $primaryKey = 'id_guarde';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_variedad',
        'mallas',
        'tallos_x_malla',
        'fecha',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }
}
