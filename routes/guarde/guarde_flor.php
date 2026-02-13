<?php

Route::get('guarde_flor', 'Guarde\GuardeFlorController@inicio');
Route::get('guarde_flor/listar_reporte', 'Guarde\GuardeFlorController@listar_reporte');
Route::get('guarde_flor/add_guardes', 'Guarde\GuardeFlorController@add_guardes');
Route::post('guarde_flor/store_guardes', 'Guarde\GuardeFlorController@store_guardes');
Route::post('guarde_flor/update_guarde', 'Guarde\GuardeFlorController@update_guarde');
Route::post('guarde_flor/delete_guarde', 'Guarde\GuardeFlorController@delete_guarde');
Route::post('guarde_flor/sacar_guarde', 'Guarde\GuardeFlorController@sacar_guarde');