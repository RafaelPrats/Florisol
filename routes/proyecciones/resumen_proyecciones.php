<?php

Route::get('resumen_proyecciones', 'Proyecciones\ResumenProyeccionesController@inicio');
Route::get('resumen_proyecciones/listar_resumen_total', 'Proyecciones\ResumenProyeccionesController@listar_resumen_semanal');
Route::get('resumen_proyecciones/select_desglose_planta', 'Proyecciones\ResumenProyeccionesController@select_desglose_planta');
Route::get('resumen_proyecciones/exportar_reporte', 'Proyecciones\ResumenProyeccionesController@exportar_reporte');
