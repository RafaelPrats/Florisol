<?php

Route::get('ot_postco', 'Postco\ListadoOtController@inicio');
Route::get('ot_postco/listar_reporte', 'Postco\ListadoOtController@listar_reporte');
Route::post('ot_postco/update_despachador', 'Postco\ListadoOtController@update_despachador');
Route::get('ot_postco/exportar_orden_trabajo_pdf', 'Postco\ListadoOtController@exportar_orden_trabajo_pdf');
Route::post('ot_postco/despachar_orden_trabajo', 'Postco\ListadoOtController@despachar_orden_trabajo');
Route::post('ot_postco/store_armar', 'Postco\ListadoOtController@store_armar');
Route::get('ot_postco/modal_reclamos', 'Postco\ListadoOtController@modal_reclamos');
Route::post('ot_postco/store_reclamo', 'Postco\ListadoOtController@store_reclamo');
Route::post('ot_postco/update_reclamo', 'Postco\ListadoOtController@update_reclamo');
Route::post('ot_postco/eliminar_reclamo', 'Postco\ListadoOtController@eliminar_reclamo');
Route::post('ot_postco/update_observacion', 'Postco\ListadoOtController@update_observacion');
