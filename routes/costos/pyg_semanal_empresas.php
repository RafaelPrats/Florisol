<?php

Route::get('pyg_semanal_empresas', 'Costos\pygEmpresasController@inicio');
Route::get('pyg_semanal_empresas/listar_reporte', 'Costos\pygEmpresasController@listar_reporte');