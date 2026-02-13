<?php

Route::get('importar_unosoft', 'ImportarUnosoftController@inicio');
Route::post('importar_unosoft/importar', 'ImportarUnosoftController@importar');
Route::get('importar_unosoft/descargar_plantilla', 'ImportarUnosoftController@descargar_plantilla');