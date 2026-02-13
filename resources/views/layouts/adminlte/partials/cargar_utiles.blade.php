{{-- resumen_jobs --}}
<div class="box box-success sombra_pequeña">
    <div class="box-header with-border">
        <h3 class="box-title mouse-hand" onclick="$('#div_resumen_jobs').toggleClass('hidden')">
            Resumen tabla jobs
        </h3>
    </div>
    <div class="box-body" id="div_resumen_jobs">
        @if(count($resumen_jobs) > 0)
            <table class="table-bordered" style="width: 100%; border-radius: 18px 18px 18px 18px; border: 1px solid #9d9d9d">
                <tr id="tr_fija_top_0">
                    <th class="text-center th_yura_green" style="border-radius: 18px 0 0 0">
                        Cola
                    </th>
                    <th class="text-center th_yura_green">
                        Intentos
                    </th>
                    <th class="text-center th_yura_green" style="border-radius: 0 18px 0 0">
                        Cantidad
                    </th>
                </tr>
                @php
                    $total_intentos = 0;
                @endphp
                @foreach($resumen_jobs as $item)
                    @php
                        $total_intentos += $item->cant;
                    @endphp
                    <tr id="tr_fija_top_0">
                        <td class="text-center" style="border-color: #9d9d9d">
                            {{$item->queue}}
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            {{$item->attempts}}
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            {{$item->cant}}
                        </td>
                    </tr>
                @endforeach
                {{-- TOTALES --}}
                <tr>
                    <th class="th_yura_green" style="padding-left: 10px; border-radius: 0 0 0 18px" colspan="2">
                        TOTAL
                    </th>
                    <th class="text-center th_yura_green" style="border-radius: 0 0 18px 0">
                        {{number_format($total_intentos)}}
                    </th>
                </tr>
            </table>
        @else
            <div class="alert alert-info text-center">Nada que mostrar</div>
        @endif
    </div>
</div>

{{-- archivos_subidos --}}
<div class="box box-success sombra_pequeña">
    <div class="box-header with-border">
        <h3 class="box-title mouse-hand" onclick="$('#div_archivos_subidos').toggleClass('hidden')">
            Listado de archivos subidos
        </h3>
    </div>
    <div class="box-body" id="div_archivos_subidos">
        @if(count($archivos_subidos) > 0)
            @php
                $iconos = [
                    'xlsx'=>'fa-file-excel-o',
                    'csv'=>'fa-file-excel-o',
                    'pdf'=>'fa-file-pdf-o',
                    'txt'=>'fa-file-text',
                ];
            @endphp
            <ul>
                @foreach($archivos_subidos as $item)
                    <li>
                        @if(isset($iconos[explode('.', $item)[1]]))
                            <i class="fa fa-fw {{$iconos[explode('.', $item)[1]]}}"></i>
                        @endif
                        {{$item}}
                    </li>
                @endforeach
            </ul>
        @else
            <div class="alert alert-info text-center">Nada que mostrar</div>
        @endif
    </div>
</div>