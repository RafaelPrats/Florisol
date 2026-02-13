<?php

Route::get('tabla_bqt', 'CRM\TablaBqtController@inicio');
Route::get('tabla_bqt/filtrar_tablas', 'CRM\TablaBqtController@filtrar_tablas');
Route::get('tabla_bqt/exportar_tabla', 'CRM\TablaBqtController@exportar_tabla');
Route::get('tabla_bqt/select_planta_semanal', 'CRM\TablaBqtController@select_planta_semanal');
Route::get('tabla_bqt/select_planta_diario', 'CRM\TablaBqtController@select_planta_diario');
Route::get('tabla_bqt/select_planta_mensual', 'CRM\TablaBqtController@select_planta_mensual');
Route::get('tabla_bqt/exportar_planta_semanal', 'CRM\TablaBqtController@exportar_planta_semanal');
Route::get('tabla_bqt/exportar_planta_mensual', 'CRM\TablaBqtController@exportar_planta_mensual');

/* ----------------------------------------------------------------------------- */
Route::get('tabla_bqt/pedidos_cliente', 'PedidoController@pedidos_cliente');
