<?php

Route::get('compra_flor', 'CompraFlorController@inicio');
Route::get('compra_flor/listar_reporte', 'CompraFlorController@listar_reporte');
Route::post('compra_flor/seleccionar_finca_origen', 'CompraFlorController@seleccionar_finca_origen');
Route::post('compra_flor/buscar_inventario', 'CompraFlorController@buscar_inventario');
Route::post('compra_flor/store_compra_flor_planta', 'CompraFlorController@store_compra_flor_planta');
Route::get('compra_flor/listar_inventario_planta', 'CompraFlorController@listar_inventario_planta');
Route::post('compra_flor/update_inventario', 'CompraFlorController@update_inventario');
Route::post('compra_flor/delete_inventario', 'CompraFlorController@delete_inventario');
Route::get('compra_flor/importar_compras', 'CompraFlorController@importar_compras');
Route::post('compra_flor/post_importar_compras', 'CompraFlorController@post_importar_compras');
Route::get('compra_flor/get_importar_compras', 'CompraFlorController@get_importar_compras');
Route::post('compra_flor/store_import_compras', 'CompraFlorController@store_import_compras');
