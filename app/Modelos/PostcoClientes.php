<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class PostcoClientes extends Model
{
    protected $table = 'postco_clientes';
    protected $primaryKey = 'id_postco_clientes';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_postco',
        'id_cliente',
        'cantidad',
    ];

    public function postco()
    {
        return $this->belongsTo('\yura\Modelos\Postco', 'id_postco');
    }

    public function cliente()
    {
        return $this->belongsTo('\yura\Modelos\Cliente', 'id_cliente');
    }
}
