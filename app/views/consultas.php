<?php

if (!isset($_SESSION["cedula"])) {
    header("Location: login");
    exit;
}

$titulo = "Consultas";
include_once __DIR__."/layout/header.php";
?>

<main class="perfil">    
    <?php include_once __DIR__."/layout/sidebar.php"; ?>

    <section class="section-1 section-1--perfil">
        <!-- Buscador y botones de acción -->
        <div class="buscador-caja">
            
                <input type="text" id="inputBuscarConsulta" placeholder="Buscar consulta por paciente, médico, cédula o motivo" class="action-card__input input-buscar-consulta" autocomplete="off">
            
            
            <div class="section-1__box transition" id="section-1-box">
                
                    <div class="box-iniciar-consulta" style="display: flex; gap: 10px;">
                        <?php if (!empty($tieneRealizarConsulta) || !empty($GLOBALS['tieneRealizarConsulta'])): ?>
                            <a name="openModal" data-modal="modalRegistrarConsulta" class="action-card__button action-card__button--grid-principal btn-iniciar-consulta" href="#">Iniciar consulta</a>
                        <?php endif; ?>
                        
                        <?php if (!empty($tieneGenerarReportes) || !empty($GLOBALS['tieneGenerarReportes'])): ?>
                            <a name="openModal" data-modal="modalReporteMorbilidad" class="action-card__button btn-generar-reporte" href="#" style="background-color: #0284c7; width: fit-content; text-align: center;">Generar Reporte</a>
                        <?php endif; ?>
                    </div>
                
            </div>
        </div>

            <div class="contenedor-tabla-consultas">
                <h3 class="titulo-tabla-consultas">Consultas Recientes</h3>
                <table id="tablaRegistrosConsultas" class="tabla-consultas">
                    <thead>
                        <tr class="tr-head-consultas">
                            <th class="th-consultas">Fecha</th>
                            <th class="th-consultas">Paciente</th>
                            <th class="th-consultas">Médico</th>
                            <th class="th-consultas">Motivo</th>
                            <th class="th-consultas">Síntomas</th>
                            <th class="th-consultas">Diagnóstico (CIE-10)</th>
                            <th class="th-consultas">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoTablaConsultas">
                        <?php if (!empty($consultasRecientes)): ?>
                            <?php foreach ($consultasRecientes as $c): ?>
                                <tr class="tr-body-consultas">
                                    <td class="td-consultas-nowrap"><?= e(date('d/m/Y H:i', strtotime($c['fecha_consulta']))) ?></td>
                                    <td class="td-consultas">
                                        <strong><?= e(($c['paciente_nombre'] ?? '') . ' ' . ($c['paciente_apellido'] ?? '')) ?></strong>
                                        <div class="td-paciente-sub">C.I. <?= e($c['id_usuario']) ?></div>
                                    </td>
                                    <td class="td-consultas"><?= e(($c['medico_nombre'] ?? '') . ' ' . ($c['medico_apellido'] ?? '')) ?></td>
                                    <td class="td-consultas"><?= e($c['motivo_de_visita']) ?></td>
                                    <td class="td-consultas">
                                        <?= !empty($c['sintomas']) ? e(implode(', ', $c['sintomas'])) : '<span class="sintomas-ninguno">Ninguno</span>' ?>
                                    </td>
                                    <td class="td-consultas">
                                        <?php if (!empty($c['diagnosticos'])): ?>
                                            <?php foreach ($c['diagnosticos'] as $diag): ?>
                                                <div class="diagnostico-item-tabla">
                                                    <strong class="diagnostico-codigo"><?= e($diag['codigo_icd_diagnostico']) ?></strong> - <?= e($diag['patologia'] ?? 'Sin detalle') ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="sintomas-ninguno">Sin diagnóstico</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="td-acciones-btn">
                                        <button class="ver-detalles-consulta action-card__button btn-detalles-consulta" data-id="<?= e($c['id']) ?>">Ver detalles</button>
                                        
                                            <button class="editar-consulta action-card__button" data-id="<?= e($c['id']) ?>">Actualizar</button>
                                        
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="td-tabla-vacia">No hay ninguna consulta asociada a ese usuario.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="contenedor-btn-cargar">
                    <button id="btnCargarMasConsultas" class="action-card__button btn-cargar-mas" style="display: none;">Cargar más</button>
                </div>
            </div>
    </section>

    <!-- Implementación modular sugerida para las etiquetas <dialog> generadas -->
    
        <?php include_once __DIR__."/modals/modalRegistrarConsulta.php"; ?>
        <?php include_once __DIR__."/modals/modalActualizarConsulta.php"; ?>
        <?php include_once __DIR__."/modals/modalVerDetallesConsulta.php"; ?>
        <?php include_once __DIR__."/modals/modalBuscarConsulta.php"; ?>
        
        <?php if (!empty($tieneGenerarReportes) || !empty($GLOBALS['tieneGenerarReportes'])): ?>
        <?php include_once __DIR__."/modals/modalReporteMorbilidad.php"; ?>
        <?php endif; ?>

    <script>
        const ES_MEDICO_O_DIRECTOR = <?= isset($tieneModificarConsulta) && $tieneModificarConsulta ? 'true' : 'false' ?>;
    </script>
</main>

<footer>
    <script src="assets/script/append.js" defer></script>
    <script src="assets/script/gestion.js" defer></script>
</footer>