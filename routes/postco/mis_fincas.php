<?php

Route::get('mis_fincas', 'Postco\MisFincasController@inicio');
Route::post('mis_fincas/store_finca', 'Postco\MisFincasController@store_finca');
Route::post('mis_fincas/update_finca', 'Postco\MisFincasController@update_finca');
Route::post('mis_fincas/cambiar_estado', 'Postco\MisFincasController@cambiar_estado');
Route::get('mis_fincas/modal_plantas', 'Postco\MisFincasController@modal_plantas');
Route::post('mis_fincas/update_planta_finca', 'Postco\MisFincasController@update_planta_finca');
