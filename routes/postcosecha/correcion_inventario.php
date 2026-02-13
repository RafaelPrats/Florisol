<?php

Route::get('correcion_inventario', 'Postcosecha\CorrecionInventarioController@inicio');
Route::get('correcion_inventario/escanear_codigo', 'Postcosecha\CorrecionInventarioController@escanear_codigo');
Route::post('correcion_inventario/corregir_all_inventario', 'Postcosecha\CorrecionInventarioController@corregir_all_inventario');
Route::post('correcion_inventario/corregir_inventario_selected', 'Postcosecha\CorrecionInventarioController@corregir_inventario_selected');
