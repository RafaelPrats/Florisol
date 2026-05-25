<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class Segmento extends Model
{
    protected $table = 'segmento';
    protected $primaryKey = 'id_segmento';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'bodega',
    ];
}
