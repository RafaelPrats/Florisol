<?php

Route::get('resumen_ebitda', 'CRM\ResumenEBITDAController@inicio');
Route::get('resumen_ebitda/buscar_resumen_ebitda', 'CRM\ResumenEBITDAController@buscar_resumen_ebitda');
Route::get('resumen_ebitda/select_desglose_planta', 'CRM\ResumenEBITDAController@select_desglose_planta');
Route::get('resumen_ebitda/refrescar_tallos_cosechados', 'CRM\ResumenEBITDAController@refrescar_tallos_cosechados');
Route::get('resumen_ebitda/refrescar_tallos_vendidos', 'CRM\ResumenEBITDAController@refrescar_tallos_vendidos');
Route::get('resumen_ebitda/refrescar_dinero_ingresado', 'CRM\ResumenEBITDAController@refrescar_dinero_ingresado');
Route::get('resumen_ebitda/refrescar_precio_tallo', 'CRM\ResumenEBITDAController@refrescar_precio_tallo');
Route::get('resumen_ebitda/refrescar_tallos_m2', 'CRM\ResumenEBITDAController@refrescar_tallos_m2');
Route::get('resumen_ebitda/refrescar_venta_m2_anno', 'CRM\ResumenEBITDAController@refrescar_venta_m2_anno');