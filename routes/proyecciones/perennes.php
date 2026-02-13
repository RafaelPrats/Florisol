<?php

Route::get('proy_perennes', 'Proyecciones\proyPerennesController@inicio');
Route::get('proy_perennes/listar_proyecciones', 'Proyecciones\proyPerennesController@listar_proyecciones');
Route::post('proy_perennes/update_semana', 'Proyecciones\proyPerennesController@update_semana');
Route::post('proy_perennes/update_all_semanas', 'Proyecciones\proyPerennesController@update_all_semanas');
Route::post('proy_perennes/copiar_a_finca', 'Proyecciones\proyPerennesController@copiar_a_finca');
Route::post('proy_perennes/corregir_proy_sem_perenne', 'Proyecciones\proyPerennesController@corregir_proy_sem_perenne');
Route::post('proy_perennes/copiar_semanas', 'Proyecciones\proyPerennesController@copiar_semanas');