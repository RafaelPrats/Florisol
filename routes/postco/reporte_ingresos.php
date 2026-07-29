<?php

Route::get('reporte_ingresos', 'Postco\ReporteIngresosController@inicio');
Route::get('reporte_ingresos/listar_reporte', 'Postco\ReporteIngresosController@listar_reporte');
