<?php

Route::get('importar_postco', 'Postco\ImportarPostcoController@inicio');
Route::get('importar_postco/listar_reporte', 'Postco\ImportarPostcoController@listar_reporte');
Route::get('importar_postco/modal_importar', 'Postco\ImportarPostcoController@modal_importar');
Route::get('importar_postco/get_importar_pedidos', 'Postco\ImportarPostcoController@get_importar_pedidos');
Route::post('importar_postco/post_importar_pedidos', 'Postco\ImportarPostcoController@post_importar_pedidos');
Route::post('importar_postco/store_postco', 'Postco\ImportarPostcoController@store_postco');
Route::post('importar_postco/delete_postco', 'Postco\ImportarPostcoController@delete_postco');
