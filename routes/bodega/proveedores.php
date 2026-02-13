<?php

Route::get('proveedores', 'Bodega\ProveedoresController@inicio');
Route::get('proveedores/listar_reporte', 'Bodega\ProveedoresController@listar_reporte');
Route::post('proveedores/update_proveedor', 'Bodega\ProveedoresController@update_proveedor');
Route::post('proveedores/cambiar_estado_proveedor', 'Bodega\ProveedoresController@cambiar_estado_proveedor');
Route::post('proveedores/store_proveedor', 'Bodega\ProveedoresController@store_proveedor');
