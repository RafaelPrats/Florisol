<?php

Route::get('ingreso_flor_nacional', 'Postco\IngresoFlorNacionalController@inicio');
Route::get('ingreso_flor_nacional/listar_reporte', 'Postco\IngresoFlorNacionalController@listar_reporte');
Route::get('ingreso_flor_nacional/modal_motivos', 'Postco\IngresoFlorNacionalController@modal_motivos');
Route::post('ingreso_flor_nacional/store_motivos', 'Postco\IngresoFlorNacionalController@store_motivos');
Route::post('ingreso_flor_nacional/update_motivo', 'Postco\IngresoFlorNacionalController@update_motivo');
Route::get('ingreso_flor_nacional/modal_nacional', 'Postco\IngresoFlorNacionalController@modal_nacional');
Route::post('ingreso_flor_nacional/store_flor_nacional', 'Postco\IngresoFlorNacionalController@store_flor_nacional');
Route::post('ingreso_flor_nacional/update_flor_nacional', 'Postco\IngresoFlorNacionalController@update_flor_nacional');
Route::post('ingreso_flor_nacional/delete_flor_nacional', 'Postco\IngresoFlorNacionalController@delete_flor_nacional');
Route::get('ingreso_flor_nacional/modal_fincas', 'Postco\IngresoFlorNacionalController@modal_fincas');
Route::post('ingreso_flor_nacional/store_fincas', 'Postco\IngresoFlorNacionalController@store_fincas');
Route::post('ingreso_flor_nacional/update_finca', 'Postco\IngresoFlorNacionalController@update_finca');
Route::post('ingreso_flor_nacional/cambiar_estado_finca', 'Postco\IngresoFlorNacionalController@cambiar_estado_finca');
