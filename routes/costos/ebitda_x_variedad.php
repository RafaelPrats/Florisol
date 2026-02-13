<?php

Route::get('ebitda_x_variedad', 'Costos\EbitdaXVariedadController@inicio');
Route::get('ebitda_x_variedad/listado_ebitda_x_variedad', 'Costos\EbitdaXVariedadController@listado_ebitda_x_variedad');
Route::get('ebitda_x_variedad/exportar_listado_operaciones', 'Costos\EbitdaXVariedadController@exportar_listado_operaciones');
