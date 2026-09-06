<?php 
$paginaActual = $paginaActual ?? ($GLOBALS['paginaActual'] ?? ''); 
global $tieneGestionarUsuarios, $tieneVerConsultas, $tieneGestionarRolesPermisos, $tieneGestionarCondiciones, $tieneGestionarOferta;
$tGestionarUsuarios = $tieneGestionarUsuarios ?? ($GLOBALS['tieneGestionarUsuarios'] ?? false);
$tVerConsultas = $tieneVerConsultas ?? ($GLOBALS['tieneVerConsultas'] ?? false);
$tConfiguracion = ($tieneGestionarRolesPermisos ?? ($GLOBALS['tieneGestionarRolesPermisos'] ?? false)) || ($tieneGestionarCondiciones ?? ($GLOBALS['tieneGestionarCondiciones'] ?? false));
$tOferta = $tieneGestionarOferta ?? ($GLOBALS['tieneGestionarOferta'] ?? false);
?>
<aside class="side-menu">
    <h1>Menu</h1>
    <hr>
    <a href="perfil" id="inicio" class="<?= $paginaActual === 'perfil' ? 'focus' : '' ?>">Inicio</a>
    <?php if ($tGestionarUsuarios): ?>
    <a href="usuarios" id="usuario" class="<?= $paginaActual === 'usuarios' ? 'focus' : '' ?>">Usuarios</a>
    <?php endif; ?>
    <?php if ($tVerConsultas): ?>
    <a href="consultas" id="consulta" class="<?= $paginaActual === 'consultas' ? 'focus' : '' ?>">Consultas</a>
    <?php endif; ?>
    <?php if ($tConfiguracion): ?>
    <a href="configuracion" id="configuracion" class="<?= $paginaActual === 'configuracion' ? 'focus' : '' ?>">Configuración</a>
    <?php endif; ?>
    <?php if ($tOferta): ?>
    <a href="sedes" id="sedes-carreras" class="<?= $paginaActual === 'sedes-carreras' ? 'focus' : '' ?>">Nucleos y PNFS</a>
    <a href="oferta" id="oferta" class="<?= $paginaActual === 'oferta' ? 'focus' : '' ?>">Ofertas Academicas</a>
    <?php endif; ?>
</aside>