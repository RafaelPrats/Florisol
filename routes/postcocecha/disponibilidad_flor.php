<?php

Route::get('disponibilidad_flor', 'DisponibilidadFlorController@inicio');
Route::get('disponibilidad_flor/listar_reporte', 'DisponibilidadFlorController@listar_reporte');
Route::get('disponibilidad_flor/detalle_ventas', 'DisponibilidadFlorController@detalle_ventas');
Route::get('disponibilidad_flor/cargar_tabla', 'DisponibilidadFlorController@cargar_tabla');
Route::get('disponibilidad_flor/exportar_listado', 'DisponibilidadFlorController@exportar_listado');
