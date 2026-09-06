<?php


use app\controller\Controller;
use app\controller\ConsultaController;
use app\controller\CondicionController;
use app\controller\NucleoPNFController;
use app\controller\ViewController;
use app\controller\ApiController;
use app\model\Consulta;
use app\model\Usuario;
use app\config\Config;

$pdo = Config::conexion(); 
$controller = new Controller($pdo);
$controllerConsulta = new ConsultaController($pdo);
$controllerOferta = new NucleoPNFController($pdo);

$userModel = new Usuario($pdo);
$userModel->sincronizarPermisos([
    "gestionar_usuarios" => "Permite registrar, actualizar y eliminar usuarios",
    "ver_consultas" => "Permite ver y buscar el historial de consultas médicas",
    "realizar_consulta" => "Permite registrar una nueva consulta médica",
    "modificar_consulta" => "Permite registrar y actualizar consultas médicas",
    "generar_reportes" => "Permite generar reportes de morbilidad médica",
    "gestionar_roles_permisos" => "Permite administrar roles, permisos y configuración del sistema",
    "gestionar_condiciones" => "Permite añadir, modificar y borrar condiciones",
    "gestionar_oferta_academica" => "Permite administrar los núcleos, PNFs y la oferta académica global"
]);

try {
    $pIdsToRemove = [];
    $stmtP = $pdo->prepare("SELECT id_permiso FROM lista_permisos WHERE nombre_permiso IN ('ver_consultas', 'realizar_consulta', 'modificar_consulta')");
    $stmtP->execute();
    $pIdsToRemove = $stmtP->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($pIdsToRemove)) {
        $inClause = implode(',', array_map('intval', $pIdsToRemove));
        $stmtDel = $pdo->prepare("DELETE FROM roles_permisos WHERE id_rol = 4 AND id_permiso IN ($inClause)");
        $stmtDel->execute();
    }

    $insertMap = [
        2 => ['gestionar_usuarios', 'ver_consultas', 'realizar_consulta', 'modificar_consulta', 'gestionar_condiciones'],
        3 => ['gestionar_usuarios', 'ver_consultas', 'realizar_consulta', 'modificar_consulta', 'generar_reportes', 'gestionar_condiciones'],
        4 => ['gestionar_usuarios', 'generar_reportes', 'gestionar_roles_permisos', 'gestionar_condiciones', 'gestionar_oferta_academica']
    ];

    foreach ($insertMap as $roleId => $permNames) {
        foreach ($permNames as $pName) {
            $stmtId = $pdo->prepare("SELECT id_permiso FROM lista_permisos WHERE nombre_permiso = :name");
            $stmtId->execute([':name' => $pName]);
            $idPerm = $stmtId->fetchColumn();
            if ($idPerm) {
                $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM roles_permisos WHERE id_rol = :role_id AND id_permiso = :id_perm");
                $stmtCheck->execute([':role_id' => $roleId, ':id_perm' => $idPerm]);
                $stmtCheck->fetchColumn();
                if ($stmtCheck->fetchColumn() == 0) {
                    $stmtIns = $pdo->prepare("INSERT INTO roles_permisos (id_rol, id_permiso) VALUES (:role_id, :id_perm)");
                    $stmtIns->execute([':role_id' => $roleId, ':id_perm' => $idPerm]);
                }
            }
        }
    }
} catch (Exception $e) {
}

function checkPerm(string $permiso, \app\model\Usuario $userModel): bool {
    if (!isset($_SESSION['cedula'])) {
        return false;
    }
    return $userModel->tienePermiso($_SESSION['cedula'], $permiso);
}

$tieneGestionarUsuarios = false;
$tieneVerConsultas = false;
$tieneRealizarConsulta = false;
$tieneModificarConsulta = false;
$tieneGenerarReportes = false;
$tieneGestionarRolesPermisos = false;
$tieneGestionarCondiciones = false;
$tieneGestionarOferta = false; 

$defaultRol = $userModel->obtenerRolDefecto();
$rolUsuario = $defaultRol;

