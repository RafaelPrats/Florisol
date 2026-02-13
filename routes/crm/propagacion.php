<?php

Route::get('dashboard_propagacion', 'Propagacion\propagDashboardController@inicio');
Route::get('dashboard_propagacion/listar_graficas', 'Propagacion\propagDashboardController@listar_graficas');