<?php

Route::get('cosecha_diaria', 'CRM\CosechaDiariaController@inicio');
Route::get('cosecha_diaria/buscar_cosecha_diaria', 'CRM\CosechaDiariaController@buscar_cosecha_diaria');
Route::get('cosecha_diaria/exportar_reporte', 'CRM\CosechaDiariaController@exportar_reporte');
Route::post('cosecha_diaria/actualizar_fecha', 'CRM\CosechaDiariaController@actualizar_fecha');
Route::post('cosecha_diaria/actualizar_all_fechas', 'CRM\CosechaDiariaController@actualizar_all_fechas');
