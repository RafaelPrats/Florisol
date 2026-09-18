<?php

Route::get('kardex', 'Postco\KardexController@inicio');
Route::get('kardex/listar_reporte', 'Postco\KardexController@listar_reporte');
Route::get('kardex/exportar_reporte', 'Postco\KardexController@exportar_reporte');
