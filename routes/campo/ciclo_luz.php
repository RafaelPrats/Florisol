<?php

Route::get('ciclo_luz', 'Campo\LuzController@inicio');
Route::get('ciclo_luz/listar_ciclo_luz', 'Campo\LuzController@listar_ciclo_luz');
Route::post('ciclo_luz/store_luz', 'Campo\LuzController@store_luz');
Route::post('ciclo_luz/update_luz', 'Campo\LuzController@update_luz');
Route::post('ciclo_luz/ejecutar_luz', 'Campo\LuzController@ejecutar_luz');
Route::post('ciclo_luz/store_all', 'Campo\LuzController@store_all');
