<?php

Route::get('proy_normales', 'Proyecciones\proyNoPerennesController@inicio');
Route::get('proy_normales/listar_proyecciones', 'Proyecciones\proyNoPerennesController@listar_proyecciones');
Route::get('proy_normales/exportar_reporte_proyecciones', 'Proyecciones\proyNoPerennesController@exportar_reporte_proyecciones');
