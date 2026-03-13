<?php

Route::get('proyectos', 'Comercializacion\ProyectoController@inicio');
Route::get('proyectos/listar_reporte', 'Comercializacion\ProyectoController@listar_reporte');
Route::get('proyectos/add_proyecto', 'Comercializacion\ProyectoController@add_proyecto');
Route::get('proyectos/cargar_opciones_orden_fija', 'Comercializacion\ProyectoController@cargar_opciones_orden_fija');
Route::post('proyectos/seleccionar_segmento', 'Comercializacion\ProyectoController@seleccionar_segmento');
Route::post('proyectos/seleccionar_cliente', 'Comercializacion\ProyectoController@seleccionar_cliente');
Route::post('proyectos/form_combos_seleccionar_receta', 'Comercializacion\ProyectoController@form_combos_seleccionar_receta');
Route::get('proyectos/agregar_combos_pedido', 'Comercializacion\ProyectoController@agregar_combos_pedido');
Route::post('proyectos/store_proyecto', 'Comercializacion\ProyectoController@store_proyecto');
Route::get('proyectos/editar_proyecto', 'Comercializacion\ProyectoController@editar_proyecto');
Route::post('proyectos/update_proyecto', 'Comercializacion\ProyectoController@update_proyecto');
Route::get('proyectos/copiar_pedido', 'Comercializacion\ProyectoController@copiar_pedido');
Route::post('proyectos/store_copiar_pedido', 'Comercializacion\ProyectoController@store_copiar_pedido');
Route::post('proyectos/delete_pedido', 'Comercializacion\ProyectoController@delete_pedido');
