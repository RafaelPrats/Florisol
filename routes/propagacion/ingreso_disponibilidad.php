<?php

Route::get('ingreso_disponibilidad', 'Propagacion\IngresoDisponibilidadController@inicio');
Route::get('ingreso_disponibilidad/listar_ingreso_disponibilidad', 'Propagacion\IngresoDisponibilidadController@listar_ingreso_disponibilidad');
Route::get('ingreso_disponibilidad/select_desglose_planta', 'Propagacion\IngresoDisponibilidadController@select_desglose_planta');
Route::post('ingreso_disponibilidad/update_requerimiento', 'Propagacion\IngresoDisponibilidadController@update_requerimiento');