<?php

Route::get('ingreso_inventario', 'Postco\InventarioRecepcionController@inicio');
Route::get('ingreso_inventario/listar_reporte', 'Postco\InventarioRecepcionController@listar_reporte');
Route::get('ingreso_inventario/modal_add', 'Postco\InventarioRecepcionController@modal_add');