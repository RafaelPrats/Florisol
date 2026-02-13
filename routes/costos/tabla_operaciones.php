<?php

Route::get('tabla_operaciones', 'Costos\TablaOperacionesController@inicio');
Route::get('tabla_operaciones/listado_operaciones', 'Costos\TablaOperacionesController@listado_operaciones');
Route::get('tabla_operaciones/exportar_listado_operaciones', 'Costos\TablaOperacionesController@exportar_listado_operaciones');