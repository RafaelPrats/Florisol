<?php

Route::get('movimientos_bodega', 'Bodega\MovimientosBodegaController@inicio');
Route::get('movimientos_bodega/listar_reporte', 'Bodega\MovimientosBodegaController@listar_reporte');
Route::get('movimientos_bodega/add_ingresos', 'Bodega\MovimientosBodegaController@add_ingresos');
Route::post('movimientos_bodega/store_ingresos', 'Bodega\MovimientosBodegaController@store_ingresos');
Route::get('movimientos_bodega/add_salidas', 'Bodega\MovimientosBodegaController@add_salidas');
Route::post('movimientos_bodega/store_salidas', 'Bodega\MovimientosBodegaController@store_salidas');
