<?php

Route::get('ingreso_inventario', 'Postco\InventarioRecepcionController@inicio');
Route::get('ingreso_inventario/listar_reporte', 'Postco\InventarioRecepcionController@listar_reporte');
Route::get('ingreso_inventario/modal_add', 'Postco\InventarioRecepcionController@modal_add');
Route::post('ingreso_inventario/store_inventario', 'Postco\InventarioRecepcionController@store_inventario');
Route::post('ingreso_inventario/recibir_pendientes', 'Postco\InventarioRecepcionController@recibir_pendientes');
Route::post('ingreso_inventario/recibir_all_pendientes', 'Postco\InventarioRecepcionController@recibir_all_pendientes');