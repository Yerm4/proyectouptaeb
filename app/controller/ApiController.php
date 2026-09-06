<?php

namespace app\controller;

use app\model\NucleoPNF;
use app\model\Usuario;
use app\model\Consulta;
use app\model\Condicion;

class ApiController {
    private $pdo;

    public function __construct($conexion) {
        $this->pdo = $conexion;
    }

    private function jsonResponse($status, $message = "", $data = null, $redirect = null) {
        header("Content-Type: application/json");
        echo json_encode([
            "status" => $status,
            "message" => $message,
            "data" => $data,
            "redirect" => $redirect
        ]); 
        exit();
    }

    private function checkPerm(string $permiso): bool {
        if (empty($_SESSION['cedula'])) {
            return false;
        }
        $userModel = new Usuario($this->pdo);
        return $userModel->tienePermiso($_SESSION['cedula'], $permiso);
    }

    private function getRequestData(): array {
        $raw = file_get_contents("php://input");
        $jsonData = json_decode($raw, true);
        if (is_array($jsonData)) {
            return $jsonData;
        }
        if (!empty($_POST)) {
            return $_POST;
        }
        return [];
    }

    public function login() {
        $data = $this->getRequestData();

        $cedula = cleanValue($data, "cedula");
        $password = cleanValue($data, "password");

        if (empty($cedula) || empty($password)) {
            code(400);
            $this->jsonResponse("error", "Parece que intentaste enviar un campo vacío");
        }

        if (strlen($password) > 20) {
            code(400);
            $this->jsonResponse("error", "Contraseña invalida");
        }

        $lenCedula = strlen((string)$cedula);
        if ($lenCedula < 7 || $lenCedula > 8) {
            code(400);
            $this->jsonResponse("error", "Cédula inválida");
        }

        $model = new Usuario($this->pdo);
        $result = $model->login($cedula);
        $data = $result["data"] ?? null;
        if ($result["status"] !== "ok" || !$data) {
            code(401);
            $this->jsonResponse("error", $result["message"] ?? "Usuario o contraseña incorrectos");
        }

        $passwordCoincide = password_verify($password, $data["contrasena"]) || ($data["contrasena"] === $password);
        if ($passwordCoincide) {
            session_regenerate_id(true);
            $_SESSION["cedula"] = $data["cedula"];
            $_SESSION["user_agent"] = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $this->jsonResponse("ok", "Logueado", null, "perfil");
        } else {
            code(401);
            $this->jsonResponse("error", "Usuario o contraseña incorrectos");
        }
    }

    public function registrarUsuario() {
        if (isset($_SESSION['cedula'])) {
            if (!$this->checkPerm("gestionar_usuarios")) {
                code(403);
                $this->jsonResponse("error", "No tiene permisos para registrar usuarios");
            }
        }

        $data = $this->getRequestData();

        $cedula                     = cleanValue($data, "cedula");
        $nombre                     = cleanValue($data, "nombre");
        $apellido                   = cleanValue($data, "apellido");
        $tipo                       = cleanValue($data, "tipo");
        $fecha_nacimiento           = cleanValue($data, "fecha_nacimiento");
        $tlfprincipal               = cleanValue($data, "tlfprincipal");
        $nombre_contacto_emergencia = cleanValue($data, "nombre_contacto_emergencia");
        $tlfemergencia              = cleanValue($data, "tlfemergencia");
        $sexo                       = cleanValue($data, "sexo");
        $direccion                  = cleanValue($data, "direccion") ?? '';

        $nucleo_id = isset($data['nucleo_id']) && $data['nucleo_id'] !== '' ? (int)$data['nucleo_id'] : null;
        $pnf_id    = isset($data['pnf_id']) && $data['pnf_id'] !== '' ? (int)$data['pnf_id'] : null;

        $rol = null;
        if (isset($data['rol']) && !empty($_SESSION['cedula'])) {
            $userModel = new Usuario($this->pdo);
            if ($userModel->tienePermiso($_SESSION['cedula'], 'gestionar_roles_permisos')) {
                $rol = (int)$data['rol'];
            }
        }

        if (empty($cedula) || empty($nombre) || empty($apellido) || empty($tipo) || 
            empty($fecha_nacimiento) || empty($tlfprincipal) || empty($tlfemergencia) || 
            empty($nombre_contacto_emergencia) || empty($sexo) || empty($direccion)) {
            code(400);
            $this->jsonResponse("error", "Por favor completa todos los campos requeridos");
        }

        if (!ctype_digit((string)$cedula)) {
            code(400);
            $this->jsonResponse("error", "La cédula solo debe contener números");
        }

        $lenCedula = strlen((string)$cedula);
        if ($lenCedula < 6 || $lenCedula > 8) {
            code(400);
            $this->jsonResponse("error", "La cédula debe contener entre 6 y 8 dígitos");
        }

        if (strlen($nombre) > 30 || strlen($apellido) > 30) {
            code(400);
            $this->jsonResponse("error", "El nombre y el apellido no pueden exceder los 30 caracteres");
        }

        $modeloPaciente = new Usuario($this->pdo);

        $resultado = $modeloPaciente->registrarUsuario(
            $cedula, $nombre, $apellido, $tipo, $fecha_nacimiento, 
            $tlfprincipal, $tlfemergencia, $nombre_contacto_emergencia, $sexo, 
            $direccion, $rol, $nucleo_id, $pnf_id
        );

        if ($resultado["status"] === "ok") {
            code(201);
            $this->jsonResponse("ok", $resultado["message"] ?? $resultado["msg"] ?? "Usuario registrado con éxito");
        } else {
            code(400);
            $this->jsonResponse("error", $resultado["message"] ?? $resultado["msg"] ?? "Error al registrar el usuario");
        }
    }

