<?php

Route::get('pedidos','Comercializacion\PedidoController@inicio');
Route::get('pedidos/add_pedido','Comercializacion\PedidoController@add_pedido');
Route::get('pedidos/buscar_inventario','Comercializacion\PedidoController@buscar_inventario');
