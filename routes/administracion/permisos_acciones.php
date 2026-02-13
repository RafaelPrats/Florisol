<?php

Route::get('permisos_acciones', 'PermisosAccionesController@inicio');
Route::get('permisos_acciones/buscar_listado_permisos', 'PermisosAccionesController@buscar_listado_permisos');
Route::post('permisos_acciones/store_permiso', 'PermisosAccionesController@store_permiso');
Route::post('permisos_acciones/update_permiso', 'PermisosAccionesController@update_permiso');
Route::post('permisos_acciones/desactivar_permiso', 'PermisosAccionesController@desactivar_permiso');
