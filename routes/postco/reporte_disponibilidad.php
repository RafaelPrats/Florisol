<?php

Route::get('reporte_disponibilidad', 'Postco\ReporteDisponibilidadController@inicio');
Route::get('reporte_disponibilidad/listar_reporte', 'Postco\ReporteDisponibilidadController@listar_reporte');
Route::get('reporte_disponibilidad/exportar_listado', 'Postco\ReporteDisponibilidadController@exportar_listado');
Route::get('reporte_disponibilidad/detalle_ventas', 'Postco\ReporteDisponibilidadController@detalle_ventas');
