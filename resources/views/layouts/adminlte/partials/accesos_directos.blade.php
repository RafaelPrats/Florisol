@foreach($accesos as $item)
    <li class="acceso_directo" title="{{$item->submenu->nombre}}">
        <a href="javascript:void(0)" onclick="cargar_url('{{$item->submenu->url}}')">
            @if($item->id_icono != '')
                <i class="fa fa-fw fa-{{$item->icono->nombre}} text-color_yura"></i>
            @else
                {{str_limit($item->submenu->nombre, 3)}}
            @endif
        </a>
    </li>
@endforeach

<li class="dropdown" title="Accesos Directos" id="btn_acceso_directo_mini">
    <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        <i class="fa fa-star-o text-color_yura"></i>
    </a>
    <ul class="dropdown-menu">
        @foreach($accesos as $item)
            <li class="acceso_directo-mini" title="{{$item->submenu->nombre}}">
                <a href="javascript:void(0)" onclick="cargar_url('{{$item->submenu->url}}')">
                    @if($item->id_icono != '')
                        <i class="fa fa-fw fa-{{$item->icono->nombre}} text-color_yura"></i> {{$item->submenu->nombre}}
                    @else
                        {{str_limit($item->submenu->nombre, 3)}}
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
</li>

<input type="hidden" id="count_accesos_directos" value="{{count($accesos)}}">

<script>
    $(window).ready(function () {
        if ($(document).width() >= 1024 && $('#count_accesos_directos').val() <= 2) { // mostrar accesos directos completos
            $('.acceso_directo').removeClass('hidden');
            $('#btn_acceso_directo_mini').addClass('hidden');
        } else {    // mostrar accesos directos minimizados
            $('.acceso_directo').addClass('hidden');
            $('#btn_acceso_directo_mini').removeClass('hidden');
        }
        if ($(document).width() >= 980) { // mostrar username
            $('#span-master_username').removeClass('hidden');
        } else {    // ocultar username
            $('#span-master_username').addClass('hidden');
        }
        if ($(document).width() >= 900) { // mostrar reportes rapidos completos
            $('#div_submenu_crm').removeClass('hidden');
            $('#div_submenu_crm_mini').addClass('hidden');
        } else {    // mostrar reportes rapidos minimizados
            $('#div_submenu_crm_mini').removeClass('hidden');
            $('#div_submenu_crm').addClass('hidden');
        }
        if ($(document).width() >= 500) { // mostrar fincas propias completos
            $('#li-master_fincas_propias').removeClass('hidden');
            $('#li-master_fincas_propias_mini').addClass('hidden');
        } else {    // mostrar fincas propias minimizados
            $('#li-master_fincas_propias_mini').removeClass('hidden');
            $('#li-master_fincas_propias').addClass('hidden');
        }
    });
</script>
