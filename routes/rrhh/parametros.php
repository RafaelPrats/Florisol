<?php

Route::get('parametros', 'RRHH\rrhhParametrosController@inicio');
Route::get('parametros/listar_parametro', 'RRHH\rrhhParametrosController@listar_parametro');
Route::get('parametros/actualizar_salario', 'RRHH\rrhhParametrosController@actualizar_salario');
/* =================== ausentismo ==================*/

Route::post('parametros/store_ausentismo', 'RRHH\rrhhParametrosController@store_ausentismo');
Route::post('parametros/update_estado','RRHH\rrhhParametrosController@actualizarEstadoausentismo')->name('update_estado.ausentismo');
Route::post('parametros/editar_ausentismo', 'RRHH\rrhhParametrosController@editar_ausentismo');
/* =================== BANCO ==================*/

Route::post('parametros/store_banco', 'RRHH\rrhhParametrosController@store_banco');
Route::post('parametros/update_estado','RRHH\rrhhParametrosController@actualizarEstadoBanco')->name('update_estado.banco');
Route::post('parametros/editar_banco', 'RRHH\rrhhParametrosController@editar_banco');

/* =================== CARGO ==================*/

Route::post('parametros/store_cargo', 'RRHH\rrhhParametrosController@store_cargo');
Route::get('parametros/add_cargo','RRHH\rrhhParametrosControlle@form_add_cargo');
Route::post('parametros/editar_cargo', 'RRHH\rrhhParametrosController@editar_cargo');
Route::post('parametros/update_cargo','RRHH\rrhhParametrosController@actualizarEstadoCargo')->name('update_estado.cargo');

/* =================== PROFESIONES ==================*/

Route::post('parametros/store_profesion', 'RRHH\rrhhParametrosController@store_profesion');
Route::post('parametros/update_profesion','RRHH\rrhhParametrosController@actualizarEstadoProfesion')->name('update_estado.profesion');
Route::post('parametros/editar_profesion', 'RRHH\rrhhParametrosController@editar_profesion');

/* =================== TIPO ROL ==================*/

Route::post('parametros/store_tipo_rol', 'RRHH\rrhhParametrosController@store_tipo_rol');
Route::post('parametros/update_estado','RRHH\rrhhParametrosController@actualizarEstadoTipo_rol')->name('update_estado.tipo_rol');
Route::post('parametros/editar_tipo_rol', 'RRHH\rrhhParametrosController@editar_tipo_rol');


/* =================== CAUSA DESVINCULACION ==================*/


Route::post('parametros/store_causa_desvinculacion', 'RRHH\rrhhParametrosController@store_causa_desvinculacion');
Route::post('parametros/update_estado_causa','RRHH\rrhhParametrosController@actualizarEstadoCausa_desvinculacion')->name('update_estado.causa_desvinculacion');
Route::post('parametros/editar_causa_desvinculacion', 'RRHH\rrhhParametrosController@editar_causa_desvinculacion');

/* =================== TIPO CONTRATO ==================*/

Route::post('parametros/store_tipo_contrato', 'RRHH\rrhhParametrosController@store_tipo_contrato');
Route::post('parametros/update_estado_tipo_contrato','RRHH\rrhhParametrosController@actualizarEstadoTipo_contrato')->name('update_estado.tipo_contrato');
Route::post('parametros/editar_tipo_contrato', 'RRHH\rrhhParametrosController@editar_tipo_contrato');

/* =================== TIPO PAGO ==================*/

Route::post('parametros/store_tipo_pago', 'RRHH\rrhhParametrosController@store_tipo_pago');
Route::post('parametros/update_tipo_pago','RRHH\rrhhParametrosController@actualizarEstadoTipo_pago')->name('update_estado.tipo_pago');
Route::post('parametros/editar_tipo_pago', 'RRHH\rrhhParametrosController@editar_tipo_pago');

/* =================== ESTRUCTURA ORGANIZATIVA ==================*/

Route::post('parametros/store_estructura_organizativa', 'RRHH\rrhhParametrosController@store_estructura_organizativa');
Route::post('parametros/update_estructura_organizativa','RRHH\rrhhParametrosController@actualizarEstadoEstructura_organizativa')->name('update_estado.estructura_organizativa');
Route::post('parametros/editar_estructura_organizativa', 'RRHH\rrhhParametrosController@editar_estructura_organizativa');

/* =================== GRUPO ==================*/

Route::post('parametros/store_grupo', 'RRHH\rrhhParametrosController@store_grupo');
Route::post('parametros/update_grupo','RRHH\rrhhParametrosController@actualizarEstadoGrupo')->name('update_estado.grupo');
Route::post('parametros/editar_grupo', 'RRHH\rrhhParametrosController@editar_grupo');

/* =================== DEPARTAMENTO ==================*/

Route::post('parametros/store_departamento', 'RRHH\rrhhParametrosController@store_departamento');
Route::post('parametros/update_estado','RRHH\rrhhParametrosController@actualizarEstadoDepartamento')->name('update_estado.departamento');
Route::post('parametros/editar_departamento', 'RRHH\rrhhParametrosController@editar_departamento');

/* =================== SUCURSAL ==================*/

Route::post('parametros/store_sucursal', 'RRHH\rrhhParametrosController@store_sucursal');
Route::post('parametros/update_sucursal','RRHH\rrhhParametrosController@actualizarEstadoSucursal')->name('update_estado.sucursal');
Route::post('parametros/editar_sucursal', 'RRHH\rrhhParametrosController@editar_sucursal');

/* =================== GRUPO_INTERNO ==================*/

Route::post('parametros/store_grupo_interno', 'RRHH\rrhhParametrosController@store_grupo_interno');
Route::post('parametros/update_grupo_interno','RRHH\rrhhParametrosController@actualizarEstadoGrupo_interno')->name('update_estado.grupo_interno');
Route::post('parametros/editar_grupo_interno', 'RRHH\rrhhParametrosController@editar_grupo_interno');

/* =================== GRADO_INSTRUCCION ==================*/

Route::post('parametros/store_grado_instruccion', 'RRHH\rrhhParametrosController@store_grado_instruccion');
Route::post('parametros/update_grado_instruccion','RRHH\rrhhParametrosController@actualizarEstadoGrado_instruccion')->name('update_estado.grado_instruccion');
Route::post('parametros/editar_grado_instruccion', 'RRHH\rrhhParametrosController@editar_grado_instruccion');

/* =================== AGRUPACION ==================*/

Route::post('parametros/store_agrupacion', 'RRHH\rrhhParametrosController@store_agrupacion');
Route::post('parametros/update_agrupacion','RRHH\rrhhParametrosController@actualizarEstadoAgrupacion')->name('update_estado.agrupacion');
Route::post('parametros/editar_agrupacion', 'RRHH\rrhhParametrosController@editar_agrupacion');


/* =================== PLANTILLA ==================*/

Route::post('parametros/store_plantilla', 'RRHH\rrhhParametrosController@store_plantilla');
Route::post('parametros/update_plantilla','RRHH\rrhhParametrosController@actualizarEstadoPlantilla')->name('update_estado.plantilla');
Route::post('parametros/editar_plantilla', 'RRHH\rrhhParametrosController@editar_plantilla');


