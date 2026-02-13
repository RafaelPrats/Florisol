<?php

Route::get('planificacion', 'Postcosecha\PlanificacionController@inicio');
Route::get('planificacion/listar_reporte', 'Postcosecha\PlanificacionController@listar_reporte');
Route::get('planificacion/modal_planificacion', 'Postcosecha\PlanificacionController@modal_planificacion');
Route::get('planificacion/admin_receta', 'Postcosecha\PlanificacionController@admin_receta');
Route::get('planificacion/exportar_receta', 'Postcosecha\PlanificacionController@exportar_receta');
Route::get('planificacion/exportar_excel_fecha', 'Postcosecha\PlanificacionController@exportar_excel_fecha');
Route::get('planificacion/exportar_excel_total', 'Postcosecha\PlanificacionController@exportar_excel_total');
Route::get('planificacion/detalle_ventas', 'Postcosecha\PlanificacionController@detalle_ventas');
