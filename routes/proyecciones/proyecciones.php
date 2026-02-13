<?php

Route::get('ingreso_proyecciones', 'Proyecciones\ProyeccionesController@inicio');
Route::get('ingreso_proyecciones/listar_ingreso_proyecciones', 'Proyecciones\ProyeccionesController@listar_ingreso_proyecciones');
Route::post('ingreso_proyecciones/update_semana', 'Proyecciones\ProyeccionesController@update_semana');
Route::post('ingreso_proyecciones/update_all_semanas', 'Proyecciones\ProyeccionesController@update_all_semanas');
Route::post('ingreso_proyecciones/copiar_semanas', 'Proyecciones\ProyeccionesController@copiar_semanas');
Route::post('ingreso_proyecciones/generar_semanas', 'Proyecciones\ProyeccionesController@generar_semanas');
Route::get('ingreso_proyecciones/refresh_jobs', 'Proyecciones\ProyeccionesController@refresh_jobs');
Route::post('ingreso_proyecciones/ejecutar_semana', 'Proyecciones\ProyeccionesController@ejecutar_semana');
