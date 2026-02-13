@if (count($person) > 0)
    @php
        $enumeracion = 1;

    @endphp
    <div id="table_personal">
        <table class="table-responsive table-bordered"
            style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0" id="table_content_personal">
            <tr>
                <th class="text-center th_yura_green" style="border-color: white; border-radius:  18px 0 0 0">#
                </th>
                <th class="text-center th_yura_green" style="border-color: white;">Nombre
                <th class="text-center th_yura_green" style="border-color: white;">Apellido</th>
                <th class="text-center th_yura_green" style="border-color: white;">Doc. de Identidad</th>
                <th class="text-center th_yura_green"
                    style="border-color: white; border-radius: 0  18px 0 0; width: 120px">Opciones
                </th>
            </tr>
            @foreach ($person as $item)
                <tr>

                    <td class="text-center" style="border-color: #9d9d9d">@php  echo $enumeracion++;@endphp </td>
                    <td class="text-center" style="border-color: #9d9d9d">{{ $item->nombre }}</td>
                    <td class="text-center" style="border-color: #9d9d9d">{{ $item->apellido }}</td>
                    <td class="text-center" style="border-color: #9d9d9d">{{ $item->cedula_identidad }}</td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <div class="btn-group">
                            {{-- <button type="button" class="btn btn-xs btn-yura_dark" title="Históricos"
                                onclick="ver_historico('{{ $item->id_personal }}')"
                                id="btn_ver_historico_{{ $item->id_personal }}">
                                <i class="fa fa-fw fa-file"></i>
                            </button> --}}
                            @if ($estado == 0)
                                {{-- <button type="button" class="btn btn-xs btn-yura_warning" title="Ficha Personal"
                                    onclick="ficha_personal('{{ $item->id_personal }}')"
                                    id="btn_ficha_personal_{{ $item->id_personal }}">
                                    <i class="fa fa-fw fa-eye" style="color: #ffff"></i>
                                </button> --}}
                                <button type="button" class="btn btn-xs btn-yura_primary" title="Incorporar"
                                    onclick="ver_incorporar_personal('{{ $item->id_personal }}')"
                                    id="btn_incorporar_personal_{{ $item->id_personal }}">
                                    <i class="fa fa-fw fa-adjust"></i>
                                </button>
                            @endif
                            @if ($estado == 1)
                                <button type="button" class="btn btn-xs btn-yura_warning" title="Desvincular"
                                    onclick="ver_desincorporar_personal('{{ $item->id_personal }}')"
                                    id="btn_desincorporar_personal_{{ $item->id_personal }}">
                                    <i class="fa fa-fw fa-toggle-on"></i>
                                </button>

                                <button type="button" class="btn btn-xs btn-yura_primary" title="Editar"
                                    onclick="ver_personal('{{ $item->id_personal }}')"
                                    id="btn_ver_personal_{{ $item->id_personal }}">
                                    <i class="fa fa-fw fa-edit"></i>
                                </button>
                            @endif
                            <button type="button" class="btn btn-xs btn-yura_danger" title="Eliminar"
                                onclick="eliminar_personal('{{ $item->id_personal }}')"
                                id="btn_eliminar_personal_{{ $item->id_personal }}">
                                <i class="fa fa-fw fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
    <script>
        function update_personal(id) {
            datos = {
                _token: '{{ csrf_token() }}',
                id_personal: id,
                nombre: $('#input_nombre_' + id).val(),
            };
            post_jquery('{{ url('personal/editar_personal') }}', datos, function() {

            });
        }

        function eliminar_personal(id_personal) {
            modal_quest('modal_quest_del_documento',
                '<div class="alert alert-info text-center">¿Está seguro de eliminar éste personal?</div>',
                '<i class="fa fa-fw fa-trash"></i> Eliminar información', true, false, '{{ isPC() ? '35%' : '' }}',
                function() {
                    $.LoadingOverlay('show');
                    datos = {
                        _token: '{{ csrf_token() }}',
                        id_personal: id_personal
                    };
                    post_jquery('{{ url('personal/eliminar_trabajador') }}', datos, function() {
                        cerrar_modals();
                        trabajador();
                    });
                    $.LoadingOverlay('hide');
                });
        }
    </script>
@else
    <div class="alert alert-info text-center">No se han encontrado coincidencias</div>
@endif
@section('script_final')
    @include('adminlte.gestion.rrhh.personal.script')
@endsection
