<?php

Route::get('tbl_ventas', 'CRM\tblVentasController@inicio');
Route::get('tbl_ventas/filtrar_tablas', 'CRM\tblVentasController@filtrar_tablas');
Route::get('tbl_ventas/exportar_tabla', 'CRM\tblVentasController@exportar_tabla');
Route::get('tbl_ventas/select_planta_semanal', 'CRM\tblVentasController@select_planta_semanal');
Route::get('tbl_ventas/select_planta_diario', 'CRM\tblVentasController@select_planta_diario');
Route::get('tbl_ventas/select_planta_mensual', 'CRM\tblVentasController@select_planta_mensual');
Route::get('tbl_ventas/exportar_planta_semanal', 'CRM\tblVentasController@exportar_planta_semanal');
Route::get('tbl_ventas/exportar_planta_mensual', 'CRM\tblVentasController@exportar_planta_mensual');
Route::get('tbl_ventas/exportar_variedades', 'CRM\tblVentasController@exportar_variedades');

/* ----------------------------------------------------------------------------- */
Route::get('tbl_ventas/pedidos_cliente', 'PedidoController@pedidos_cliente');
