<?php

Route::get('cosechadores', 'CosechadoresController@inicio');
Route::get('cosechadores/buscar_listado_cosechadores', 'CosechadoresController@buscar_listado_cosechadores');
Route::post('cosechadores/store_cosechador', 'CosechadoresController@store_cosechador');
Route::post('cosechadores/update_cosechador', 'CosechadoresController@update_cosechador');
Route::post('cosechadores/desactivar_cosechador', 'CosechadoresController@desactivar_cosechador');