    public function actualizarUsuario() {
        if (empty($_SESSION['cedula'])) {
            code(401);
            $this->jsonResponse("error", "Debe iniciar sesión para realizar esta acción");
        }

        $data = $this->getRequestData();

        $cedula                     = cleanValue($data, "cedula");
        $nombre                     = cleanValue($data, "nombre");
        $apellido                   = cleanValue($data, "apellido");
        $tipo                       = cleanValue($data, "tipo");
        $fecha_nacimiento           = cleanValue($data, "fecha_nacimiento");
        $tlfprincipal               = cleanValue($data, "tlfprincipal");
        $nombre_contacto_emergencia = cleanValue($data, "nombre_contacto_emergencia");
        $tlfemergencia              = cleanValue($data, "tlfemergencia");
        $sexo                       = cleanValue($data, "sexo");
        $direccion                  = cleanValue($data, "direccion") ?? '';

        $userModel = new Usuario($this->pdo);
        if ((string)$_SESSION['cedula'] !== (string)$cedula && !$userModel->tienePermiso($_SESSION['cedula'], 'gestionar_usuarios')) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos para actualizar este usuario");
        }

        $nucleo_id = isset($data['nucleo_id']) && $data['nucleo_id'] !== '' ? (int)$data['nucleo_id'] : null;
        $pnf_id    = isset($data['pnf_id']) && $data['pnf_id'] !== '' ? (int)$data['pnf_id'] : null;

        $rol = null;
        if (isset($data['rol']) && !empty($_SESSION['cedula'])) {
            if ($userModel->tienePermiso($_SESSION['cedula'], 'gestionar_roles_permisos')) {
                $rol = (int)$data['rol'];
            }
        }

        if (empty($cedula)) {
            code(400);
            $this->jsonResponse("error", "La cédula es requerida para actualizar el usuario");
        }

        if (!ctype_digit((string)$cedula)) {
            code(400);
            $this->jsonResponse("error", "La cédula solo debe contener números");
        }

        if (strlen($nombre) > 30 || strlen($apellido) > 30) {
            code(400);
            $this->jsonResponse("error", "El nombre y el apellido no pueden exceder los 30 caracteres");
        }

        $guardado = $userModel->actualizarUsuarioCompleto(
            $cedula, $nombre, $apellido, $tipo, $fecha_nacimiento, 
            $tlfprincipal, $nombre_contacto_emergencia, $tlfemergencia, 
            $sexo, $direccion, $rol, $nucleo_id, $pnf_id
        );

