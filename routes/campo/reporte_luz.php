<?php

Route::get('reporte_luz', 'Campo\ReporteLuzController@inicio');
Route::get('reporte_luz/listar_reporte_luz', 'Campo\ReporteLuzController@listar_reporte_luz');
Route::post('reporte_luz/listar_row_luz', 'Campo\ReporteLuzController@listar_row_luz');
Route::get('reporte_luz/exportar_reporte', 'Campo\ReporteLuzController@exportar_reporte');
