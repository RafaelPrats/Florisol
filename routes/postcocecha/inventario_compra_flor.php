<?php

Route::get('inventario_compra_flor', 'InventarioCompraFlorController@inicio');
Route::get('inventario_compra_flor/listar_inventario_compra_flor', 'InventarioCompraFlorController@listar_inventario_compra_flor');
Route::get('inventario_compra_flor/listar_inventario_compra_flor_acumulado', 'InventarioCompraFlorController@listar_inventario_compra_flor_acumulado');
Route::get('inventario_compra_flor/detalle_ventas', 'InventarioCompraFlorController@detalle_ventas');
Route::post('inventario_compra_flor/confirmar_compra', 'InventarioCompraFlorController@confirmar_compra');
Route::get('inventario_compra_flor/prorrogar_compra', 'InventarioCompraFlorController@prorrogar_compra');
Route::post('inventario_compra_flor/store_prorroga', 'InventarioCompraFlorController@store_prorroga');
Route::post('inventario_compra_flor/update_compra', 'InventarioCompraFlorController@update_compra');
Route::get('inventario_compra_flor/exportar_listado_compra_flor', 'InventarioCompraFlorController@exportar_listado_compra_flor');
Route::get('inventario_compra_flor/exportar_listado_compra_flor_acumulado', 'InventarioCompraFlorController@exportar_listado_compra_flor_acumulado');
Route::get('inventario_compra_flor/exportar_archivo_compras', 'InventarioCompraFlorController@exportar_archivo_compras');
Route::post('inventario_compra_flor/refrescar_ventas', 'InventarioCompraFlorController@refrescar_ventas');
Route::post('inventario_compra_flor/refrescar_all_ventas', 'InventarioCompraFlorController@refrescar_all_ventas');
Route::get('inventario_compra_flor/actualizar_variedad', 'InventarioCompraFlorController@actualizar_variedad');
Route::get('inventario_compra_flor/get_thead_acumulado', 'InventarioCompraFlorController@get_thead_acumulado');
Route::post('inventario_compra_flor/store_compra_parcial', 'InventarioCompraFlorController@store_compra_parcial');
