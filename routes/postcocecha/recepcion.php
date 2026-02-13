<?php

Route::get('recepcion', 'RecepcionController@inicio');
Route::get('recepcion/listar_reporte', 'RecepcionController@listar_reporte');
Route::post('recepcion/seleccionar_finca_origen', 'RecepcionController@seleccionar_finca_origen');
Route::post('recepcion/buscar_inventario', 'RecepcionController@buscar_inventario');
Route::post('recepcion/store_recepcion_planta', 'RecepcionController@store_recepcion_planta');
Route::get('recepcion/listar_inventario_planta', 'RecepcionController@listar_inventario_planta');
Route::post('recepcion/update_inventario', 'RecepcionController@update_inventario');
Route::post('recepcion/delete_inventario', 'RecepcionController@delete_inventario');
Route::get('recepcion/view_pdf_inventario', 'RecepcionController@view_pdf_inventario');
Route::get('recepcion/view_all_pdf_inventario', 'RecepcionController@view_all_pdf_inventario');
Route::get('recepcion/modal_scan', 'RecepcionController@modal_scan');
Route::get('recepcion/escanear_codigo', 'RecepcionController@escanear_codigo');
Route::post('recepcion/store_despachos', 'RecepcionController@store_despachos');
