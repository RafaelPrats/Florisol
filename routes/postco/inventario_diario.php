<?php

Route::get('inventario_diario', 'Postco\ReporteInventarioDiarioController@inicio');
Route::get('inventario_diario/listar_reporte', 'Postco\ReporteInventarioDiarioController@listar_reporte');
Route::get('inventario_diario/exportar_reporte', 'Postco\ReporteInventarioDiarioController@exportar_reporte');
