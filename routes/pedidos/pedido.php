<?php

Route::get('pedidos','PedidoController@inicio');
Route::get('pedidos/crear_pedido','PedidoController@crearPedido');
Route::get('pedidos/obtener_inventario_planta','PedidoController@obetenerInventarioPLanta');
Route::get('pedidos/obtener_inventario_planta_variedad','PedidoController@obetenerInventarioPlantaVariedad');
Route::get('pedidos/obtener_data_pedido','PedidoController@obtenerDataPedido');
Route::post('pedidos/store_pedido','PedidoController@storePedido');
