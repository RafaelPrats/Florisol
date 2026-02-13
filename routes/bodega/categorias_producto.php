<?php

Route::get('categorias_producto', 'Bodega\CategoriaProductoController@inicio');
Route::get('categorias_producto/listar_reporte', 'Bodega\CategoriaProductoController@listar_reporte');
Route::post('categorias_producto/update_categoria', 'Bodega\CategoriaProductoController@update_categoria');
Route::post('categorias_producto/cambiar_estado_categoria', 'Bodega\CategoriaProductoController@cambiar_estado_categoria');
Route::post('categorias_producto/store_categoria', 'Bodega\CategoriaProductoController@store_categoria');
