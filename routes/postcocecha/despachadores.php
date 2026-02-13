<?php

Route::get('despachadores', 'DespachadorController@inicio');
Route::get('despachadores/buscar_listado_despachadores', 'DespachadorController@buscar_listado_despachadores');
Route::post('despachadores/store_despachador', 'DespachadorController@store_despachador');
Route::post('despachadores/update_despachador', 'DespachadorController@update_despachador');
Route::post('despachadores/desactivar_despachador', 'DespachadorController@desactivar_despachador');
