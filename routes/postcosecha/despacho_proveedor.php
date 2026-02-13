<?php

Route::get('despacho_proveedor', 'Postcosecha\DespachoProveedorController@inicio');
Route::get('despacho_proveedor/listar_reporte', 'Postcosecha\DespachoProveedorController@listar_reporte');
Route::get('despacho_proveedor/add_despacho', 'Postcosecha\DespachoProveedorController@add_despacho');
Route::post('despacho_proveedor/seleccionar_proveedor', 'Postcosecha\DespachoProveedorController@seleccionar_proveedor');
Route::post('despacho_proveedor/store_despacho', 'Postcosecha\DespachoProveedorController@store_despacho');
Route::get('despacho_proveedor/imprimir_all', 'Postcosecha\DespachoProveedorController@imprimir_all');
Route::get('despacho_proveedor/imprimir_etiqueta', 'Postcosecha\DespachoProveedorController@imprimir_etiqueta');
Route::post('despacho_proveedor/delete_model', 'Postcosecha\DespachoProveedorController@delete_model');
