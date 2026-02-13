<?php

Route::get('ingreso_clasificacion', 'Postcosecha\IngresoClasificacionController@inicio');
Route::get('ingreso_clasificacion/listar_reporte', 'Postcosecha\IngresoClasificacionController@listar_reporte');
Route::get('ingreso_clasificacion/armar_combinacion', 'Postcosecha\IngresoClasificacionController@armar_combinacion');
Route::post('ingreso_clasificacion/store_armar_pedido', 'Postcosecha\IngresoClasificacionController@store_armar_pedido');
Route::get('ingreso_clasificacion/exportar_excel_fecha', 'Postcosecha\IngresoClasificacionController@exportar_excel_fecha');
Route::get('ingreso_clasificacion/admin_receta', 'Postcosecha\IngresoClasificacionController@admin_receta');
Route::get('ingreso_clasificacion/exportar_receta', 'Postcosecha\IngresoClasificacionController@exportar_receta');
Route::get('ingreso_clasificacion/listar_ordenes_trabajo', 'Postcosecha\IngresoClasificacionController@listar_ordenes_trabajo');
Route::post('ingreso_clasificacion/eliminar_orden_trabajo', 'Postcosecha\IngresoClasificacionController@eliminar_orden_trabajo');
Route::get('ingreso_clasificacion/exportar_orden_trabajo', 'Postcosecha\IngresoClasificacionController@exportar_orden_trabajo');
Route::post('ingreso_clasificacion/store_armar_ramos', 'Postcosecha\IngresoClasificacionController@store_armar_ramos');
Route::post('ingreso_clasificacion/confirmar_pedido', 'Postcosecha\IngresoClasificacionController@confirmar_pedido');
Route::post('ingreso_clasificacion/bloquear_distribucion', 'Postcosecha\IngresoClasificacionController@bloquear_distribucion');
Route::post('ingreso_clasificacion/copiar_distribucion', 'Postcosecha\IngresoClasificacionController@copiar_distribucion');
Route::post('ingreso_clasificacion/update_despachador', 'Postcosecha\IngresoClasificacionController@update_despachador');
Route::post('ingreso_clasificacion/confirmar_ramos', 'Postcosecha\IngresoClasificacionController@confirmar_ramos');
Route::get('ingreso_clasificacion/dividir_receta', 'Postcosecha\IngresoClasificacionController@dividir_receta');
Route::post('ingreso_clasificacion/store_dividir_receta', 'Postcosecha\IngresoClasificacionController@store_dividir_receta');
Route::get('ingreso_clasificacion/listar_pre_ordenes_trabajo', 'Postcosecha\IngresoClasificacionController@listar_pre_ordenes_trabajo');
Route::post('ingreso_clasificacion/eliminar_pre_orden_trabajo', 'Postcosecha\IngresoClasificacionController@eliminar_pre_orden_trabajo');
Route::post('ingreso_clasificacion/convertir_a_orden_trabajo', 'Postcosecha\IngresoClasificacionController@convertir_a_orden_trabajo');
Route::get('ingreso_clasificacion/editar_preorden', 'Postcosecha\IngresoClasificacionController@editar_preorden');
Route::post('ingreso_clasificacion/store_distribucion_pre_orden', 'Postcosecha\IngresoClasificacionController@store_distribucion_pre_orden');
Route::post('ingreso_clasificacion/convertir_parcial', 'Postcosecha\IngresoClasificacionController@convertir_parcial');
Route::get('ingreso_clasificacion/exportar_orden_trabajo_pdf', 'Postcosecha\IngresoClasificacionController@exportar_orden_trabajo_pdf');
