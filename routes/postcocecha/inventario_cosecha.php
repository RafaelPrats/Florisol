<?php

Route::get('inventario_cosecha', 'InventarioCosechaController@inicio');
Route::get('inventario_cosecha/listar_inventario_cosecha', 'InventarioCosechaController@listar_inventario_cosecha');
Route::get('inventario_cosecha/listar_inventario_cosecha_acumulado', 'InventarioCosechaController@listar_inventario_cosecha_acumulado');
Route::post('inventario_cosecha/sacar_inventario', 'InventarioCosechaController@sacar_inventario');
Route::post('inventario_cosecha/sacar_all_inventario', 'InventarioCosechaController@sacar_all_inventario');
Route::get('inventario_cosecha/detalle_ventas', 'InventarioCosechaController@detalle_ventas');
Route::post('inventario_cosecha/store_devolucion', 'InventarioCosechaController@store_devolucion');
Route::get('inventario_cosecha/exportar_listado_acumulado', 'InventarioCosechaController@exportar_listado_acumulado');
