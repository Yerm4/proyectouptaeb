<?php

if (!$_SESSION["cedula"]) {
    header("Location: login");
    exit;
}

$titulo = "Sedes";
include_once __DIR__."/layout/header.php";
?>

<main class="perfil">    
    <?php include_once __DIR__."/layout/sidebar.php"; ?>

    <section class="section-1 section-1--perfil">
        <!-- Inicio del bloque dinámico sedes-carreras -->
        <div id="sedes-carreras" class="contenedor-sedes-carreras" style="margin-top: 1.5rem; width: 100%; display: flex; flex-direction: column;">
            
            <div class="gestion-grid-superior" style="display: flex; flex-direction: column; width: 100%; gap: 2.5rem; margin-bottom: 2rem;">
                
                <!-- Tabla Núcleos -->
                <div class="contenedor-tabla-consultas" style="width: 100%;">
                    <div class="cabecera-tabla-global" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h3 class="titulo-tabla-consultas" style="margin: 0;">Núcleos</h3>
                        <a name="openModal" data-modal="modalRegistrarNucleo" class="action-card__button" href="#" style="width: fit-content; display: inline-block;">Registrar Nucleo</a>
                    </div>
                    <div id="alert-container-nucleo" style="margin-bottom: 1rem;"></div>
                    <table id="tablaNucleos" class="tabla-consultas" style="width: 100%;">
                        <thead>
                            <tr class="tr-head-consultas">
                                <th class="th-consultas" style="text-align: left;">ID</th>
                                <th class="th-consultas" style="text-align: left;">Nombre del Núcleo</th>
                                <th class="th-consultas" style="text-align: left;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTablaNucleos">
                            <?php 
                            if (!empty($nucleos)): 
                                $i = 1;
                                foreach ($nucleos as $n): 
                            ?>
                                    <tr class="tr-body-consultas">
                                        <td class="td-consultas-nowrap" style="text-align: left;"><?= $i++ ?></td>
                                        <td class="td-consultas" style="text-align: left;"><strong><?= e($n['nombre_nucleo']) ?></strong></td>
                                        
                                        <td class="td-acciones-btn" style="text-align: left;">
                                            <div style="display: flex; gap: 10px; justify-content: flex-start; align-items: center;">
                                                <button class="action-card__button editar-nucleo" name="openModal" data-modal="modalActualizarNucleo" data-id="<?= e($n['id_nucleo']) ?>" data-nombre="<?= e($n['nombre_nucleo']) ?>" style="width: fit-content; padding: 0.5rem 1rem; cursor: pointer">Actualizar</button>

                                                <form method="POST" action="index.php" class="form-eliminar-nucleo" style="display: inline; width: fit-content; margin: 0;">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                    <input type="hidden" name="form" value="eliminar_nucleo">
                                                    <input type="hidden" name="id_nucleo" value="<?= e($n['id_nucleo']) ?>">
                                                    <button type="submit" class="action-card__button" style="background-color: #d9534f; width: fit-content; padding: 0.5rem 1rem; cursor: pointer;">Eliminar</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="td-tabla-vacia">No hay núcleos registrados en el sistema.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Tabla PNFs -->
                <div id="contenedor-tabla-dinamica">
                    <div class="contenedor-tabla-consultas" style="width: 100%;">
                        <div class="cabecera-tabla-global" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h3 class="titulo-tabla-consultas" style="margin: 0;">Programas de Formación Nacional (PNF)</h3>
                            <a name="openModal" data-modal="modalRegistrarPNF" class="action-card__button" href="#" style="width: fit-content; display: inline-block; cursor: pointer">Registrar PNF</a>
                        </div>
                        
                        <div id="alert-container-pnf" style="margin-bottom: 1rem;"></div>
                        
                        <table id="tablaPnfs" class="tabla-consultas" style="width: 100%;">
                            <thead>
                                <tr class="tr-head-consultas">
                                    <th class="th-consultas" style="text-align: left;">ID</th>
                                    <th class="th-consultas" style="text-align: left;">Nombre del PNF</th>
                                    <th class="th-consultas" style="text-align: left;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-pnf-body">
                                <?php 
                                if (!empty($pnfs)): 
                                    $i = 1; 
                                    foreach ($pnfs as $p): 
                                ?>
                                    <tr class="tr-body-consultas">
                                        <td class="td-consultas-nowrap" style="text-align: left;"><?= $i++ ?></td>
                                        <td class="td-consultas" style="text-align: left;"><strong><?= e($p['nombre_pnf']) ?></strong></td>
                                        <td class="td-acciones-btn" style="text-align: left;">
                                            <div style="display: flex; gap: 10px; justify-content: flex-start; align-items: center;">
                                                <button class="action-card__button editar-pnf" name="openModal" data-modal="modalActualizarPNF" data-id="<?= e($p['id_pnf']) ?>" data-nombre="<?= e($p['nombre_pnf']) ?>" style="width: fit-content; padding: 0.5rem 1rem; cursor: pointer;">Actualizar</button>
                            
                                                <form method="POST" action="index.php" class="form-eliminar-pnf" style="display: inline; width: fit-content; margin: 0;">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                    <input type="hidden" name="form" value="eliminar_pnf">
                                                    <input type="hidden" name="id_pnf" value="<?= e($p['id_pnf']) ?>">
                                                    <button type="submit" class="action-card__button" style="background-color: #d9534f; width: fit-content; padding: 0.5rem 1rem; cursor: pointer;">Eliminar</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="td-tabla-vacia">No hay PNFs registrados en el sistema.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Importación de Modales vinculados a la vista -->
                <?php include_once __DIR__."/modals/modalRegistrarPNF.php" ?>
                <?php include_once __DIR__."/modals/modalActualizarPNF.php" ?>
                <?php include_once __DIR__."/modals/modalRegistrarNucleo.php" ?>
                <?php include_once __DIR__."/modals/modalActualizarNucleo.php" ?>
                <?php include_once __DIR__."/modals/modalRegistrarOferta.php" ?>

        <!-- Caja de Buscador (vacía en el renderizado hola.html debido a las condiciones de la vista) -->
        <div class="buscador-caja">
            <div class="section-1__box transition" id="section-1-box"></div>
        </div>

        <!-- Banner de Notificaciones -->
        <?php if (!empty($_SESSION["registro_msg"])): ?>
            <div class="notification-banner notification-banner--<?= $_SESSION["registro_status"] ?>">
                <p><strong><?php echo e($_SESSION["registro_msg"]); unset($_SESSION["registro_msg"]); ?></strong></p>
            </div>
        <?php endif; ?>
    </section>

    <!-- Modal de Usuario (presente al final del archivo hola.html) -->
        <script src="assets/script/gestionpnfnucleo.js" defer></script>

</main>