<?php

Route::get('tallos_preproduccion', 'Postcosecha\TallosPreproduccionController@inicio');
Route::get('tallos_preproduccion/listar_reporte', 'Postcosecha\TallosPreproduccionController@listar_reporte');
Route::get('tallos_preproduccion/modal_combinacion', 'Postcosecha\TallosPreproduccionController@modal_combinacion');
