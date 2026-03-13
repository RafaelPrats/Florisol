<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class RenovarOrdenFija extends Model
{
    protected $table = 'renovar_orden_fija';
    protected $primaryKey = 'id_renovar_orden_fija';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'orden_fija',
        'renovacion',
    ];
}
