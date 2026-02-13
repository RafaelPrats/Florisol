<?php

Route::get('proy_resumen_total', 'Proyecciones\proyResumenTotalController@inicio');
Route::get('proy_resumen_total/listar_resumen_total', 'Proyecciones\proyResumenTotalController@listar_resumen_semanal');
Route::get('proy_resumen_total/select_desglose_planta', 'Proyecciones\proyResumenTotalController@select_desglose_planta');
Route::post('proy_resumen_total/actualizar_resumen_segundo_plano', 'Proyecciones\proyResumenTotalController@actualizar_resumen_segundo_plano');
Route::post('proy_resumen_total/actualizar_proyectados_job', 'Proyecciones\proyResumenTotalController@actualizar_proyectados_job');
Route::get('proy_resumen_total/exportar_reporte', 'Proyecciones\proyResumenTotalController@exportar_reporte');
