<?php

Route::get('ingreso_bouquetera', 'Bouquetera\IngresoBqtController@inicio');
Route::get('ingreso_bouquetera/listar_ingresos_bqt', 'Bouquetera\IngresoBqtController@listar_ingresos_bqt');
Route::post('ingreso_bouquetera/store_bqt', 'Bouquetera\IngresoBqtController@store_bqt');
Route::post('ingreso_bouquetera/update_bqt', 'Bouquetera\IngresoBqtController@update_bqt');
Route::post('ingreso_bouquetera/delete_bqt', 'Bouquetera\IngresoBqtController@delete_bqt');
Route::get('ingreso_bouquetera/importar_file_bqt', 'Bouquetera\IngresoBqtController@importar_file_bqt');
Route::get('ingreso_bouquetera/descargar_plantilla', 'Bouquetera\IngresoBqtController@descargar_plantilla');
Route::post('ingreso_bouquetera/upload_file_bqt', 'Bouquetera\IngresoBqtController@upload_file_bqt');
Route::post('ingreso_bouquetera/delete_registros', 'Bouquetera\IngresoBqtController@delete_registros');