<?php

Route::get('corregir_inventario', 'Postco\CorregirInventarioController@inicio');
Route::get('corregir_inventario/listar_reporte', 'Postco\CorregirInventarioController@listar_reporte');
Route::post('corregir_inventario/store_correccion', 'Postco\CorregirInventarioController@store_correccion');