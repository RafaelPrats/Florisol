<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class FincaFlorNacional extends Model
{
    protected $table = 'finca_flor_nacional';
    protected $primaryKey = 'id_finca_flor_nacional';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'nombre',
        'estado',
    ];
}
