<?php

Route::get('botar_inventario', 'Postco\BotarInventarioController@inicio');
Route::get('botar_inventario/listar_reporte', 'Postco\BotarInventarioController@listar_reporte');
Route::get('botar_inventario/admin_motivos', 'Postco\BotarInventarioController@admin_motivos');
Route::post('botar_inventario/store_motivos', 'Postco\BotarInventarioController@store_motivos');
Route::post('botar_inventario/update_motivo', 'Postco\BotarInventarioController@update_motivo');
Route::post('botar_inventario/cambiar_estado_motivo', 'Postco\BotarInventarioController@cambiar_estado_motivo');
Route::post('botar_inventario/botar_inventario', 'Postco\BotarInventarioController@botar_inventario');