if (isset($_SESSION['cedula'])) {
    $datosUsuarioLogueado = $userModel->login($_SESSION['cedula']);
    $rolUsuario = isset($datosUsuarioLogueado['data']['rol']) ? (int)$datosUsuarioLogueado['data']['rol'] : (isset($datosUsuarioLogueado['rol']) ? (int)$datosUsuarioLogueado['rol'] : $defaultRol);

    $tieneGestionarUsuarios = checkPerm("gestionar_usuarios", $userModel);
    $tieneVerConsultas = checkPerm("ver_consultas", $userModel);
    $tieneRealizarConsulta = checkPerm("realizar_consulta", $userModel);
    $tieneModificarConsulta = checkPerm("modificar_consulta", $userModel);
    $tieneGenerarReportes = checkPerm("generar_reportes", $userModel);
    $tieneGestionarRolesPermisos = checkPerm("gestionar_roles_permisos", $userModel);
    $tieneGestionarCondiciones = checkPerm("gestionar_condiciones", $userModel);
    $tieneGestionarOferta = checkPerm("gestionar_oferta_academica", $userModel);
}

if (!isset($_SESSION['cedula'])) {
    if (in_array($paginaActual, ["perfil", "usuarios", "consultas", "configuracion", "sesion", "condiciones", "oferta", "sedes", "sedes-carreras"])) {
        header("Location: login");
        exit();
    }
} else {
    if ($paginaActual === "usuarios" && !$tieneGestionarUsuarios) {
        header("Location: perfil");
        exit();
    }
    if ($paginaActual === "consultas" && !$tieneVerConsultas) {
        header("Location: perfil");
        exit();
    }
    if ($paginaActual === "configuracion" && !$tieneGestionarRolesPermisos && !$tieneGestionarCondiciones) {
        header("Location: perfil");
        exit();
    }
    if (($paginaActual === "oferta" || $paginaActual === "sedes" || $paginaActual === "sedes-carreras") && !$tieneGestionarOferta) { 
        header("Location: perfil");
        exit();
    }
}



if (isset($_SESSION['cedula'])) {
    $currentUa = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (!isset($_SESSION['user_agent']) || $_SESSION['user_agent'] !== $currentUa) {
        session_unset();
        session_destroy();
        header("Location: login");
        exit();
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        exit("CSRF token validation failed");
    }
}


if ($paginaActual === "buscar_patologia") {
    if (!checkPerm("ver_consultas", $userModel)) {
        header('Content-Type: application/json');
        echo json_encode([]);
        exit();
    }
    $controllerConsulta->buscarPatologiaAjax();
    exit();
}

if ($paginaActual === "buscar_paciente") {
    if (!checkPerm("ver_consultas", $userModel)) {
        header('Content-Type: application/json');
        echo json_encode([]);
        exit();
    }
    $controllerConsulta->buscarPacienteAjax();
    exit();
}

if ($paginaActual === "buscar_consultas_paciente") {
    if (!checkPerm("ver_consultas", $userModel)) {
        header('Content-Type: application/json');
        echo json_encode([]);
        exit();
    }
    $controllerConsulta->obtenerConsultasPacienteAjax();
    exit();
}

if ($paginaActual === "buscar_condicion") {
    if (!checkPerm("ver_consultas", $userModel)) {
        header('Content-Type: application/json');
        echo json_encode([]);
        exit();
    }
    $controllerConsulta->buscarCondicionAjax();
    exit();
}

if ($paginaActual === "buscar_condiciones_paciente") {
    if (!checkPerm("ver_consultas", $userModel)) {
        header('Content-Type: application/json');
        echo json_encode([]);
        exit();
    }
    $controllerConsulta->obtenerCondicionesPacienteAjax();
    exit();
}

$todosLosUsuarios = [];
if (isset($_SESSION['cedula']) && $tieneGestionarUsuarios) {
    $modeloConsulta = new Consulta($pdo);
    $todosLosUsuarios = $modeloConsulta->obtenerTodosLosUsuarios();
}