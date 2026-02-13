<?php

Route::get('propag_disponibilidad', 'Propagacion\propagDisponibilidadController@inicio');
Route::get('propag_disponibilidad/listar_disponibilidades', 'Propagacion\propagDisponibilidadController@listar_disponibilidades');
Route::post('propag_disponibilidad/update_disponibilidad', 'Propagacion\propagDisponibilidadController@update_disponibilidad');
Route::post('propag_disponibilidad/update_semana', 'Propagacion\propagDisponibilidadController@update_semana');