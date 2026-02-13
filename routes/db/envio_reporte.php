<?php

Route::get('envio_reporte', 'EnvioReporteController@inicio');
Route::get('envio_reporte/seleccionar_reporte', 'EnvioReporteController@seleccionar_reporte');
Route::post('envio_reporte/seleccionar_usuario', 'EnvioReporteController@seleccionar_usuario');