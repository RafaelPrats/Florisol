<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class UsoChatbot extends Model
{
    protected $table = 'uso_chatbot';
    protected $primaryKey = 'id_uso_chatbot';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'pregunta',
        'respuesta',
        'fecha_registro',
        'consulta_sql',
    ];

    public function usuario()
    {
        return $this->belongsTo('\yura\Modelos\Usuario', 'id_usuario');
    }
}
