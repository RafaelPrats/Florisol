<?php

Route::get('reporte_salidas', 'Postco\ReporteSalidasController@inicio');
Route::get('reporte_salidas/listar_reporte', 'Postco\ReporteSalidasController@listar_reporte');
Route::get('reporte_salidas/exportar_reporte', 'Postco\ReporteSalidasController@exportar_reporte');
