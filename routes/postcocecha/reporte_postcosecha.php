<?php

Route::get('reporte_postcosecha', 'ReportePostcosechaController@inicio');
Route::get('reporte_postcosecha/listar_reporte', 'ReportePostcosechaController@listar_reporte');
Route::get('reporte_postcosecha/exportar_reporte', 'ReportePostcosechaController@exportar_reporte');
