<div class="input-group" id="accion_1">
    <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
        <i class="fa fa-fw fa-leaf"></i> Variedad
    </div>
    <select name="filtro_predeterminado_planta" id="filtro_predeterminado_planta"
            class="form-control"
            onchange="select_planta_global($(this).val(), 'id_variedad', '', '<option value= selected>Seleccione</option>')">
        <option value="">Variedad</option>
        @foreach(getPlantas() as $p)
            <option value="{{$p->id_planta}}">{{$p->nombre}}</option>
        @endforeach
    </select>
    <div class="input-group-addon bg-yura_dark">
        <i class="fa fa-fw fa-leaf"></i> Tipo
    </div>
    <select name="id_variedad" id="id_variedad"
            class="form-control">
        <option value="">Tipo</option>
    </select>
    <div class="input-group-addon bg-yura_dark">
        <i class="fa fa-fw fa-calendar"></i> Año
    </div>
    <select name="anno" id="anno" class="form-control" required>
        <option value="">Año</option>
        @foreach($annos as $item)
            <option value="{{$item->anno}}">{{$item->anno}}</option>
        @endforeach
    </select>
    <div class="input-group-btn">
        <button type="button" class="btn btn-yura_primary" onclick="listar()">
            <i class="fa fa-fw fa-check"></i> Buscar
        </button>
    </div>
</div>