<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class MotivoFlorNacional extends Model
{
    protected $table = 'motivo_flor_nacional';
    protected $primaryKey = 'id_motivo_flor_nacional';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado',
    ];
}
