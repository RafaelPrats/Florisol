<?php

Route::get('motivos_reclamos', 'Postco\MotivosReclamosController@inicio');
Route::get('motivos_reclamos/listar_reporte', 'Postco\MotivosReclamosController@listar_reporte');
Route::post('motivos_reclamos/store_motivo', 'Postco\MotivosReclamosController@store_motivo');
Route::post('motivos_reclamos/update_motivo', 'Postco\MotivosReclamosController@update_motivo');
Route::post('motivos_reclamos/cambiar_estado_motivo', 'Postco\MotivosReclamosController@cambiar_estado_motivo');