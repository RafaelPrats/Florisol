<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yura\Modelos\Submenu;
use yura\Modelos\UsoChatbot;
use yura\Modelos\Usuario;

class ChatbotController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.ai.chatbot.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function cargar_chatbot(Request $request)
    {
        $usuario = Usuario::find(session('id_usuario'));
        $reporte = $usuario->chatbot_activo;
        if ($reporte == '') {
            $reporte = 'v_resumen';
            $usuario->chatbot_activo = 'v_resumen';
            $usuario->save();
        }
        return view('layouts.adminlte.partials.cargar_chatbot', [
            'reporte' => $reporte
        ]);
    }

    public function cambiar_reporte(Request $request)
    {
        try {
            DB::beginTransaction();
            $usuario = Usuario::find(session('id_usuario'));
            $usuario->chatbot_activo = $request->reporte;
            $usuario->save();

            DB::commit();
            $success = true;
            $msg = '😌Has <b>cambiado</b> el reporte a ' . getReportesChatBot()[$request->reporte]['cambio'];
        } catch (\Exception $e) {
            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function send_chat(Request $request)
    {
        try {
            $context = session('chat_context');

            if ($context && now()->diffInMinutes($context['timestamp']) > 15) {
                session()->forget('chat_context');
            }

            $question = $request->input('message');
            $usuario = Usuario::find(session('id_usuario'));
            $reporte = $usuario->chatbot_activo;
            if ($reporte == '') {
                $reporte = 'v_resumen';
                $usuario->chatbot_activo = 'v_resumen';
                $usuario->save();
            }

            // Registrar uso
            $model_uso = new UsoChatbot();
            $model_uso->id_usuario = session('id_usuario');
            $model_uso->pregunta = $question;

            // guardar memoria de la conversacion
            $isFollowUp = $this->isFollowUpQuestion($question);

            $questionForIA = $isFollowUp
                ? $this->buildContextualQuestion($question)
                : $question;

            // 1️⃣ Generar SQL
            $sql = $this->generateSqlFromIA($questionForIA, $reporte);

            $model_uso->consulta_sql = $sql;
            $model_uso->save();
            $id = DB::table('uso_chatbot')
                ->select(DB::raw('max(id_uso_chatbot) as id'))
                ->get()[0]->id;

            // 2️⃣ Validar SQL
            $this->validateSql($sql, $id);

            // 3️⃣ Ejecutar SQL (readonly)
            //$data = DB::connection('mysql_readonly')->select(DB::raw($sql));
            $data = DB::select(DB::raw($sql));

            // Guardar datos para exportación
            //session(['chatbot_last_data' => $data]);

            // 🔥 NUEVO: generar tabla HTML
            $tableHtml = $this->generateHtmlTable($data);

            // generar data del chart
            $chartData = $this->generateChartData($data);
            //dd($data, $chartData);

            // 4️⃣ Generar respuesta natural
            $answer = $this->generateNaturalAnswer($question, $data);
            $model_uso = UsoChatbot::find($id);
            $model_uso->respuesta = 'OK';
            $model_uso->save();
        } catch (\Exception $e) {
            $msg = '❌ Ha ocurrido un problema al interpretar la pregunta: <b>' . $e->getMessage() /*. ' ' . $e->getFile() . ' ' . $e->getLine()*/ . '</b>';
            return [
                'mensaje' => $msg,
                'success' => false
            ];
        }

        session([
            'chat_context' => [
                'last_sql'      => $sql,
                'last_question' => $question,
                'timestamp'     => now()
            ]
        ]);

        return response()->json([
            'sql' => $sql,
            'answer' => $answer,
            'table' => $tableHtml,
            'chart'  => $chartData,
            'data_encode'  => json_encode($data),
            'mensaje' => '',
            'success' => true
        ]);
    }

    private function generateSqlFromIA($question, $reporte)
    {
        $client = new Client();

        $prompt = "
Eres un generador de SQL SOLO DE LECTURA.

REGLAS:
- SOLO SELECT
- PROHIBIDO INSERT, UPDATE, DELETE, DROP, ALTER
- SOLO usar las tablas dentro del schema
- NO subconsultas
- No debes mostrar los ids
- Si no puedes responder devuelve ERROR_NO_SQL

Schema:
" . getSchemasChatBot()[$reporte] . "

La siguiente instrucción puede ser:
- una pregunta nueva
- o una modificación de una consulta anterior (ej: 'ahora', 'solo', 'lo mismo')

{$question}

Devuelve SOLO SQL sin la palabra sql al inicio.
";

        //dd($prompt);

        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => env('OPENAI_MODEL'),
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0,
            ],
        ]);

        $body = json_decode($response->getBody(), true);

        return trim($body['choices'][0]['message']['content']);
    }

    private function validateSql($sql, $id_model)
    {
        $sql = strtolower($sql);
        $model = UsoChatbot::find($id_model);
        //dd($sql, starts_with(trim($sql), 'select'));

        if (!starts_with(trim($sql), 'select')) {
            $model->respuesta = 'SQL no permitido';
            $model->save();
            abort(400, 'SQL no permitido');
        }

        $forbidden = ['insert', 'update', 'delete', 'drop', 'alter', 'truncate'];
        foreach ($forbidden as $word) {
            if (strpos($sql, $word) !== false) {
                $model->respuesta = 'SQL peligroso';
                $model->save();
                abort(400, 'SQL peligroso');
            }
        }

        $tablasPermitidas = [
            'v_resumen',
            'v_bouquets',
            'v_detalle_bouquets'
        ];
        $permitido = false;
        foreach ($tablasPermitidas as $tabla) {
            if (strpos($sql, $tabla) !== false) {
                $permitido = true;
                break;
            }
        }
        if (!$permitido) {
            $model->respuesta = 'Tabla no permitida';
            $model->save();
            abort(400, 'Tabla no permitida');
        }
    }

    private function generateNaturalAnswer($question, $data)
    {
        $client = new Client();
        $prompt1 = 'Responde en español, de forma clara, usando exactamente el nombre de los valores como aparece en la data, en lenguaje natural para una persona que no es informática y usando solo los datos proporcionados.';
        $prompt2 = 'Responde en español, de forma clara y siempre usando solo los datos proporcionados.';
        $prompt3 = 'Responde en español, de forma clara. Cuando la data sea muy grande o compleja en forma de tabla, responder solamente diciendo que para ver toda la informacion, que revise la tabla, SIN DESCRIBIR el contenido de la respuesta. Usando exactamente el nombre de los valores como aparece en la data, en lenguaje natural para una persona que no es informática y usando solo los datos proporcionados.';

        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => env('OPENAI_MODEL'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $prompt3
                    ],
                    [
                        'role' => 'user',
                        'content' => json_encode([
                            'pregunta' => $question,
                            'datos' => $data
                        ])
                    ]
                ],
                'temperature' => 0.2,
            ],
        ]);

        $body = json_decode($response->getBody(), true);

        return $body['choices'][0]['message']['content'];
    }

    private function generateHtmlTable(array $data)
    {
        if (count($data) === 0) {
            return null;
        }

        $html = '<table class="chat-table">';
        $html .= '<thead><tr>';

        // Encabezados
        foreach (array_keys((array)$data[0]) as $column) {
            $html .= '<th>' . e(mb_strtoupper(str_replace('_', ' ', $column))) . '</th>';
        }

        $html .= '</tr></thead><tbody>';

        // Filas
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ((array)$row as $value) {
                if (is_numeric($value)) {
                    $value = number_format($value, 0);
                }

                $html .= '<td>' . e($value) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }

    private function generateChartData(array $data)
    {
        if (count($data) === 0) {
            return null;
        }

        $firstRow = (array) $data[0];

        $labelColumn = null;
        $valueColumn = null;

        // 1️⃣ Priorizar columnas conocidas como dimensión
        $dimensionCandidates = ['mes', 'semana', 'anno', 'fecha'];

        foreach ($dimensionCandidates as $candidate) {
            if (array_key_exists($candidate, $firstRow)) {
                $labelColumn = $candidate;
                break;
            }
        }

        // 2️⃣ Detectar métrica (numérica distinta a la dimensión)
        foreach ($firstRow as $key => $value) {
            if ($key !== $labelColumn && is_numeric($value)) {
                $valueColumn = $key;
                break;
            }
        }

        // 3️⃣ Fallback: primera columna como label
        if ($labelColumn === null) {
            $keys = array_keys($firstRow);
            $labelColumn = $keys[0];
        }

        // 4️⃣ Validación final
        if ($labelColumn === null || $valueColumn === null) {
            return null;
        }

        $labels = [];
        $values = [];

        foreach ($data as $row) {
            $row = (array) $row;

            $labels[] = (string) $row[$labelColumn];
            $values[] = (float) $row[$valueColumn];
        }

        return [
            'type'   => in_array($labelColumn, ['fecha', 'mes', 'semana', 'anno']) ? 'line' : 'bar',
            'label'  => ucfirst(str_replace('_', ' ', $valueColumn)),
            'labels' => $labels,
            'values' => $values
        ];
    }

    private function isFollowUpQuestion($question)
    {
        $keywords = [
            'eso',
            'lo mismo',
            'ahora',
            'igual',
            'solo',
            'también',
            'pero',
            'y',
            'además',
            'ahora',
            'ahora solo'
        ];

        $question = mb_strtolower($question);

        foreach ($keywords as $word) {
            if (strpos($question, $word) !== false) {
                return true;
            }
        }

        return false;
    }

    private function buildContextualQuestion($question)
    {
        $context = session('chat_context');

        if (!$context) {
            return $question;
        }

        // Ej: "ahora en abril"
        return "
Contexto previo:
- Pregunta anterior: {$context['last_question']}
- SQL anterior: {$context['last_sql']}

Nueva instrucción del usuario:
{$question}

Interpreta la nueva instrucción como una MODIFICACIÓN del contexto previo.
";
    }

    public function exportExcel(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_chat($spread, $request);

        $fileName = "ReporteChat.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_chat($spread, $request)
    {
        $data = json_decode($request->data);
        $encabezados = array_keys((array)$data[0]);

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Reporte');

        $row = 1;
        $col = -1;
        foreach ($encabezados as $th) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, mb_strtoupper(str_replace('_', ' ', $th)));
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($data as $pos_d => $data) {
            $row++;
            $col = -1;
            $encabezados = array_keys((array)$data);
            foreach ($encabezados as $indice) {
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $data->{$indice});
            }
        }

        //setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
