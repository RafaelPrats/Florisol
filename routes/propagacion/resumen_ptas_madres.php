<?php

Route::get('resumen_plantas_madres', 'Propagacion\ResumenPtasMadresController@inicio');
Route::get('resumen_plantas_madres/listar_resumen', 'Propagacion\ResumenPtasMadresController@listar_resumen');
Route::post('resumen_plantas_madres/job_update_propag', 'Propagacion\ResumenPtasMadresController@job_update_propag');
Route::get('resumen_plantas_madres/select_desglose_planta', 'Propagacion\ResumenPtasMadresController@select_desglose_planta');