<?php

Route::get('enraizamiento', 'Propagacion\EnraizamientoController@inicio');
Route::get('enraizamiento/listar_enraizamientos', 'Propagacion\EnraizamientoController@listar_enraizamientos');
Route::post('enraizamiento/store_enraizamiento', 'Propagacion\EnraizamientoController@store_enraizamiento');
Route::post('enraizamiento/buscar_enraizamiento_semanal', 'Propagacion\EnraizamientoController@buscar_enraizamiento_semanal');
Route::post('enraizamiento/update_enraizamiento', 'Propagacion\EnraizamientoController@update_enraizamiento');
Route::post('enraizamiento/update_detalle_enraizamiento', 'Propagacion\EnraizamientoController@update_detalle_enraizamiento');
Route::post('enraizamiento/delete_detalle_enraizamiento', 'Propagacion\EnraizamientoController@delete_detalle_enraizamiento');
