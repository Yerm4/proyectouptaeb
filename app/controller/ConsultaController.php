<?php
namespace app\controller;

use app\model\Consulta;
use Exception;

class ConsultaController {
    private $db;

    public function __construct($conexion){
        $this->db = $conexion;
    }

    public function registrar() {
        try {
            $cedulaPaciente = isset($_POST['cedula_paciente']) ? trim($_POST['cedula_paciente']) : '';
            $motivo         = isset($_POST['motivo_de_visita']) ? trim($_POST['motivo_de_visita']) : '';
            $observaciones  = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';
            $medicamento    = isset($_POST['medicamento_suministrado']) ? trim($_POST['medicamento_suministrado']) : '';
            
            $sintomas       = isset($_POST['sintomas']) ? $_POST['sintomas'] : [];
            if (!is_array($sintomas)) {
                $sintomas = array_filter(array_map('trim', explode(',', $sintomas)));
            }

            $diagnosticos   = isset($_POST['diagnosticos']) ? $_POST['diagnosticos'] : [];
            if (!is_array($diagnosticos)) {
                $diagnosticos = array_filter(array_map('trim', explode(',', $diagnosticos)));
            }

            $condiciones    = isset($_POST['condiciones']) ? $_POST['condiciones'] : [];
            if (!is_array($condiciones)) {
                $condiciones = array_filter(array_map('intval', explode(',', $condiciones)));
            }

            $cedulaMedico = isset($_SESSION['cedula']) ? $_SESSION['cedula'] : '';

            if (empty($cedulaPaciente) || empty($motivo) || empty($cedulaMedico)) {
                throw new Exception("La consulta no fue registrada, revisa que todos los campos estén llenos");
            }

            $modeloConsulta = new Consulta($this->db);
            $resultado = $modeloConsulta->registrarConsulta($cedulaPaciente, $cedulaMedico, $motivo, $observaciones, $sintomas, $diagnosticos, $condiciones, $medicamento);

            if ($resultado === true) {
                unset($_SESSION['inputs']);
                $_SESSION["registro_status"] = "success";
                $_SESSION["registro_msg"] = "La consulta fue registrada exitosamente";
                header("Location: perfil");
                exit();
            } else {
                throw new Exception("La consulta no fue registrada, revisa que todos los campos estén llenos");
            }
        } catch (Exception $e) {
            $_SESSION['inputs'] = $_POST;
            $_SESSION["registro_status"] = "error";
            $_SESSION["registro_msg"] = $e->getMessage();
            header("Location: perfil");
            exit();
        }
    }

    public function buscarPatologiaAjax() {
        header('Content-Type: application/json');
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        if (strlen($q) < 2) {
            echo json_encode([]);
            exit();
        }
        $modeloConsulta = new Consulta($this->db);
        $resultados = $modeloConsulta->buscarPatologia($q);
        echo json_encode($resultados);
        exit();
    }

    public function buscarPacienteAjax() {
        header('Content-Type: application/json');
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        if (strlen($q) < 2) {
            echo json_encode([]);
            exit();
        }
        $modeloConsulta = new Consulta($this->db);
        $resultados = $modeloConsulta->buscarUsuario($q);
        echo json_encode($resultados);
        exit();
    }

    public function actualizar() {
        try {
            $idConsulta    = isset($_POST['id_consulta']) ? (int)$_POST['id_consulta'] : 0;
            $motivo        = isset($_POST['motivo_de_visita']) ? trim($_POST['motivo_de_visita']) : '';
            $observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';
            $medicamento   = isset($_POST['medicamento_suministrado']) ? trim($_POST['medicamento_suministrado']) : '';

            $sintomas      = isset($_POST['sintomas']) ? $_POST['sintomas'] : [];
            if (!is_array($sintomas)) {
                $sintomas = array_filter(array_map('trim', explode(',', $sintomas)));
            }

            $diagnosticos  = isset($_POST['diagnosticos']) ? $_POST['diagnosticos'] : [];
            if (!is_array($diagnosticos)) {
                $diagnosticos = array_filter(array_map('trim', explode(',', $diagnosticos)));
            }

            $condiciones   = isset($_POST['condiciones']) ? $_POST['condiciones'] : [];
            if (!is_array($condiciones)) {
                $condiciones = array_filter(array_map('intval', explode(',', $condiciones)));
            }

            if ($idConsulta <= 0 || empty($motivo)) {
                throw new Exception("La consulta no fue actualizada, revisa que todos los campos estén llenos");
            }

            $modeloConsulta = new Consulta($this->db);
            $resultado = $modeloConsulta->actualizarConsulta($idConsulta, $motivo, $observaciones, $sintomas, $diagnosticos, $condiciones, $medicamento);

            if ($resultado === true) {
                unset($_SESSION['inputs']);
                $_SESSION["registro_status"] = "success";
                $_SESSION["registro_msg"] = "La consulta fue actualizada exitosamente";
                header("Location: perfil");
                exit();
            } else {
                throw new Exception("La consulta no fue actualizada, revisa que todos los campos estén llenos");
            }
        } catch (Exception $e) {
            $_SESSION['inputs'] = $_POST;
            $_SESSION["registro_status"] = "error";
            $_SESSION["registro_msg"] = $e->getMessage();
            header("Location: perfil");
            exit();
        }
    }

