<?php

Route::get('confirmar_proyecto', 'Postco\ConfirmarProyectoController@inicio');
Route::get('confirmar_proyecto/listar_reporte', 'Postco\ConfirmarProyectoController@listar_reporte');
Route::post('confirmar_proyecto/store_confirmar_pedido', 'Postco\ConfirmarProyectoController@store_confirmar_pedido');