        if (is_array($guardado) && ($guardado["status"] === "ok" || $guardado["status"] === "success")) {
            code(200);
            $this->jsonResponse("ok", "Usuario actualizado con éxito");
        } elseif ($guardado === true) {
            code(200);
            $this->jsonResponse("ok", "Usuario actualizado con éxito");
        } else {
            code(400);
            $this->jsonResponse("error", "No se realizaron cambios o hubo un fallo al actualizar");
        }
    }

    public function buscarUsuario($id = null) {
        if (empty($_SESSION['cedula'])) {
            code(401);
            $this->jsonResponse("error", "Debe iniciar sesión");
        }

        if (!$this->checkPerm("gestionar_usuarios") && !$this->checkPerm("ver_consultas")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos para buscar usuarios");
        }

        $model = new Usuario($this->pdo);

        if (empty($id) || $id === "0") {
            $resultados = $model->consultarUsuarios();
        } else {
            $resultados = $model->buscarUsuarios($id);
        }

        $datos = isset($resultados["data"]) ? $resultados["data"] : $resultados;
        code(200);
        $this->jsonResponse("ok", "Resultados enviados", $datos);
    }

    public function obtenerUsuario($id = null) {
        if (empty($_SESSION['cedula'])) {
            code(401);
            $this->jsonResponse("error", "Debe iniciar sesión");
        }

        $cedula = $id ?? (isset($_GET['cedula']) ? trim($_GET['cedula']) : (isset($_GET['id']) ? trim($_GET['id']) : ''));

        if ((string)$_SESSION['cedula'] !== (string)$cedula && !$this->checkPerm("gestionar_usuarios")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos para consultar este usuario");
        }

        if (empty($cedula)) {
            code(400);
            $this->jsonResponse("error", "Cédula no proporcionada");
        }

        $model = new Usuario($this->pdo);
        $usuario = $model->login($cedula);

        if ($usuario && isset($usuario["status"]) && $usuario["status"] === "ok") {
            code(200);
            $this->jsonResponse("ok", "Usuario obtenido con éxito", $usuario["data"]);
        } else {
            code(404);
            $this->jsonResponse("error", "No se encontró el registro");
        }
    }

    public function eliminarUsuario() {
        if (empty($_SESSION['cedula'])) {
            code(401);
            $this->jsonResponse("error", "Debe iniciar sesión");
        }

        if (!$this->checkPerm("gestionar_usuarios")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos para eliminar usuarios");
        }

        $data = $this->getRequestData();
        $id = cleanValue($data, "cedula");

        if (empty($id)) {
            code(400);
            $this->jsonResponse("error", "Cédula requerida para eliminar");
        }

        $userModel = new Usuario($this->pdo);
        $result = $userModel->eliminarUsuario($id);

        if (is_array($result) && ($result["status"] === "ok" || $result["status"] === "success")) {
            code(200);
            $this->jsonResponse("ok", "Usuario " . $id . " eliminado");
        } elseif ($result === true) {
            code(200);
            $this->jsonResponse("ok", "Usuario " . $id . " eliminado");
        } else {
            code(400);
            $this->jsonResponse("error", "No se pudo eliminar el usuario");
        }
    }

    public function getConsultas($id = null) {
        if (empty($_SESSION['cedula'])) {
            code(401);
            $this->jsonResponse("error", "Debe iniciar sesión");
        }

        if (!$this->checkPerm("ver_consultas")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos para ver consultas");
        }

        $modeloConsulta = new Consulta($this->pdo);
        $idConsulta = $id ?? (isset($_GET['id']) ? (int)$_GET['id'] : null);
        if ($idConsulta !== null && $idConsulta > 0) {
            $consulta = $modeloConsulta->obtenerConsultaPorId($idConsulta);
            if ($consulta) {
                code(200);
                $this->jsonResponse("ok", "Consulta obtenida con éxito", $consulta);
            } else {
                code(404);
                $this->jsonResponse("error", "No se encontró la consulta médica");
            }
        }

        $query = isset($_GET['query']) ? trim($_GET['query']) : (isset($_GET['q']) ? trim($_GET['q']) : '');
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

        if (empty($query)) {
            $resultados = $modeloConsulta->obtenerConsultasRecientes($limit > 0 ? $limit : 10);
        } else {
            $resultados = $modeloConsulta->buscarConsultas($query, $limit > 0 ? $limit : 20);
        }

        code(200);
        $this->jsonResponse("ok", "Consultas obtenidas con éxito", $resultados ?: []);
    }

    public function buscarConsultas($id = null) {
        $this->getConsultas($id);
    }

    public function obtenerConsulta($id = null) {
        $this->getConsultas($id);
    }

    public function registroConsulta() {
        if (empty($_SESSION['cedula'])) {
            code(401);
            $this->jsonResponse("error", "Debe iniciar sesión");
        }

        if (!$this->checkPerm("realizar_consulta")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos para registrar consultas");
        }

        $data = $this->getRequestData();

        $cedulaPaciente = cleanValue($data, 'cedula_paciente') ?? cleanValue($data, 'cedula');
        $motivo         = cleanValue($data, 'motivo_de_visita') ?? cleanValue($data, 'motivo');
        $observaciones  = cleanValue($data, 'observaciones') ?? '';
        $medicamento    = cleanValue($data, 'medicamento_suministrado') ?? cleanValue($data, 'medicamento') ?? '';

        $sintomas = $data['sintomas'] ?? [];
        if (!is_array($sintomas)) {
            $sintomas = array_filter(array_map('trim', explode(',', (string)$sintomas)));
        }

        $diagnosticos = $data['diagnosticos'] ?? [];
        if (!is_array($diagnosticos)) {
            $diagnosticos = array_filter(array_map('trim', explode(',', (string)$diagnosticos)));
        }

        $condiciones = $data['condiciones'] ?? [];
        if (!is_array($condiciones)) {
            $condiciones = array_filter(array_map('intval', explode(',', (string)$condiciones)));
        }

        $cedulaMedico = $_SESSION['cedula'] ?? '';

        if (empty($cedulaPaciente) || empty($motivo) || empty($cedulaMedico)) {
            code(400);
            $this->jsonResponse("error", "La consulta no fue registrada, revisa que todos los campos requeridos estén llenos");
        }

        $modeloConsulta = new Consulta($this->pdo);
        $resultado = $modeloConsulta->registrarConsulta(
            $cedulaPaciente, $cedulaMedico, $motivo, $observaciones, $sintomas, $diagnosticos, $condiciones, $medicamento
        );

        if ($resultado === true) {
            code(201);
            $this->jsonResponse("ok", "La consulta fue registrada exitosamente");
        } else {
            code(400);
            $this->jsonResponse("error", "La consulta no pudo ser registrada");
        }
    }

    public function actualizarConsulta() {
        if (empty($_SESSION['cedula'])) {
            code(401);
            $this->jsonResponse("error", "Debe iniciar sesión");
        }

        if (!$this->checkPerm("modificar_consulta")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos para actualizar consultas");
        }

        $data = $this->getRequestData();

        $idConsulta    = isset($data['id_consulta']) ? (int)$data['id_consulta'] : (isset($data['id']) ? (int)$data['id'] : 0);
        $motivo        = cleanValue($data, 'motivo_de_visita') ?? cleanValue($data, 'motivo');
        $observaciones = cleanValue($data, 'observaciones') ?? '';
        $medicamento   = cleanValue($data, 'medicamento_suministrado') ?? cleanValue($data, 'medicamento') ?? '';

        $sintomas = $data['sintomas'] ?? [];
        if (!is_array($sintomas)) {
            $sintomas = array_filter(array_map('trim', explode(',', (string)$sintomas)));
        }

        $diagnosticos = $data['diagnosticos'] ?? [];
        if (!is_array($diagnosticos)) {
            $diagnosticos = array_filter(array_map('trim', explode(',', (string)$diagnosticos)));
        }

        $condiciones = $data['condiciones'] ?? [];
        if (!is_array($condiciones)) {
            $condiciones = array_filter(array_map('intval', explode(',', (string)$condiciones)));
        }

        if ($idConsulta <= 0 || empty($motivo)) {
            code(400);
            $this->jsonResponse("error", "ID de consulta y motivo son obligatorios");
        }

        $modeloConsulta = new Consulta($this->pdo);
        $resultado = $modeloConsulta->actualizarConsulta(
            $idConsulta, $motivo, $observaciones, $sintomas, $diagnosticos, $condiciones, $medicamento
        );

        if ($resultado === true) {
            code(200);
            $this->jsonResponse("ok", "La consulta fue actualizada exitosamente");
        } else {
            code(400);
            $this->jsonResponse("error", "No se pudo actualizar la consulta");
        }
    }

    public function generarReporteMorbilidad() {
        if (empty($_SESSION['cedula'])) {
            code(401);
            $this->jsonResponse("error", "Debe iniciar sesión");
        }

        if (!$this->checkPerm("generar_reportes")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos para generar reportes");
        }

        $data = $this->getRequestData();
        $fechaInicio = cleanValue($data, 'fecha_inicio') ?? ($_GET['fecha_inicio'] ?? '');
        $fechaFin    = cleanValue($data, 'fecha_fin') ?? ($_GET['fecha_fin'] ?? '');

        if (empty($fechaInicio) || empty($fechaFin)) {
            code(400);
            $this->jsonResponse("error", "Debe especificar la fecha de inicio y la fecha de fin.");
        }

        if (strtotime($fechaInicio) > strtotime($fechaFin)) {
            code(400);
            $this->jsonResponse("error", "La fecha de inicio no puede ser posterior a la fecha de fin.");
        }

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (str_contains($accept, 'application/json') || isset($_GET['json'])) {
            $modeloConsulta = new Consulta($this->pdo);
            $datos = $modeloConsulta->obtenerDatosReporteMorbilidad($fechaInicio, $fechaFin);
            code(200);
            $this->jsonResponse("ok", "Datos de reporte de morbilidad", $datos);
        }

        $_POST['fecha_inicio'] = $fechaInicio;
        $_POST['fecha_fin'] = $fechaFin;
        $controllerConsulta = new \app\controller\ConsultaController($this->pdo);
        $controllerConsulta->generarReporteMorbilidad();
        exit();
    }

    public function buscarPatologia() {
        if (!$this->checkPerm("ver_consultas")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos");
        }

        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        if (strlen($q) < 2) {
            $this->jsonResponse("ok", "Resultados", []);
        }

        $modeloConsulta = new Consulta($this->pdo);
        $resultados = $modeloConsulta->buscarPatologia($q);
        $this->jsonResponse("ok", "Resultados", $resultados ?: []);
    }

    public function buscarPaciente() {
        if (!$this->checkPerm("ver_consultas")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos");
        }

        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        if (strlen($q) < 2) {
            $this->jsonResponse("ok", "Resultados", []);
        }

        $modeloConsulta = new Consulta($this->pdo);
        $resultados = $modeloConsulta->buscarUsuario($q);
        $this->jsonResponse("ok", "Resultados", $resultados ?: []);
    }

    public function obtenerConsultasPaciente($cedula = null) {
        if (!$this->checkPerm("ver_consultas")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos");
        }

        $ced = $cedula ?? (isset($_GET['cedula']) ? trim($_GET['cedula']) : '');
        if (empty($ced)) {
            $this->jsonResponse("ok", "Resultados", []);
        }

        $modeloConsulta = new Consulta($this->pdo);
        $resultados = $modeloConsulta->obtenerConsultasPorPaciente($ced);
        $this->jsonResponse("ok", "Resultados", $resultados ?: []);
    }

    public function buscarCondicion() {
        if (!$this->checkPerm("ver_consultas")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos");
        }

        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        if (strlen($q) < 2) {
            $this->jsonResponse("ok", "Resultados", []);
        }

        $modeloConsulta = new Consulta($this->pdo);
        $resultados = $modeloConsulta->buscarCondicion($q);
        $this->jsonResponse("ok", "Resultados", $resultados ?: []);
    }

    public function obtenerCondicionesPaciente($cedula = null) {
        if (!$this->checkPerm("ver_consultas")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos");
        }

        $ced = $cedula ?? (isset($_GET['cedula']) ? trim($_GET['cedula']) : '');
        if (empty($ced)) {
            $this->jsonResponse("ok", "Resultados", []);
        }

        $modeloConsulta = new Consulta($this->pdo);
        $resultados = $modeloConsulta->obtenerCondicionesPaciente($ced);
        $this->jsonResponse("ok", "Resultados", $resultados ?: []);
    }

    public function registrarRol() {
        if (!$this->checkPerm("gestionar_roles_permisos")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos para administrar roles y permisos");
        }

        $data = $this->getRequestData();
        $nombreRol = cleanValue($data, 'nombre_rol');
        $descripcionRol = cleanValue($data, 'descripcion_rol') ?? '';

        if (empty($nombreRol)) {
            code(400);
            $this->jsonResponse("error", "El nombre del rol es obligatorio");
        }

        $userModel = new Usuario($this->pdo);
        $resultado = $userModel->crearRol($nombreRol, $descripcionRol);

        if ($resultado) {
            code(201);
            $this->jsonResponse("ok", "¡Rol creado exitosamente!");
        } else {
            code(400);
            $this->jsonResponse("error", "Hubo un error al crear el rol");
        }
    }

    public function actualizarRol() {
        if (!$this->checkPerm("gestionar_roles_permisos")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos para administrar roles y permisos");
        }

        $data = $this->getRequestData();
        $idRol = isset($data['id_rol']) ? (int)$data['id_rol'] : (isset($data['id']) ? (int)$data['id'] : 0);
        $nombreRol = cleanValue($data, 'nombre_rol');
        $descripcionRol = cleanValue($data, 'descripcion_rol') ?? '';

        if ($idRol <= 0 || empty($nombreRol)) {
            code(400);
            $this->jsonResponse("error", "Datos del rol inválidos");
        }

        $userModel = new Usuario($this->pdo);
        $resultado = $userModel->actualizarRol($idRol, $nombreRol, $descripcionRol);

        if ($resultado) {
            code(200);
            $this->jsonResponse("ok", "¡Rol actualizado con éxito!");
        } else {
            code(400);
            $this->jsonResponse("error", "No se pudo actualizar el rol");
        }
    }

    public function eliminarRol() {
        if (!$this->checkPerm("gestionar_roles_permisos")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos para administrar roles y permisos");
        }

        $data = $this->getRequestData();
        $idRol = isset($data['id_rol']) ? (int)$data['id_rol'] : (isset($data['id']) ? (int)$data['id'] : 0);

        if ($idRol <= 0) {
            code(400);
            $this->jsonResponse("error", "No se puede eliminar este rol");
        }

        $userModel = new Usuario($this->pdo);
        $resultado = $userModel->eliminarRol($idRol);

        if ($resultado) {
            code(200);
            $this->jsonResponse("ok", "¡Rol eliminado con éxito!");
        } else {
            code(400);
            $this->jsonResponse("error", "Hubo un error al intentar eliminar el rol");
        }
    }

    public function guardarRolesPermisos() {
        if (!$this->checkPerm("gestionar_roles_permisos")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos para administrar roles y permisos");
        }

        $data = $this->getRequestData();
        $postedPermisos = $data['permisos'] ?? [];

        $userModel = new Usuario($this->pdo);
        $roles = $userModel->obtenerRoles();

        foreach ($roles as $role) {
            $idRol = $role['id_rol'];
            $permisosIds = isset($postedPermisos[$idRol]) ? $postedPermisos[$idRol] : [];
            $userModel->actualizarPermisosRol($idRol, $permisosIds);
        }

        code(200);
        $this->jsonResponse("ok", "¡Roles y permisos actualizados con éxito!");
    }

    public function guardarConfiguracion() {
        if (!$this->checkPerm("gestionar_roles_permisos")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos para administrar la configuración");
        }

        $data = $this->getRequestData();
        $rolDefecto = isset($data['rol_defecto']) ? (int)$data['rol_defecto'] : 0;

        if ($rolDefecto <= 0) {
            code(400);
            $this->jsonResponse("error", "Seleccione un rol válido");
        }

        $userModel = new Usuario($this->pdo);
        $resultado = $userModel->actualizarRolDefecto($rolDefecto);

        if ($resultado) {
            code(200);
            $this->jsonResponse("ok", "¡Configuración general guardada con éxito!");
        } else {
            code(400);
            $this->jsonResponse("error", "No se pudo guardar la configuración");
        }
    }

    public function registrarCondicion() {
        if (!$this->checkPerm("gestionar_condiciones")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos para gestionar condiciones");
        }

        $data = $this->getRequestData();
        $nombre = cleanValue($data, 'nombre_condicion');
        $descripcion = cleanValue($data, 'descripcion_condicion') ?? '';

        if (empty($nombre)) {
            code(400);
            $this->jsonResponse("error", "El nombre de la condición es obligatorio");
        }

        $model = new Condicion($this->pdo);
        $registrado = $model->registrarCondicion($nombre, $descripcion);

        if ($registrado) {
            code(201);
            $this->jsonResponse("ok", "¡Condición registrada con éxito!");
        } else {
            code(400);
            $this->jsonResponse("error", "Hubo un error al registrar la condición");
        }
    }

    public function actualizarCondicion() {
        if (!$this->checkPerm("gestionar_condiciones")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos para gestionar condiciones");
        }

        $data = $this->getRequestData();
        $id = isset($data['id']) ? (int)$data['id'] : (isset($data['id_condicion']) ? (int)$data['id_condicion'] : 0);
        $nombre = cleanValue($data, 'nombre_condicion');
        $descripcion = cleanValue($data, 'descripcion_condicion') ?? '';

        if ($id <= 0 || empty($nombre)) {
            code(400);
            $this->jsonResponse("error", "Datos de condición inválidos");
        }

        $model = new Condicion($this->pdo);
        $actualizado = $model->actualizarCondicion($id, $nombre, $descripcion);

        if ($actualizado) {
            code(200);
            $this->jsonResponse("ok", "¡Condición actualizada con éxito!");
        } else {
            code(400);
            $this->jsonResponse("error", "No se pudo actualizar la condición");
        }
    }

    public function eliminarCondicion() {
        if (!$this->checkPerm("gestionar_condiciones")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos para gestionar condiciones");
        }

        $data = $this->getRequestData();
        $id = isset($data['id']) ? (int)$data['id'] : (isset($data['id_condicion']) ? (int)$data['id_condicion'] : 0);

        if ($id <= 0) {
            code(400);
            $this->jsonResponse("error", "ID de condición no válido");
        }

        $model = new Condicion($this->pdo);
        $eliminado = $model->eliminarCondicion($id);

        if ($eliminado) {
            code(200);
            $this->jsonResponse("ok", "¡Condición eliminada con éxito!");
        } else {
            code(400);
            $this->jsonResponse("error", "Error al intentar eliminar la condición");
        }
    }

    public function obtenerPnfsPorNucleo($id) {
        if ((int)$id <= 0) {
            code(400);
            $this->jsonResponse("error", "La id no es valida");
        }
        $pnfModel = new NucleoPNF($this->pdo);
        $pnfs = $pnfModel->obtenerPnfsPorNucleo((int)$id);
        code(200);
        $this->jsonResponse("ok", "Pnfs enviados", $pnfs ?: []);
    }

    public function buscarPnfs() {
        $model = new Usuario($this->pdo);
        $pnfs = $model->buscarPnfs();
        code(200);
        $this->jsonResponse("ok", "Pnfs obtenidos", $pnfs ?: []);
    }

    public function registrarNucleo() {
        if (!$this->checkPerm("gestionar_oferta_academica")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos para administrar la oferta académica");
        }

        $data = $this->getRequestData();
        $nombre = cleanValue($data, 'nombre_nucleo');

        if (empty($nombre)) {
            code(400);
            $this->jsonResponse("error", "El nombre del núcleo es obligatorio");
        }

        if (strlen($nombre) < 4 || strlen($nombre) > 50) {
            code(400);
            $this->jsonResponse("error", "El nombre del núcleo debe tener entre 4 y 50 caracteres");
        }

        if (!preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s\(\)]+$/u', $nombre)) {
            code(400);
            $this->jsonResponse("error", "El nombre del núcleo solo puede contener letras y espacios");
        }

        $model = new NucleoPNF($this->pdo);
        $resultado = $model->registrarNucleo($nombre);

        if ($resultado === "duplicado") {
            code(400);
            $this->jsonResponse("error", "El núcleo ya se encuentra registrado");
        } elseif ($resultado) {
            code(201);
            $this->jsonResponse("ok", "¡Núcleo registrado con éxito!");
        } else {
            code(400);
            $this->jsonResponse("error", "Ocurrió un error al intentar registrar el núcleo");
        }
    }

    public function actualizarNucleo() {
        if (!$this->checkPerm("gestionar_oferta_academica")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos para administrar la oferta académica");
        }

        $data = $this->getRequestData();
        $id = isset($data['id_nucleo']) ? (int)$data['id_nucleo'] : (isset($data['id']) ? (int)$data['id'] : 0);
        $nombre = cleanValue($data, 'nombre_nucleo');

        if ($id <= 0 || empty($nombre)) {
            code(400);
            $this->jsonResponse('error', 'El campo no puede estar vacio.');
        }

        if (strlen($nombre) < 4 || strlen($nombre) > 100) {
            code(400);
            $this->jsonResponse('error', 'El nombre del núcleo debe tener entre 4 y 100 caracteres.');
        }

        if (!preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s\(\)]+$/u', $nombre)) {
            code(400);
            $this->jsonResponse('error', 'El nombre del núcleo no puede contener números ni caracteres especiales.');
        }

        $NucleoModelo = new NucleoPNF($this->pdo);
        $resultado = $NucleoModelo->actualizarNucleo($id, $nombre);

        if ($resultado === "duplicado") {
            code(400);
            $this->jsonResponse('error', 'El nucleo ya se encuentra registrado.');
        } elseif ($resultado) {
            code(200);
            $this->jsonResponse('ok', '¡Núcleo actualizado con éxito!');
        } else {
            code(400);
            $this->jsonResponse('error', 'Ocurrió un error al intentar actualizar el núcleo.');
        }
    }

    public function eliminarNucleo() {
        if (!$this->checkPerm("gestionar_oferta_academica")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos para administrar la oferta académica");
        }

        $data = $this->getRequestData();
        $id = isset($data['id_nucleo']) ? (int)$data['id_nucleo'] : (isset($data['id']) ? (int)$data['id'] : 0);

        if ($id <= 0) {
            code(400);
            $this->jsonResponse("error", "ID de núcleo inválido");
        }

        $model = new NucleoPNF($this->pdo);
        if ($model->desactivarNucleo($id)) {
            code(200);
            $this->jsonResponse("ok", "¡Núcleo eliminado con éxito!");
        } else {
            code(400);
            $this->jsonResponse("error", "Ocurrió un error al intentar eliminar el núcleo");
        }
    }

    public function registrarPnf() {
        if (!$this->checkPerm("gestionar_oferta_academica")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos para administrar la oferta académica");
        }

        $data = $this->getRequestData();
        $nombre = cleanValue($data, 'nombre_pnf');

        if (empty($nombre)) {
            code(400);
            $this->jsonResponse("error", "El campo no puede estar vacio");
        }

        if (strlen($nombre) < 4 || strlen($nombre) > 100) {
            code(400);
            $this->jsonResponse("error", "El nombre del PNF debe tener entre 4 y 100 caracteres");
        }

        if (!preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s\(\)]+$/u', $nombre)) {
            code(400);
            $this->jsonResponse("error", "El nombre del PNF solo puede contener letras y espacios");
        }

        $model = new NucleoPNF($this->pdo);
        $resultado = $model->registrarPnf($nombre);

        if ($resultado === "duplicado") {
            code(400);
            $this->jsonResponse("error", "El PNF ya se encuentra registrado");
        } elseif ($resultado) {
            code(201);
            $this->jsonResponse("ok", "¡PNF registrado con éxito!");
        } else {
            code(400);
            $this->jsonResponse("error", "Ocurrió un error al intentar registrar el PNF");
        }
    }

    public function actualizarPnf() {
        if (!$this->checkPerm("gestionar_oferta_academica")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos para administrar la oferta académica");
        }

        $data = $this->getRequestData();
        $id = isset($data['id_pnf']) ? (int)$data['id_pnf'] : (isset($data['id']) ? (int)$data['id'] : 0);
        $nombre = cleanValue($data, 'nombre_pnf');

        if ($id <= 0 || empty($nombre)) {
            code(400);
            $this->jsonResponse("error", "El campo no puede estar vacio");
        }

        if (strlen($nombre) < 4 || strlen($nombre) > 100) {
            code(400);
            $this->jsonResponse("error", "El nombre del PNF debe tener entre 4 y 100 caracteres");
        }

        if (!preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s\(\)]+$/u', $nombre)) {
            code(400);
            $this->jsonResponse("error", "El nombre del PNF no puede contener números ni caracteres especiales");
        }

        $model = new NucleoPNF($this->pdo);
        $resultado = $model->actualizarPnf($id, $nombre);

        if ($resultado === "duplicado") {
            code(400);
            $this->jsonResponse("error", "El PNF ya se encuentra registrado");
        } elseif ($resultado) {
            code(200);
            $this->jsonResponse("ok", "¡PNF actualizado con éxito!");
        } else {
            code(400);
            $this->jsonResponse("error", "Ocurrió un error al intentar actualizar el PNF");
        }
    }

    public function eliminarPnf() {
        if (!$this->checkPerm("gestionar_oferta_academica")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos para administrar la oferta académica");
        }

        $data = $this->getRequestData();
        $id = isset($data['id_pnf']) ? (int)$data['id_pnf'] : (isset($data['id']) ? (int)$data['id'] : 0);

        if ($id <= 0) {
            code(400);
            $this->jsonResponse("error", "ID de PNF inválido");
        }

        $model = new NucleoPNF($this->pdo);
        if ($model->desactivarPnf($id)) {
            code(200);
            $this->jsonResponse("ok", "¡PNF eliminado con éxito!");
        } else {
            code(400);
            $this->jsonResponse("error", "Ocurrió un error al intentar eliminar el PNF");
        }
    }

    public function registrarOferta() {
        if (!$this->checkPerm("gestionar_oferta_academica")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos para administrar la oferta académica");
        }

        $data = $this->getRequestData();
        $nucleoId = isset($data['nucleo_id']) ? (int)$data['nucleo_id'] : 0;
        $pnfId = isset($data['pnf_id']) ? (int)$data['pnf_id'] : 0;

        if ($nucleoId <= 0 || $pnfId <= 0) {
            code(400);
            $this->jsonResponse("error", "Debe seleccionar un núcleo y una carrera válidos");
        }

        $model = new NucleoPNF($this->pdo);
        $resultado = $model->registrarOferta($nucleoId, $pnfId);

        if ($resultado === "duplicado") {
            code(400);
            $this->jsonResponse("error", "Esta carrera ya está ofertada en la sede seleccionada");
        } elseif ($resultado) {
            code(201);
            $this->jsonResponse("ok", "¡Oferta académica asignada con éxito!");
        } else {
            code(400);
            $this->jsonResponse("error", "Ocurrió un error al asignar la oferta");
        }
    }

    public function eliminarOferta() {
        if (!$this->checkPerm("gestionar_oferta_academica")) {
            code(403);
            $this->jsonResponse("error", "No tiene permisos para administrar la oferta académica");
        }

        $data = $this->getRequestData();
        $idOferta = isset($data['id_oferta']) ? (int)$data['id_oferta'] : (isset($data['id']) ? (int)$data['id'] : 0);

        if ($idOferta <= 0) {
            code(400);
            $this->jsonResponse("error", "ID de oferta inválido");
        }

        $model = new NucleoPNF($this->pdo);
        if ($model->desactivarOferta($idOferta)) {
            code(200);
            $this->jsonResponse("ok", "¡Oferta académica eliminada con éxito!");
        } else {
            code(400);
            $this->jsonResponse("error", "Ocurrió un error al intentar eliminar la oferta");
        }
    }
}
