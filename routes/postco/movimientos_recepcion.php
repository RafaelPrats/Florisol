<?php

Route::get('movimientos_recepcion', 'Postco\MovimientosRecepcionController@inicio');
Route::get('movimientos_recepcion/listar_reporte', 'Postco\MovimientosRecepcionController@listar_reporte');
Route::get('movimientos_recepcion/exportar_reporte', 'Postco\MovimientosRecepcionController@exportar_reporte');
