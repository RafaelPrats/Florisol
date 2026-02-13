<?php

Route::get('fenograma_ejecucion', 'FenogramaEjecucionController@inicio');
Route::get('fenograma_ejecucion/filtrar_ciclos', 'FenogramaEjecucionController@filtrar_ciclos');
Route::get('fenograma_ejecucion/exportar_reporte', 'FenogramaEjecucionController@exportar_reporte');