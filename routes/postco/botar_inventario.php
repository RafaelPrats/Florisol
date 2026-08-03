<?php

Route::get('botar_inventario', 'Postco\BotarInventarioController@inicio');
Route::get('botar_inventario/listar_reporte', 'Postco\BotarInventarioController@listar_reporte');
Route::get('botar_inventario/admin_motivos', 'Postco\BotarInventarioController@admin_motivos');
Route::post('botar_inventario/store_motivos', 'Postco\BotarInventarioController@store_motivos');
Route::post('botar_inventario/update_motivo', 'Postco\BotarInventarioController@update_motivo');
Route::post('botar_inventario/cambiar_estado_motivo', 'Postco\BotarInventarioController@cambiar_estado_motivo');
Route::post('botar_inventario/store_orden_basura', 'Postco\BotarInventarioController@store_orden_basura');
Route::get('botar_inventario/modal_baja', 'Postco\BotarInventarioController@modal_baja');
Route::get('botar_inventario/listar_ordenes', 'Postco\BotarInventarioController@listar_ordenes');
Route::post('botar_inventario/completar_orden', 'Postco\BotarInventarioController@completar_orden');
Route::post('botar_inventario/delete_orden_basura', 'Postco\BotarInventarioController@delete_orden_basura');
