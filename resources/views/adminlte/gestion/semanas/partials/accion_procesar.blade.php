<div class="row" id="accion_2">
    <div class="col-md-2">
        <select name="filtro_predeterminado_planta" id="filtro_predeterminado_planta"
                class="form-control"
                onchange="select_planta_global($(this).val(), 'id_variedad', '', '<option value= selected>Seleccione</option>')">
            <option value="">Planta</option>
            @foreach(getPlantas() as $p)
                <option value="{{$p->id_planta}}">{{$p->nombre}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <select name="id_variedad" id="id_variedad"
                class="form-control">
            <option value="">Tipo</option>
        </select>
    </div>
    <div class="col-md-2">
        <input type="number" id="anno" name="anno" class="form-control" min="2019" value="2021" required>
    </div>
    <div class="col-md-3">
        <div class="form-group input-group">
            <span class="input-group-addon span-addon" style="background-color: #e9ecef;">Fecha inicial</span>
            <input type="date" id="fecha_inicial" name="fecha_inicial" class="form-control" required value="{{date('Y-m-d')}}">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group input-group">
            <span class="input-group-addon span-addon" style="background-color: #e9ecef;">Fecha fin</span>
            <input type="date" id="fecha_final" name="fecha_final" class="form-control" required>
        </div>
    </div>
    <div class="col-md-2">
        <button type="button" class="btn btn-block btn-primary" onclick="procesar()">
            <i class="fa fa-fw fa-check"></i> Continuar
        </button>
    </div>
</div>

<script>
    //set_min_today($('#fecha_inicial'));
    set_min_today($('#fecha_final'));
</script>