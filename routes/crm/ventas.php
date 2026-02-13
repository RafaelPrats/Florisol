<?php

Route::get('crm_ventas','CRM\crmVentasController@inicio');
Route::get('crm_ventas/listar_graficas','CRM\crmVentasController@listar_graficas');
Route::get('crm_ventas/listar_ranking','CRM\crmVentasController@listar_ranking');
