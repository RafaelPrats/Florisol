<?php

Route::get('control_diario', 'RRHH\rrhhControlDiarioController@inicio');
Route::get('control_diario/obtener_actividad_mano_obra', 'RRHH\rrhhControlDiarioController@obtener_actividad_mano_obra');
Route::get('control_diario/buscar_control_diario', 'RRHH\rrhhControlDiarioController@buscar_control_diario');
Route::post('control_diario/store_control_diario', 'RRHH\rrhhControlDiarioController@store_control_diario');
Route::get('control_diario/add_control_personal', 'RRHH\rrhhControlDiarioController@add_control_personal');
Route::post('control_diario/store_control_personal', 'RRHH\rrhhControlDiarioController@store_control_personal');
Route::post('control_diario/delete_control_personal', 'RRHH\rrhhControlDiarioController@delete_control_personal');
Route::post('control_diario/compare_photo', 'RRHH\rrhhControlDiarioController@compare_photo');
Route::get('control_diario/modal_foto', 'RRHH\rrhhControlDiarioController@modal_foto');
