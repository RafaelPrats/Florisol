<?php

Route::get('dashboard_temperaturas', 'Proyecciones\DashboardTemperaturasController@inicio');
Route::get('dashboard_temperaturas/listar_graficas_temperaturas', 'Proyecciones\DashboardTemperaturasController@listar_graficas_temperaturas');
