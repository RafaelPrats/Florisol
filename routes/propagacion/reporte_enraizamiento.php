<?php

Route::get('reporte_enraizamiento', 'Propagacion\ReporteEnraizamientoController@inicio');
Route::get('reporte_enraizamiento/listar_reporte_enraizamiento', 'Propagacion\ReporteEnraizamientoController@listar_reporte_enraizamiento');
Route::get('reporte_enraizamiento/select_desglose_planta', 'Propagacion\ReporteEnraizamientoController@select_desglose_planta');