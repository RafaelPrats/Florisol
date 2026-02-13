<?php

Route::get('clasificaciones', 'Postcosecha\ClasificacionesController@inicio');
Route::get('clasificaciones/listar_ramos', 'Postcosecha\ClasificacionesController@listar_ramos');
Route::get('clasificaciones/listar_presentaciones', 'Postcosecha\ClasificacionesController@listar_presentaciones');
Route::post('clasificaciones/store_ramo', 'Postcosecha\ClasificacionesController@store_ramo');
Route::post('clasificaciones/store_presentacion', 'Postcosecha\ClasificacionesController@store_presentacion');
Route::post('clasificaciones/update_ramo', 'Postcosecha\ClasificacionesController@update_ramo');
Route::post('clasificaciones/update_presentacion', 'Postcosecha\ClasificacionesController@update_presentacion');
Route::post('clasificaciones/cambiar_estado_ramo', 'Postcosecha\ClasificacionesController@cambiar_estado_ramo');
Route::post('clasificaciones/cambiar_estado_presentacion', 'Postcosecha\ClasificacionesController@cambiar_estado_presentacion');
Route::get('clasificaciones/listar_cajas', 'Postcosecha\ClasificacionesController@listar_cajas');
Route::post('clasificaciones/store_caja', 'Postcosecha\ClasificacionesController@store_caja');
Route::post('clasificaciones/update_caja', 'Postcosecha\ClasificacionesController@update_caja');
Route::post('clasificaciones/cambiar_estado_caja', 'Postcosecha\ClasificacionesController@cambiar_estado_caja');
