<?php

Route::get('reporte_ingresos', 'Postco\ReporteIngresosController@inicio');
Route::get('reporte_ingresos/listar_reporte', 'Postco\ReporteIngresosController@listar_reporte');
Route::post('reporte_ingresos/habilitar_modificar', 'Postco\ReporteIngresosController@habilitar_modificar');
Route::post('reporte_ingresos/update_compra', 'Postco\ReporteIngresosController@update_compra');
