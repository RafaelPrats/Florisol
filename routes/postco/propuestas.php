<?php

Route::get('propuestas', 'Postco\PropuestaController@inicio');
Route::get('propuestas/listar_reporte', 'Postco\PropuestaController@listar_reporte');
Route::get('propuestas/add_propuesta', 'Postco\PropuestaController@add_propuesta');
Route::post('propuestas/store_propuesta', 'Postco\PropuestaController@store_propuesta');
Route::post('propuestas/delete_propuesta', 'Postco\PropuestaController@delete_propuesta');
Route::get('propuestas/editar_propuesta', 'Postco\PropuestaController@editar_propuesta');
Route::post('propuestas/update_propuesta', 'Postco\PropuestaController@update_propuesta');
Route::get('propuestas/abrirGaleria', 'Postco\PropuestaController@abrirGaleria');
