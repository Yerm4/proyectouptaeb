
<?php 
$titulo = "Perfil";
include __DIR__."/layout/header.php";
?>
<main class="perfil">    
    <?php include_once __DIR__."/layout/sidebar.php"; ?>
    
    <section class="section-1 section-1--perfil">
        <div class="buscador-caja">
            <div class="section-1__box transition" id="section-1-box"></div>
        </div>

        <?php if (!$tieneGestionarUsuarios && !$tieneVerConsultas && !$tieneGestionarRolesPermisos): ?>
            <?php if (!empty($misCondiciones)): ?>
                <div style="background: #fff; padding: 15px 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px;">
                    <h4 style="margin: 0 0 10px 0; color: #333;">Condiciones Médicas / Crónicas Registradas</h4>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <?php foreach ($misCondiciones as $cond): ?>
                            <span style="background-color: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 12px; font-size: 0.85em; font-weight: 600;">
                                <?= e($cond['nombre_condicion'] ?? $cond['condicion'] ?? '') ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($misConsultas)): ?>
                <div class="contenedor-tabla-consultas">
                    <h3 class="titulo-tabla-consultas">Mi Historial Médico</h3>
                    <table class="tabla-consultas">
                        <thead>
                            <tr class="tr-head-consultas">
                                <th class="th-consultas">Fecha</th>
                                <th class="th-consultas">Médico Tratante</th>
                                <th class="th-consultas">Motivo</th>
                                <th class="th-consultas">Síntomas</th>
                                <th class="th-consultas">Diagnóstico (CIE-10)</th>
                                <th class="th-consultas">Tratamiento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($misConsultas as $c): ?>
                                <tr class="tr-body-consultas">
                                    <td class="td-consultas-nowrap"><?= e(date('d/m/Y H:i', strtotime($c['fecha_consulta']))) ?></td>
                                    <td class="td-consultas"><?= e(($c['medico_nombre'] ?? '') . ' ' . ($c['medico_apellido'] ?? '')) ?></td>
                                    <td class="td-consultas">
                                        <?= e($c['motivo_de_visita']) ?>
                                        <?php if (!empty($c['observaciones'])): ?>
                                            <div class="td-paciente-sub"><strong>Obs:</strong> <?= e($c['observaciones']) ?></div>
                                        <?php endif; ?>
                                    </td>
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
                                    <td class="td-consultas"><?= e($c['medicamento_suministrado'] ?: 'Ninguno') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="contenedor-historial-vacio">
                    <p class="texto-historial-vacio">No hay consultas médicas asociadas a este usuario.</p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="dashboard-container">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3 class="titulo-configuracion-interna" style="margin: 0;">Panel de Inicio</h3>
                    <div style="display: flex; gap: 10px;">
                        <?php if (!empty($tieneRealizarConsulta) || !empty($GLOBALS['tieneRealizarConsulta'])): ?>
                            <a name="openModal" data-modal="modalRegistrarConsulta" class="action-card__button action-card__button--grid-principal btn-iniciar-consulta" href="#">Iniciar consulta</a>
                        <?php endif; ?>
                        <?php if (!empty($tieneGenerarReportes) || !empty($GLOBALS['tieneGenerarReportes'])): ?>
                            <a name="openModal" data-modal="modalReporteMorbilidad" class="action-card__button btn-generar-reporte" href="#" style="background-color: #0284c7; width: fit-content; text-align: center;">Generar Reporte de Morbilidad</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="dashboard-stats-grid">
                    <div class="stat-card">
                        <div class="stat-card__number"><?= $stats['total_consultas'] ?? 0 ?></div>
                        <div class="stat-card__label">Consultas Realizadas</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card__number"><?= $stats['total_usuarios'] ?? 0 ?></div>
                        <div class="stat-card__label">Usuarios Registrados</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card__number"><?= $stats['total_condiciones'] ?? 0 ?></div>
                        <div class="stat-card__label">Condiciones Médicas</div>
                    </div>
                </div>

                <div class="contenedor-tabla-consultas">
                    <h3 class="titulo-tabla-consultas" style="text-align: left; margin-bottom: 15px;">Últimas Consultas Registradas</h3>
                    <?php if (empty($consultasRecientesDashboard)): ?>
                        <div class="contenedor-historial-vacio">
                            <p class="texto-historial-vacio">No hay consultas médicas registradas recientemente.</p>
                        </div>
                    <?php else: ?>
                        <table class="tabla-consultas">
                            <thead>
                                <tr class="tr-head-consultas">
                                    <th class="th-consultas">Fecha</th>
                                    <th class="th-consultas">Paciente</th>
                                    <th class="th-consultas">Médico Tratante</th>
                                    <th class="th-consultas">Motivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($consultasRecientesDashboard as $c): ?>
                                    <tr class="tr-body-consultas">
                                        <td class="td-consultas-nowrap"><?= e(date('d/m/Y H:i', strtotime($c['fecha_consulta']))) ?></td>
                                        <td class="td-consultas"><strong><?= e(($c['paciente_nombre'] ?? '') . ' ' . ($c['paciente_apellido'] ?? '')) ?></strong></td>
                                        <td class="td-consultas"><?= e(($c['medico_nombre'] ?? '') . ' ' . ($c['medico_apellido'] ?? '')) ?></td>
                                        <td class="td-consultas"><?= e($c['motivo_de_visita']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION["registro_msg"])): ?>
            <div class="notification-banner notification-banner--<?= $_SESSION["registro_status"] ?>">
                <p><strong><?php echo e($_SESSION["registro_msg"]); unset($_SESSION["registro_msg"]); ?></strong></p>
            </div>
        <?php endif; ?>
    </section>

    <?php include_once __DIR__."/modals/modalRegistrarUsuario.php"; ?>
    <?php include_once __DIR__."/modals/modalActualizarUsuario.php"; ?>
    <?php include_once __DIR__."/modals/modalDetallesUsuario.php"; ?>
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
    <script src="assets/script/gestionpnfnucleo.js" defer></script>
    <script src="assets/script/gestionoferta.js" defer></script>
</footer>