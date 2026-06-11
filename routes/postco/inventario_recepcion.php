<?php

Route::get('inventario_recepcion', 'Postco\InventarioDiarioController@inicio');
Route::get('inventario_recepcion/listar_reporte', 'Postco\InventarioDiarioController@listar_reporte');