    public function obtenerConsultasPacienteAjax() {
        header('Content-Type: application/json');
        $cedula = isset($_GET['cedula']) ? trim($_GET['cedula']) : '';
        if (empty($cedula)) {
            echo json_encode([]);
            exit();
        }
        $modeloConsulta = new Consulta($this->db);
        $resultados = $modeloConsulta->obtenerConsultasPorPaciente($cedula);
        echo json_encode($resultados);
        exit();
    }

    public function buscarCondicionAjax() {
        header('Content-Type: application/json');
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        if (strlen($q) < 2) {
            echo json_encode([]);
            exit();
        }
        $modeloConsulta = new Consulta($this->db);
        $resultados = $modeloConsulta->buscarCondicion($q);
        echo json_encode($resultados);
        exit();
    }

    public function obtenerCondicionesPacienteAjax() {
        header('Content-Type: application/json');
        $cedula = isset($_GET['cedula']) ? trim($_GET['cedula']) : '';
        if (empty($cedula)) {
            echo json_encode([]);
            exit();
        }
        $modeloConsulta = new Consulta($this->db);
        $resultados = $modeloConsulta->obtenerCondicionesPaciente($cedula);
        echo json_encode($resultados);
        exit();
    }

    public function buscarConsultasAjax() {
        header('Content-Type: application/json');
        $query = isset($_POST['query']) ? trim($_POST['query']) : '';
        $modeloConsulta = new Consulta($this->db);
        if (empty($query)) {
            $resultados = $modeloConsulta->obtenerConsultasRecientes(10);
        } else {
            $resultados = $modeloConsulta->buscarConsultas($query, 20);
        }
        echo json_encode($resultados);
        exit();
    }

