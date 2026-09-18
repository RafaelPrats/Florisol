<?php

Route::get('bitacora_recepcion', 'Postco\BitacoraController@inicio');
Route::get('bitacora_recepcion/listar_reporte', 'Postco\BitacoraController@listar_reporte');
Route::get('bitacora_recepcion/exportar_reporte', 'Postco\BitacoraController@exportar_reporte');
