<?php

Route::get('exportador','Postcosecha\ExportadorController@inicio');
Route::get('exportador/listar','Postcosecha\ExportadorController@listar');
Route::post('exportador/store','Postcosecha\ExportadorController@store');
Route::post('exportador/update','Postcosecha\ExportadorController@update');
Route::post('exportador/cambiar_estado','Postcosecha\ExportadorController@cambiar_estado');
