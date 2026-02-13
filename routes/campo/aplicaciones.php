<?php

Route::get('aplicaciones_campo', 'Campo\AplicacionesController@inicio');
Route::get('aplicaciones_campo/buscar_listado', 'Campo\AplicacionesController@buscar_listado');
Route::post('aplicaciones_campo/store_app', 'Campo\AplicacionesController@store_app');
Route::get('aplicaciones_campo/get_row_listado', 'Campo\AplicacionesController@get_row_listado');
Route::post('aplicaciones_campo/update_app', 'Campo\AplicacionesController@update_app');
Route::post('aplicaciones_campo/desactivar_app', 'Campo\AplicacionesController@desactivar_app');
Route::get('aplicaciones_campo/mezclas_app', 'Campo\AplicacionesController@mezclas_app');
Route::get('aplicaciones_campo/detalles_app', 'Campo\AplicacionesController@detalles_app');
Route::post('aplicaciones_campo/store_detalle_app', 'Campo\AplicacionesController@store_detalle_app');
Route::post('aplicaciones_campo/delete_det_app', 'Campo\AplicacionesController@delete_det_app');
Route::get('aplicaciones_campo/parametrizar_det', 'Campo\AplicacionesController@parametrizar_det');
Route::post('aplicaciones_campo/store_parametro', 'Campo\AplicacionesController@store_parametro');
Route::post('aplicaciones_campo/delete_par', 'Campo\AplicacionesController@delete_par');
Route::get('aplicaciones_campo/parametrizar_app', 'Campo\AplicacionesController@parametrizar_app');
Route::post('aplicaciones_campo/store_parametro_app', 'Campo\AplicacionesController@store_parametro_app');
Route::post('aplicaciones_campo/delete_par_app', 'Campo\AplicacionesController@delete_par_app');
Route::get('aplicaciones_campo/variedades_app', 'Campo\AplicacionesController@variedades_app');
Route::post('aplicaciones_campo/seleccionar_app_variedad', 'Campo\AplicacionesController@seleccionar_app_variedad');
Route::get('aplicaciones_campo/add_matriz', 'Campo\AplicacionesController@add_matriz');
Route::post('aplicaciones_campo/store_mezcla', 'Campo\AplicacionesController@store_mezcla');
Route::post('aplicaciones_campo/update_mezcla', 'Campo\AplicacionesController@update_mezcla');
Route::post('aplicaciones_campo/delete_mezcla', 'Campo\AplicacionesController@delete_mezcla');