<?php

Route::get('orden_trabajo', 'OrdenTrabajoController@inicio');
Route::get('orden_trabajo/listar_reporte', 'OrdenTrabajoController@listar_reporte');
Route::post('orden_trabajo/despachar_orden_trabajo', 'OrdenTrabajoController@despachar_orden_trabajo');
Route::post('orden_trabajo/eliminar_orden_trabajo', 'Postcosecha\IngresoClasificacionController@eliminar_orden_trabajo');
Route::post('orden_trabajo/store_armar', 'OrdenTrabajoController@store_armar');
Route::post('orden_trabajo/update_despachador', 'OrdenTrabajoController@update_despachador');
