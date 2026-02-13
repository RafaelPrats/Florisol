<select class="select-yura_default" id="select_submenu_crm" onchange="select_submenu_crm($(this).val())">
    <option value="">Dashboards</option>
    <option value="dashboard_inicial">Dashboard Inicial</option>
    @foreach($getSubmenusOfUser as $item)
        @if($item->tipo == 'C')
            <option value="{{$item->url}}">{{$item->nombre}}</option>
        @endif
    @endforeach
</select>