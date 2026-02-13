<?php

Route::get('re_armado', 'Postco\ReArmadoController@inicio');
Route::get('re_armado/listar_reporte', 'Postco\ReArmadoController@listar_reporte');
Route::get('re_armado/modal_receta', 'Postco\ReArmadoController@modal_receta');
