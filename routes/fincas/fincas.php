<?php

Route::get('fincas', 'FincasController@inicio');
Route::get('fincas/listar_fincas', 'FincasController@listar_fincas');
Route::get('fincas/listar_super_fincas', 'FincasController@listar_super_fincas');
Route::post('fincas/store_super_finca', 'FincasController@store_super_finca');
Route::post('fincas/update_finca', 'FincasController@update_finca');
Route::post('fincas/update_super_finca', 'FincasController@update_super_finca');