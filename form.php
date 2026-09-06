<?php

$rutasApi = [
    "GET" => [
        "api/consulta"                      => "getConsultas",
        "api/consulta/{id}"                 => "getConsultas",
        "api/consultas"                     => "getConsultas",
        "api/consultas/{id}"                => "getConsultas",
        "api/consultas/detalle"             => "obtenerConsulta",
        "api/consultas/detalle/{id}"        => "obtenerConsulta",
        "api/consultas/reporte-morbilidad"  => "generarReporteMorbilidad",
        "api/consultas/patologias"          => "buscarPatologia",
        "api/consultas/pacientes"           => "buscarPaciente",
        "api/consultas/paciente/{cedula}"   => "obtenerConsultasPaciente",
        "api/users/buscar/{id}"             => "buscarUsuario",
        "api/users/cedula"                  => "obtenerUsuario",
        "api/users/{id}"                    => "obtenerUsuario",
        "api/condiciones/buscar"            => "buscarCondicion",
        "api/condiciones/paciente/{cedula}" => "obtenerCondicionesPaciente",
        "api/pnfs"                          => "buscarPnfs",
        "api/nucleos/pnfs/{id}"             => "obtenerPnfsPorNucleo"
    ],
    "POST" => [
        "api/auth/login"                    => "login",
        "api/users"                         => "registrarUsuario",
        "api/consulta"                      => "registroConsulta",
        "api/consultas"                     => "registroConsulta",
        "api/consultas/reporte-morbilidad"  => "generarReporteMorbilidad",
        "api/roles"                         => "registrarRol",
        "api/condiciones"                   => "registrarCondicion",
        "api/nucleos"                       => "registrarNucleo",
        "api/pnfs"                          => "registrarPnf",
        "api/ofertas"                       => "registrarOferta"
    ],
    "PUT" => [
        "api/users"                         => "actualizarUsuario",
        "api/consulta"                      => "actualizarConsulta",
        "api/consultas"                     => "actualizarConsulta",
        "api/roles"                         => "actualizarRol",
        "api/roles/permisos"                => "guardarRolesPermisos",
        "api/configuracion"                 => "guardarConfiguracion",
        "api/condiciones"                   => "actualizarCondicion",
        "api/nucleos"                       => "actualizarNucleo",
        "api/pnfs"                          => "actualizarPnf"
    ],
    "PATCH" => [
    ],
    "DELETE" => [
        "api/users"                         => "eliminarUsuario",
        "api/roles"                         => "eliminarRol",
        "api/condiciones"                   => "eliminarCondicion",
        "api/nucleos"                       => "eliminarNucleo",
        "api/pnfs"                          => "eliminarPnf",
        "api/ofertas"                       => "eliminarOferta"
    ]
];

return $rutasApi;