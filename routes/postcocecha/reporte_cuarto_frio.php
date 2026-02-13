<?php

Route::get('reporte_cuarto_frio', 'ReporteCuartoFrioController@inicio');
Route::get('reporte_cuarto_frio/listar_reporte', 'ReporteCuartoFrioController@listar_reporte');
Route::get('reporte_cuarto_frio/importar_bajas', 'ReporteCuartoFrioController@importar_bajas');
Route::post('reporte_cuarto_frio/importar_file_bajas', 'ReporteCuartoFrioController@importar_file_bajas');
Route::get('reporte_cuarto_frio/get_importar_file_bajas', 'ReporteCuartoFrioController@get_importar_file_bajas');
Route::post('reporte_cuarto_frio/store_bajas', 'ReporteCuartoFrioController@store_bajas');
Route::get('reporte_cuarto_frio/exportar_reporte', 'ReporteCuartoFrioController@exportar_reporte');