    public function obtenerConsultaPorIdAjax() {
        header('Content-Type: application/json');
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            echo json_encode(['error' => 'ID de consulta inválido.']);
            exit();
        }
        $modeloConsulta = new Consulta($this->db);
        $consulta = $modeloConsulta->obtenerConsultaPorId($id);
        if ($consulta) {
            echo json_encode($consulta);
        } else {
            echo json_encode(['error' => 'No se encontró la consulta médica.']);
        }
        exit();
    }

    public function generarReporteMorbilidad() {
        try {
            $fechaInicio = isset($_POST['fecha_inicio']) ? trim($_POST['fecha_inicio']) : '';
            $fechaFin = isset($_POST['fecha_fin']) ? trim($_POST['fecha_fin']) : '';

            if (empty($fechaInicio) || empty($fechaFin)) {
                throw new \Exception("Debe especificar la fecha de inicio y la fecha de fin.");
            }
            if (strtotime($fechaInicio) > strtotime($fechaFin)) {
                throw new \Exception("La fecha de inicio no puede ser posterior a la fecha de fin.");
            }

            $modeloConsulta = new Consulta($this->db);
            $datos = $modeloConsulta->obtenerDatosReporteMorbilidad($fechaInicio, $fechaFin);
            $pacientesUnicos = count(array_unique(array_column($datos['registros'], 'paciente_cedula')));

            $nombreGenerador = 'Médico de Guardia';
            if (isset($_SESSION['cedula'])) {
                $stmtUser = $this->db->prepare("SELECT nombre, apellido FROM usuarios WHERE cedula = :cedula");
                $stmtUser->execute([':cedula' => (int)$_SESSION['cedula']]);
                $userLogged = $stmtUser->fetch(\PDO::FETCH_ASSOC);
                if ($userLogged) {
                    $nombreGenerador = $userLogged['nombre'] . ' ' . $userLogged['apellido'];
                }
            }

            $fechaInicioFmt = date('d/m/Y', strtotime($fechaInicio));
            $fechaFinFmt = date('d/m/Y', strtotime($fechaFin));
            $fechaEmision = date('d/m/Y H:i:s');

            $logoPath = __DIR__ . '/../../public/assets/media/img/uptaeb.jpg';
            $logoHtml = '<div style="width:55px;height:55px;background:#1e3a8a;color:#fff;font-weight:bold;font-size:16px;line-height:55px;text-align:center;border-radius:4px;">UPTAEB</div>';
            if (file_exists($logoPath)) {
                $ld = base64_encode(file_get_contents($logoPath));
                $logoHtml = '<img src="data:image/jpeg;base64,' . $ld . '" style="height:55px;width:auto;max-width:80px;">';
            }

            $rgbColores = [
                [59,130,246], [16,185,129], [245,158,11],
                [239,68,68], [139,92,246], [100,116,139],
                [236,72,153], [20,184,166]
            ];
            $hexColores = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#64748b','#ec4899','#14b8a6'];

            $prepararDatosPastel = function($datosAgrupados, $valKey, $labelKey, $maxSlices = 5) {
                if (empty($datosAgrupados)) return [];
                $totalVal = 0;
                foreach ($datosAgrupados as $d) $totalVal += (int)$d[$valKey];
                if ($totalVal === 0) return [];

                $sorted = $datosAgrupados;
                usort($sorted, function($a, $b) use ($valKey) {
                    return (int)$b[$valKey] <=> (int)$a[$valKey];
                });

                $result = [];
                $otherSum = 0;
                for ($i = 0; $i < count($sorted); $i++) {
                    if ($i < $maxSlices) {
                        $val = (int)$sorted[$i][$valKey];
                        $result[] = [
                            'label' => $sorted[$i][$labelKey],
                            'value' => $val,
                            'pct' => round(($val / $totalVal) * 100)
                        ];
                    } else {
                        $otherSum += (int)$sorted[$i][$valKey];
                    }
                }
                if ($otherSum > 0) {
                    $result[] = [
                        'label' => 'Otros',
                        'value' => $otherSum,
                        'pct' => round(($otherSum / $totalVal) * 100)
                    ];
                }
                return $result;
            };

            $generarPastelPNG = function($chartData) use ($rgbColores) {
                $size = 400;
                $img = imagecreatetruecolor($size, $size);
                $white = imagecolorallocate($img, 255, 255, 255);
                imagefill($img, 0, 0, $white);

                if (empty($chartData)) {
                    $gris = imagecolorallocate($img, 148, 163, 184);
                    imagestring($img, 5, (int)($size / 2 - 30), (int)($size / 2 - 7), 'Sin datos', $gris);
                    ob_start();
                    imagepng($img);
                    $data = ob_get_clean();
                    return base64_encode($data);
                }

                $totalVal = 0;
                foreach ($chartData as $item) $totalVal += $item['value'];

                $cx = (int)($size / 2);
                $cy = (int)($size / 2);
                $d = $size - 40;
                $startAngle = 270;
                $accumulated = 0;

                foreach ($chartData as $idx => $item) {
                    $c = $rgbColores[$idx % count($rgbColores)];
                    $color = imagecolorallocate($img, $c[0], $c[1], $c[2]);
                    $accumulated += $item['value'];

                    if ($idx === count($chartData) - 1) {
                        $endAngle = 270 + 360;
                    } else {
                        $endAngle = 270 + (int)round(($accumulated / $totalVal) * 360);
                    }

                    imagefilledarc($img, $cx, $cy, $d, $d, $startAngle, $endAngle, $color, IMG_ARC_PIE);
                    $startAngle = $endAngle;
                }

                $borde = imagecolorallocate($img, 203, 213, 225);
                imagearc($img, $cx, $cy, $d, $d, 0, 360, $borde);

                ob_start();
                imagepng($img);
                $data = ob_get_clean();
                return base64_encode($data);
            };

            $htmlLeyenda = function($chartData) use ($hexColores) {
                if (empty($chartData)) return '<div style="font-size:7px;color:#94a3b8;text-align:center;padding:4px;">Sin datos</div>';
                $h = '<table style="width:100%;border-collapse:collapse;margin-top:4px;">';
                foreach ($chartData as $idx => $item) {
                    $color = $hexColores[$idx % count($hexColores)];
                    $lbl = htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8');
                    if (mb_strlen($item['label']) > 22) $lbl = htmlspecialchars(mb_substr($item['label'], 0, 20), ENT_QUOTES, 'UTF-8') . '..';
                    $h .= '<tr>';
                    $h .= '<td style="width:8px;padding:1px 3px 1px 0;vertical-align:middle;"><div style="width:6px;height:6px;background:' . $color . ';border-radius:2px;"></div></td>';
                    $h .= '<td style="font-size:7px;color:#334155;font-weight:bold;padding:1px 2px;">' . $lbl . '</td>';
                    $h .= '<td style="font-size:7px;color:#64748b;text-align:right;padding:1px 0;white-space:nowrap;">' . $item['value'] . ' (' . $item['pct'] . '%)</td>';
                    $h .= '</tr>';
                }
                $h .= '</table>';
                return $h;
            };

            $datosBenef = $prepararDatosPastel($datos['tipos_beneficiario'], 'total', 'nombre_tipo');
            $datosNucleo = $prepararDatosPastel($datos['nucleos'], 'total', 'nombre_nucleo');
            $datosPnf = $prepararDatosPastel($datos['pnfs'], 'total', 'nombre_pnf');

            $imgBenef = $generarPastelPNG($datosBenef);
            $imgNucleo = $generarPastelPNG($datosNucleo);
            $imgPnf = $generarPastelPNG($datosPnf);

            $topPatologias = array_slice($datos['patologias'], 0, 10);
            $topCondiciones = array_slice($datos['condiciones_cronicas'], 0, 10);

            $options = new \Dompdf\Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            $dompdf = new \Dompdf\Dompdf($options);

            $html = '<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Helvetica, Arial, sans-serif; color: #1e293b; font-size: 10px; line-height: 1.3; margin: 0; padding: 0; }
    .hdr { width: 100%; border-bottom: 2px solid #1e3a8a; padding-bottom: 6px; margin-bottom: 8px; }
    .hdr-t { width: 100%; border-collapse: collapse; }
    .hdr-t td { vertical-align: middle; }
    .hdr-logo { width: 80px; }
    .hdr-info { text-align: left; padding-left: 10px; }
    .hdr-info h1 { font-size: 12px; color: #1e3a8a; margin: 0 0 2px 0; text-transform: uppercase; letter-spacing: 0.3px; }
    .hdr-info p { margin: 0; color: #64748b; font-size: 9.5px; font-weight: bold; }
    .hdr-meta { text-align: right; font-size: 8.5px; color: #475569; line-height: 1.4; }
    .kpi-t { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    .kpi-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 5px; text-align: center; }
    .kpi-num { font-size: 17px; font-weight: bold; color: #1e3a8a; }
    .kpi-lbl { font-size: 7px; color: #64748b; text-transform: uppercase; font-weight: bold; }
    .charts-t { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    .chart-cell { width: 33.3%; text-align: center; border: 1px solid #e2e8f0; border-radius: 4px; padding: 4px; background: #fff; vertical-align: top; }
    .chart-title { font-weight: bold; font-size: 8px; color: #1e3a8a; margin-bottom: 2px; text-transform: uppercase; }
    .cols-t { width: 100%; border-collapse: collapse; }
    .col-l { width: 50%; vertical-align: top; padding-right: 10px; }
    .col-r { width: 50%; vertical-align: top; padding-left: 10px; }
    .sec-title { font-size: 9px; font-weight: bold; color: #0f172a; border-bottom: 1px solid #cbd5e1; padding-bottom: 2px; margin-top: 6px; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.3px; }
    .dt { width: 100%; border-collapse: collapse; }
    .dt th { background: #1e3a8a; color: #fff; text-align: left; padding: 2px 4px; font-size: 7px; font-weight: bold; text-transform: uppercase; }
    .dt th:last-child { text-align: right; }
    .dt td { padding: 2px 4px; border-bottom: 1px solid #f1f5f9; font-size: 7.5px; }
    .dt tr:nth-child(even) td { background: #f8fafc; }
</style>
</head>
<body>

<div class="hdr">
    <table class="hdr-t">
        <tr>
            <td class="hdr-logo">' . $logoHtml . '</td>
            <td class="hdr-info">
                <h1>Universidad Polit&eacute;cnica Territorial Andr&eacute;s Eloy Blanco</h1>
                <p>Servicio M&eacute;dico &mdash; Reporte de Morbilidad</p>
            </td>
            <td class="hdr-meta">
                <strong>Periodo:</strong> ' . $fechaInicioFmt . ' al ' . $fechaFinFmt . '<br>
                <strong>Emisi&oacute;n:</strong> ' . $fechaEmision . '<br>
                <strong>Generado por:</strong> ' . htmlspecialchars($nombreGenerador, ENT_QUOTES, 'UTF-8') . '
            </td>
        </tr>
    </table>
</div>

<table class="kpi-t">
    <tr>
        <td style="width:33%;padding-right:5px;"><div class="kpi-card"><div class="kpi-num">' . $datos['total_consultas'] . '</div><div class="kpi-lbl">Consultas M&eacute;dicas</div></div></td>
        <td style="width:34%;padding:0 3px;"><div class="kpi-card"><div class="kpi-num">' . count($datos['patologias']) . '</div><div class="kpi-lbl">Patolog&iacute;as Identificadas</div></div></td>
        <td style="width:33%;padding-left:5px;"><div class="kpi-card"><div class="kpi-num">' . $pacientesUnicos . '</div><div class="kpi-lbl">Pacientes Atendidos</div></div></td>
    </tr>
</table>

<table class="charts-t">
    <tr>
        <td class="chart-cell" style="padding-right:5px;">
            <div class="chart-title">Beneficiarios</div>
            <img src="data:image/png;base64,' . $imgBenef . '" style="width:90px;height:90px;">
            ' . $htmlLeyenda($datosBenef) . '
        </td>
        <td class="chart-cell" style="padding:0 3px;">
            <div class="chart-title">N&uacute;cleos / Sedes</div>
            <img src="data:image/png;base64,' . $imgNucleo . '" style="width:90px;height:90px;">
            ' . $htmlLeyenda($datosNucleo) . '
        </td>
        <td class="chart-cell" style="padding-left:5px;">
            <div class="chart-title">Programas (PNF)</div>
            <img src="data:image/png;base64,' . $imgPnf . '" style="width:90px;height:90px;">
            ' . $htmlLeyenda($datosPnf) . '
        </td>
    </tr>
</table>

<table class="cols-t">
    <tr>
        <td class="col-l">
            <div class="sec-title">Diagn&oacute;sticos CIE-10 m&aacute;s Comunes</div>
            <table class="dt">
                <thead><tr><th style="width:40px;">C&oacute;digo</th><th>Patolog&iacute;a</th><th style="width:35px;">Casos</th></tr></thead>
                <tbody>';
            if (empty($topPatologias)) {
                $html .= '<tr><td colspan="3" style="text-align:center;color:#94a3b8;padding:6px;">No hay diagn&oacute;sticos</td></tr>';
            } else {
                foreach ($topPatologias as $p) {
                    $html .= '<tr>';
                    $html .= '<td style="font-weight:bold;color:#1e3a8a;white-space:nowrap;">' . htmlspecialchars($p['codigo_icd'], ENT_QUOTES, 'UTF-8') . '</td>';
                    $html .= '<td>' . htmlspecialchars($p['patologia'], ENT_QUOTES, 'UTF-8') . '</td>';
                    $html .= '<td style="text-align:right;font-weight:bold;">' . $p['total'] . '</td>';
                    $html .= '</tr>';
                }
            }
            $html .= '</tbody></table>
        </td>
        <td class="col-r">
            <div class="sec-title">Condiciones M&eacute;dicas m&aacute;s Comunes</div>
            <table class="dt">
                <thead><tr><th>Condici&oacute;n</th><th style="width:45px;">Pacientes</th></tr></thead>
                <tbody>';
            if (empty($topCondiciones)) {
                $html .= '<tr><td colspan="2" style="text-align:center;color:#94a3b8;padding:6px;">No hay condiciones</td></tr>';
            } else {
                foreach ($topCondiciones as $c) {
                    $html .= '<tr>';
                    $html .= '<td style="font-weight:bold;">' . htmlspecialchars($c['nombre_condicion'], ENT_QUOTES, 'UTF-8') . '</td>';
                    $html .= '<td style="text-align:right;font-weight:bold;">' . $c['total'] . '</td>';
                    $html .= '</tr>';
                }
            }
            $html .= '</tbody></table>
        </td>
    </tr>
</table>

</body>
</html>';

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream("Reporte_Morbilidad_" . $fechaInicio . "_a_" . $fechaFin . ".pdf", ["Attachment" => false]);
            exit();

        } catch (\Exception $e) {
            http_response_code(400);
            echo "Error al generar el reporte: " . $e->getMessage();
            exit();
        }
    }
}
