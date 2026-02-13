<form id="form-igualar_datos">
    <div class="row">
        @if($curva == 'true')
            <div class="col-md-6">
                <div class="form-group input-group">
                <span class="input-group-addon" style="background-color: #e9ecef;">
                    Curva
                </span>
                    <input type="text" id="curva" name="curva" class="form-control" maxlength="250" required
                           placeholder="10-20-40-30">
                </div>
            </div>
        @else
            <input type="hidden" id="curva" name="curva" value="">
        @endif
        @if($desecho == 'true')
            <div class="col-md-6">
                <div class="form-group input-group">
                <span class="input-group-addon" style="background-color: #e9ecef;">
                    Desecho %
                </span>
                    <input type="number" class="text-center form-control" name="desecho" id="desecho" required maxlength="2" min="0"
                           max="99">
                </div>
            </div>
        @else
            <input type="hidden" id="desecho" name="desecho" value="">
        @endif
    </div>
    <div class="row">
        @if($semana_poda == 'true')
            <div class="col-md-6">
                <div class="form-group input-group">
                <span class="input-group-addon" style="background-color: #e9ecef;">
                    Semana Poda
                </span>
                    <input type="number" class="text-center form-control" name="semana_poda" id="semana_poda" required maxlength="2" min="0"
                           max="53">
                </div>
            </div>
        @else
            <input type="hidden" id="semana_poda" name="semana_poda" value="">
        @endif
        @if($semana_siembra == 'true')
            <div class="col-md-6">
                <div class="form-group input-group">
                <span class="input-group-addon" style="background-color: #e9ecef;">
                    Semana Siembra
                </span>
                    <input type="number" class="text-center form-control" name="semana_siembra" id="semana_siembra" required maxlength="2"
                           min="0" max="53">
                </div>
            </div>
        @else
            <input type="hidden" id="semana_siembra" name="semana_siembra" value="">
        @endif
    </div>
    <div class="row">
        @if($tallos_planta_siembra == 'true')
            <div class="col-md-6">
                <div class="form-group input-group">
                <span class="input-group-addon" style="background-color: #e9ecef;">
                    Tallos/planta Siembra
                </span>
                    <input type="number" class="text-center form-control" name="tallos_planta_siembra" id="tallos_planta_siembra" required
                           min="0">
                </div>
            </div>
        @else
            <input type="hidden" id="tallos_planta_siembra" name="tallos_planta_siembra" value="">
        @endif
        @if($tallos_planta_poda == 'true')
            <div class="col-md-6">
                <div class="form-group input-group">
                <span class="input-group-addon" style="background-color: #e9ecef;">
                    Tallos/planta Poda
                </span>
                    <input type="number" class="text-center form-control" name="tallos_planta_poda" id="tallos_planta_poda" required
                           min="0">
                </div>
            </div>
        @else
            <input type="hidden" id="tallos_planta_poda" name="tallos_planta_poda" value="">
        @endif
    </div>
    <div class="row">
        @if($tallos_ramo_siembra == 'true')
            <div class="col-md-6">
                <div class="form-group input-group">
                <span class="input-group-addon" style="background-color: #e9ecef;">
                    Tallos/ramo Siembra
                </span>
                    <input type="number" class="text-center form-control" name="tallos_ramo_siembra" id="tallos_ramo_siembra" required
                           min="0">
                </div>
            </div>
        @else
            <input type="hidden" id="tallos_ramo_siembra" name="tallos_ramo_siembra" value="">
        @endif
        @if($tallos_ramo_poda == 'true')
            <div class="col-md-6">
                <div class="form-group input-group">
                <span class="input-group-addon" style="background-color: #e9ecef;">
                    Tallos/ramo Poda
                </span>
                    <input type="number" class="text-center form-control" name="tallos_ramo_poda" id="tallos_ramo_poda" required
                           min="0">
                </div>
            </div>
        @else
            <input type="hidden" id="tallos_ramo_poda" name="tallos_ramo_poda" value="">
        @endif
    </div>
    <div class="row">
        @if($plantas_iniciales == 'true')
            <div class="col-md-6">
                <div class="form-group input-group">
                <span class="input-group-addon" style="background-color: #e9ecef;">
                    Plantas iniciales
                </span>
                    <input type="number" class="text-center form-control" name="plantas_iniciales" id="plantas_iniciales" required min="0">
                </div>
            </div>
        @else
            <input type="hidden" id="plantas_iniciales" name="plantas_iniciales" value="" min="0">
        @endif
        @if($densidad == 'true')
            <div class="col-md-6">
                <div class="form-group input-group">
                <span class="input-group-addon" style="background-color: #e9ecef;">
                    Densidad
                </span>
                    <input type="number" class="text-center form-control" name="densidad" id="densidad" required min="0">
                </div>
            </div>
        @else
            <input type="hidden" id="densidad" name="densidad" value="" min="0">
        @endif
    </div>
    <div class="row">
        @if($porcent_bqt == 'true')
            <div class="col-md-6">
                <div class="form-group input-group">
                <span class="input-group-addon" style="background-color: #e9ecef;">
                    Porcentaje Bqt.
                </span>
                    <input type="number" class="text-center form-control" name="porcent_bqt" id="porcent_bqt" required min="0">
                </div>
            </div>
        @else
            <input type="hidden" id="porcent_bqt" name="porcent_bqt" value="" min="0">
        @endif
        @if($porcent_export == 'true')
            <div class="col-md-6">
                <div class="form-group input-group">
                <span class="input-group-addon" style="background-color: #e9ecef;">
                    Porcentaje Export.
                </span>
                    <input type="number" class="text-center form-control" name="porcent_export" id="porcent_export" required min="0">
                </div>
            </div>
        @else
            <input type="hidden" id="porcent_export" name="porcent_export" value="" min="0">
        @endif
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group input-group">
                <span class="input-group-addon" style="background-color: #e9ecef;">
                    <input type="checkbox" id="check_variedades" name="check_variedades">
                    <label for="check_variedades" class="mouse-hand">Igualar para las variedades de la misma planta</label>
                </span>
            </div>
        </div>
    </div>
</form>