<?php

Route::get('preproduccion', 'Postco\PreproduccionController@inicio');
Route::get('preproduccion/listar_reporte', 'Postco\PreproduccionController@listar_reporte');
Route::get('preproduccion/modal_receta', 'Postco\PreproduccionController@modal_receta');
Route::post('preproduccion/armar_ramos', 'Postco\PreproduccionController@armar_ramos');
Route::get('preproduccion/admin_receta', 'Postco\PreproduccionController@admin_receta');
Route::post('preproduccion/store_ot', 'Postco\PreproduccionController@store_ot');
Route::get('preproduccion/cargar_receta', 'Postco\PreproduccionController@cargar_receta');
Route::post('preproduccion/store_distribucion_receta', 'Postco\PreproduccionController@store_distribucion_receta');
Route::get('preproduccion/listar_ordenes_trabajo', 'Postco\PreproduccionController@listar_ordenes_trabajo');
Route::post('preproduccion/update_despachador', 'Postco\PreproduccionController@update_despachador');
Route::post('preproduccion/eliminar_orden_trabajo', 'Postco\PreproduccionController@eliminar_orden_trabajo');
Route::get('preproduccion/exportar_orden_trabajo', 'Postco\PreproduccionController@exportar_orden_trabajo');
Route::post('preproduccion/copiar_receta', 'Postco\PreproduccionController@copiar_receta');
Route::get('preproduccion/exportar_reporte', 'Postco\PreproduccionController@exportar_reporte');
Route::post('preproduccion/bloquear_postco', 'Postco\PreproduccionController@bloquear_postco');
Route::post('preproduccion/store_oa', 'Postco\PreproduccionController@store_oa');
Route::get('preproduccion/listar_ordenes_alistamiento', 'Postco\PreproduccionController@listar_ordenes_alistamiento');
Route::post('preproduccion/update_despachador_oa', 'Postco\PreproduccionController@update_despachador_oa');
Route::post('preproduccion/eliminar_orden_alistamiento', 'Postco\PreproduccionController@eliminar_orden_alistamiento');
Route::post('preproduccion/convertir_ot', 'Postco\PreproduccionController@convertir_ot');
Route::get('preproduccion/buscar_variedades', 'Postco\PreproduccionController@buscar_variedades');
