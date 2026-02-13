<?php

Route::get('personal', 'RRHH\rrhhPersonalController@inicio');
Route::get('personal/buscar_personal','RRHH\rrhhPersonalController@buscar_personal');
Route::get('personal/trabajadores','RRHH\rrhhPersonalController@trabajador');
Route::get('personal/busqueda_fecha','RRHH\rrhhPersonalController@busqueda_fecha');
//Route::get('personal/listar_personal','RRHH\rrhhPersonalController@listar_personal');
Route::get('personal/add','RRHH\rrhhPersonalController@add_personal');
Route::get('personal/crear_personal','RRHH\rrhhPersonalController@crear_personal');
Route::post('personal/store_personal','RRHH\rrhhPersonalController@store_personal');
Route::get('personal/update_personal','RRHH\rrhhPersonalController@ver_personal');
Route::get('personal/ficha_personal','RRHH\rrhhPersonalController@ficha_personal');
Route::get('personal/seleccionar_area', 'RRHH\rrhhPersonalController@seleccionar_area');
Route::get('personal/seleccionar_actividad', 'RRHH\rrhhPersonalController@seleccionar_actividad');
Route::post('personal/actualiza_personal','RRHH\rrhhPersonalController@actualiza_personal');
Route::post('personal/reincorporar_personal','RRHH\rrhhPersonalController@reincorporar_personal');
Route::get('personal/view_historico','RRHH\rrhhPersonalController@ver_historico');
Route::get('personal/view_desincorporar_personal','RRHH\rrhhPersonalController@ver_desincorporar_personal');
Route::get('personal/view_incorporar_personal','RRHH\rrhhPersonalController@ver_incorporar_personal');
Route::post('personal/desincorporar_persona','RRHH\rrhhPersonalController@desincorporar_persona');
Route::post('personal/eliminar_trabajador','RRHH\rrhhPersonalController@eliminar_trabajador');
Route::post('personal/update_estado','RRHH\rrhhPersonalController@actualizarEstadoPersonal')->name('update_estado.personal_detalle');
Route::get('personal/excel','RRHH\rrhhPersonalController@excel_personal');


// DASHBOARD
Route::get('dashboard_personal','RRHH\rrhhDashboardController@inicio');
Route::get('dashboard_personal/deglose_indicador','RRHH\rrhhDashboardController@deglose_indicador');
Route::get('dashboard_personal/filtrar_graficas','RRHH\rrhhDashboardController@filtrar_graficas');

