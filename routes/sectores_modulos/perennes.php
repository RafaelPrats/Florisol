<?php

Route::get('sectores_modulos_perennes', 'SectoresModulosPerennesController@inicio');
Route::get('sectores_modulos_perennes/listar_ciclos', 'SectoresModulosPerennesController@listar_ciclos');
Route::post('sectores_modulos_perennes/store_crear_activar_modulo', 'SectoresModulosPerennesController@store_crear_activar_modulo');
Route::post('sectores_modulos_perennes/update_ciclo', 'SectoresModulosPerennesController@update_ciclo');
Route::post('sectores_modulos_perennes/reiniciar_ciclo', 'SectoresModulosPerennesController@reiniciar_ciclo');
Route::get('sectores_modulos_perennes/ver_ciclos_historicos', 'SectoresModulosPerennesController@ver_ciclos_historicos');