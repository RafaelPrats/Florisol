<?php

Route::get('codigo_autorizacion', 'CodigoAutorizacionController@inicio');
Route::post('codigo_autorizacion/store_codigos', 'CodigoAutorizacionController@store_codigos');