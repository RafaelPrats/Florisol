<?php

Route::get('importar_pedidos', 'Comercializacion\ImportarPedidosController@inicio');
Route::get('importar_pedidos/add_pedido', 'Comercializacion\ImportarPedidosController@add_pedido');
Route::get('importar_pedidos/listar_reporte', 'Comercializacion\ImportarPedidosController@listar_reporte');
Route::get('importar_pedidos/descargar_plantilla', 'Comercializacion\ImportarPedidosController@descargar_plantilla');
Route::post('importar_pedidos/post_importar_pedidos', 'Comercializacion\ImportarPedidosController@post_importar_pedidos');
Route::get('importar_pedidos/get_importar_pedidos', 'Comercializacion\ImportarPedidosController@get_importar_pedidos');
Route::post('importar_pedidos/store_importar_pedidos', 'Comercializacion\ImportarPedidosController@store_importar_pedidos');
Route::post('importar_pedidos/eliminar_pedido', 'Comercializacion\ImportarPedidosController@eliminar_pedido');
Route::get('importar_pedidos/admin_receta', 'Comercializacion\ImportarPedidosController@admin_receta');
Route::get('importar_pedidos/buscar_variedades', 'Comercializacion\ImportarPedidosController@buscar_variedades');
Route::post('importar_pedidos/store_distribucion_receta', 'Comercializacion\ImportarPedidosController@store_distribucion_receta');
Route::post('importar_pedidos/reiniciar_distribucion_receta', 'Comercializacion\ImportarPedidosController@reiniciar_distribucion_receta');
Route::get('importar_pedidos/cargar_receta', 'Comercializacion\ImportarPedidosController@cargar_receta');
Route::get('importar_pedidos/mover_fecha_pedido', 'Comercializacion\ImportarPedidosController@mover_fecha_pedido');
Route::post('importar_pedidos/store_mover_fecha_pedido', 'Comercializacion\ImportarPedidosController@store_mover_fecha_pedido');
Route::get('importar_pedidos/ver_procesos', 'Comercializacion\ImportarPedidosController@ver_procesos');
Route::get('importar_pedidos/cargar_procesos', 'Comercializacion\ImportarPedidosController@cargar_procesos');
