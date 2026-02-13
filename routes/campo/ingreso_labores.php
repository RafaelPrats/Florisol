<?php

Route::get('ingreso_labores', 'Campo\LaboresCampoController@inicio');
Route::get('ingreso_labores/listar_labores', 'Campo\LaboresCampoController@listar_labores');
Route::post('ingreso_labores/store_labor', 'Campo\LaboresCampoController@store_labor');
Route::post('ingreso_labores/update_labor', 'Campo\LaboresCampoController@update_labor');
Route::post('ingreso_labores/delete_labor', 'Campo\LaboresCampoController@delete_labor');
Route::get('ingreso_labores/add_adicional', 'Campo\LaboresCampoController@add_adicional');
Route::get('ingreso_labores/ver_labores_by_ciclo', 'Campo\LaboresCampoController@ver_labores_by_ciclo');
Route::post('ingreso_labores/seleccionar_modulo', 'Campo\LaboresCampoController@seleccionar_modulo');
Route::post('ingreso_labores/seleccionar_labor', 'Campo\LaboresCampoController@seleccionar_labor');
Route::post('ingreso_labores/store_adicional', 'Campo\LaboresCampoController@store_adicional');
Route::get('ingreso_labores/aplicar_mezclas', 'Campo\LaboresCampoController@aplicar_mezclas');
Route::get('ingreso_labores/seleccionar_mezcla', 'Campo\LaboresCampoController@seleccionar_mezcla');
Route::post('ingreso_labores/store_mezclas', 'Campo\LaboresCampoController@store_mezclas');
Route::get('ingreso_labores/exportar_reporte', 'Campo\LaboresCampoController@exportar_reporte');
Route::post('ingreso_labores/update_aplicacion', 'Campo\LaboresCampoController@update_aplicacion');
Route::post('ingreso_labores/delete_aplicacion', 'Campo\LaboresCampoController@delete_aplicacion');
Route::post('ingreso_labores/seleccionar_tipo_labor', 'Campo\LaboresCampoController@seleccionar_tipo_labor');
Route::post('ingreso_labores/store_all_labor', 'Campo\LaboresCampoController@store_all_labor');
Route::post('ingreso_labores/duplicar_labor', 'Campo\LaboresCampoController@duplicar_labor');