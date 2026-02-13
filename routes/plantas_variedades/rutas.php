<?php

Route::get('plantas_variedades', 'PlantaController@inicio');
Route::get('plantas_variedades/select_planta', 'PlantaController@select_planta');
Route::get('plantas_variedades/add_planta', 'PlantaController@add_planta');
Route::post('plantas_variedades/store_planta', 'PlantaController@store_planta');
Route::get('plantas_variedades/edit_planta', 'PlantaController@edit_planta');
Route::post('plantas_variedades/update_planta', 'PlantaController@update_planta');
Route::post('plantas_variedades/cambiar_estado_planta', 'PlantaController@cambiar_estado_planta');

Route::get('plantas_variedades/add_variedad', 'PlantaController@add_variedad');
Route::post('plantas_variedades/store_variedad', 'PlantaController@store_variedad');
Route::get('plantas_variedades/edit_variedad', 'PlantaController@edit_variedad');
Route::post('plantas_variedades/update_variedad', 'PlantaController@update_variedad');
Route::post('plantas_variedades/cambiar_estado_variedad', 'PlantaController@cambiar_estado_variedad');
Route::get('plantas_variedades/admin_receta', 'PlantaController@admin_receta');
Route::get('plantas_variedades/buscar_variedades', 'PlantaController@buscar_variedades');
Route::post('plantas_variedades/store_agregar_variedades', 'PlantaController@store_agregar_variedades');
Route::get('plantas_variedades/productos_receta', 'PlantaController@productos_receta');
Route::get('plantas_variedades/buscar_productos', 'PlantaController@buscar_productos');
Route::post('plantas_variedades/store_agregar_productos', 'PlantaController@store_agregar_productos');


Route::get('plantas_variedades/form_precio_variedad','PlantaController@form_precio_variedad');
Route::post('plantas_variedades/store_precio','PlantaController@store_precio');
Route::post('plantas_variedades/update_precio','PlantaController@update_precio');
Route::get('plantas_variedades/add_inptus_precio_variedad','PlantaController@add_inptus_precio_variedad');

Route::get('plantas_variedades/vincular_variedad_unitaria','PlantaController@vincular_variedad_unitaria');
Route::post('plantas_variedades/store_vinculo','PlantaController@store_vinculo');
Route::get('plantas_variedades/add_regalias','PlantaController@add_regalias');
Route::post('plantas_variedades/buscar_regalias','PlantaController@buscar_regalias');
Route::post('plantas_variedades/store_regalias','PlantaController@store_regalias');

Route::get('plantas_variedades/form_compelto','PlantaController@form_compelto');
Route::post('plantas_variedades/actualizar_planta','PlantaController@actualizar_planta');
Route::post('plantas_variedades/actualizar_variedad','PlantaController@actualizar_variedad');

Route::get('plantas_variedades/add_proveedor', 'PlantaController@add_proveedor');
Route::post('plantas_variedades/store_proveedor', 'PlantaController@store_proveedor');
Route::get('plantas_variedades/edit_proveedor', 'PlantaController@edit_proveedor');
Route::post('plantas_variedades/update_proveedor', 'PlantaController@update_proveedor');
Route::post('plantas_variedades/asignar_proveedor', 'PlantaController@asignar_proveedor');
Route::post('plantas_variedades/asignar_all_variedades', 'PlantaController@asignar_all_variedades');

Route::get('plantas_variedades/importar_variedades', 'PlantaController@importar_variedades');
Route::get('plantas_variedades/descargar_plantilla', 'PlantaController@descargar_plantilla');
Route::post('plantas_variedades/post_importar_variedades', 'PlantaController@post_importar_variedades');
Route::get('plantas_variedades/get_importar_variedades', 'PlantaController@get_importar_variedades');
Route::post('plantas_variedades/store_importar_variedades', 'PlantaController@store_importar_variedades');
Route::get('plantas_variedades/importar_recetas', 'PlantaController@importar_recetas');
Route::get('plantas_variedades/descargar_plantilla_recetas', 'PlantaController@descargar_plantilla_recetas');
Route::post('plantas_variedades/post_importar_recetas', 'PlantaController@post_importar_recetas');
Route::post('plantas_variedades/seleccionar_receta_defecto', 'PlantaController@seleccionar_receta_defecto');
Route::post('plantas_variedades/bloquear_distribucion', 'PlantaController@bloquear_distribucion');
