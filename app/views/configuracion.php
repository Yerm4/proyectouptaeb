<?php
if (!isset($_SESSION["cedula"])) {
    header("Location: login");
    exit;
}

$titulo = "Configuración";
include_once __DIR__."/layout/header.php";
?>

<main class="perfil">    
    <?php include_once __DIR__."/layout/sidebar.php"; ?>

    <section class="section-1 section-1--perfil">
<div id="seccion-configuracion" class="configuracion-container seccion-configuracion-box">
    <div class="nested-tabs-menu menu-subtabs">
        <?php if ($tieneGestionarRolesPermisos): ?>
            <a href="#" id="sub-tab-general" class="sub-tab-link subtab-link-comun subtab-link-general">General</a>
            <a href="#" id="sub-tab-roles" class="sub-tab-link subtab-link-comun subtab-link-roles">Roles y Permisos</a>
        <?php endif; ?>
        <?php if ($tieneGestionarCondiciones): ?>
            <a href="#" id="sub-tab-condiciones" class="sub-tab-link subtab-link-comun subtab-link-condiciones" style="<?= !$tieneGestionarRolesPermisos ? 'color:#333; border-bottom:3px solid blue;' : '' ?>">Condiciones</a>
        <?php endif; ?>
    </div>

    <?php if ($tieneGestionarRolesPermisos): ?>
        <!-- Pestaña Configuración General -->
        <div id="sub-content-general" class="sub-tab-content subcontent-general-box configuracion-bloque-formulario">
            <h3 class="titulo-configuracion-interna">Configuración General</h3>
            <form action="index.php" method="POST" class="form-configuracion-flex">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="form" value="guardar_configuracion">
                
                <label class="label-rol-defecto">
                    <span class="texto-label-negrita">Rol por defecto en registro</span>
                    <select name="rol_defecto" required class="action-card__select select-rol-defecto">
                        <?php 
                            $currentDefRol = $userModel->obtenerRolDefecto();
                            foreach ($roles as $role): 
                                $sel = ($role['id_rol'] == $currentDefRol) ? 'selected' : '';
                        ?>
                            <option value="<?= e($role['id_rol']) ?>" <?= $sel ?>><?= e($role['nombre_rol']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button type="submit" class="action-card__button btn-guardar-config">Guardar Configuración</button>
            </form>
        </div>

        <!-- Pestaña Roles y Permisos -->
        <div id="sub-content-roles" class="sub-tab-content subcontent-roles-box">
            <div class="contenedor-form-nuevo-rol">
                <h3 class="titulo-configuracion-interna">Crear Nuevo Rol</h3>
                <form action="index.php" method="POST" class="form-configuracion-flex">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="form" value="registrar_rol">
                    <input type="text" name="nombre_rol" required class="action-card__input" placeholder="Ej. Enfermero">
                    <input type="text" name="descripcion_rol" class="action-card__input" placeholder="Ej. Personal médico">
                    <button type="submit" class="action-card__button btn-crear-rol">Crear Rol</button>
                </form>
            </div>

            <hr class="separador-configuracion">

            <div class="contenedor-roles-registrados">
                <h3 class="titulo-configuracion-interna">Roles Registrados</h3>
                <table class="tabla-consultas">
                    <thead>
                        <tr class="tr-head-consultas">
                            <th>ID</th>
                            <th>Nombre del Rol</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roles as $role): ?>
                            <tr class="tr-body-consultas">
                                <td><?= e($role['id_rol']) ?></td>
                                <td><strong><?= e($role['nombre_rol']) ?></strong></td>
                                <td><?= e($role['descripcion_rol']) ?></td>
                                <td>
                                    <button type="button" class="action-card__button editar-rol" data-id="<?= e($role['id_rol']) ?>" data-nombre="<?= e($role['nombre_rol']) ?>" data-descripcion="<?= e($role['descripcion_rol']) ?>">Editar</button>
                                    <form action="index.php" method="POST" class="form-eliminar-inline">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="form" value="eliminar_rol">
                                        <input type="hidden" name="id_rol" value="<?= e($role['id_rol']) ?>">
                                        <button type="submit" class="action-card__button action-card__button--red">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <hr class="separador-configuracion">

            <h3 class="titulo-configuracion-interna">Matriz de Asignación de Permisos</h3>
            <form action="index.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="form" value="guardar_roles_permisos">
                <table class="tabla-consultas">
                    <thead>
                        <tr class="tr-head-consultas">
                            <th>Permiso / Descripción</th>
                            <?php foreach ($roles as $role): ?>
                                <th><?= e($role['nombre_role'] ?? $role['nombre_rol']) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($permisos)): ?>
                            <?php foreach ($permisos as $perm): ?>
                                <tr class="tr-body-consultas">
                                    <td>
                                        <div><?= e($perm['nombre_permiso']) ?></div>
                                        <small><?= e($perm['descripcion_permiso']) ?></small>
                                    </td>
                                    <?php foreach ($roles as $role): ?>
                                        <td>
                                            <input type="checkbox" name="permisos[<?= $role['id_rol'] ?>][]" value="<?= $perm['id_permiso'] ?>" <?= isset($rolePermMap[$role['id_rol']][$perm['id_permiso']]) ? 'checked' : '' ?>>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <button type="submit" class="action-card__button">Guardar Matriz de Permisos</button>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($tieneGestionarCondiciones): ?>
        <!-- Pestaña Condiciones Médicas -->
        <div id="sub-content-condiciones" class="sub-tab-content subcontent-condiciones-box" style="display: <?= $tieneGestionarRolesPermisos ? 'none' : 'block' ?>;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h3 class="titulo-configuracion-interna">Condiciones Registradas</h3>
                <button type="button" class="action-card__button" name="openModal" data-modal="modalRegistrarCondicion">Registrar Condición</button>
            </div>
            <table class="tabla-consultas" id="tablaCondiciones">
                <thead>
                    <tr class="tr-head-consultas">
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTablaCondiciones">
                    <?php if (empty($condicionesRegistradas)): ?>
                        <tr><td colspan="4" class="td-tabla-vacia">No hay condiciones registradas.</td></tr>
                    <?php else: foreach ($condicionesRegistradas as $cond): ?>
                        <tr class="tr-body-consultas">
                            <td><?= e($cond['id']) ?></td>
                            <td><strong><?= e($cond['nombre_condicion']) ?></strong></td>
                            <td><?= e($cond['descripcion_condicion']) ?></td>
                            <td>
                                <button type="button" class="action-card__button editar-condicion" data-id="<?= e($cond['id']) ?>" data-nombre="<?= e($cond['nombre_condicion']) ?>" data-descripcion="<?= e($cond['descripcion_condicion']) ?>">Editar</button>
                                <form action="index.php" method="POST" class="form-eliminar-inline">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="form" value="eliminar_condicion">
                                    <input type="hidden" name="id" value="<?= e($cond['id']) ?>">
                                    <button type="submit" class="action-card__button action-card__button--red">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
    </section>

    <?php include_once __DIR__."/modals/modalEditarRol.php"; ?>
    <?php include_once __DIR__."/modals/modalEditarCondicion.php"; ?>
    <?php include_once __DIR__."/modals/modalRegistrarCondicion.php"; ?>
    <script src="assets/script/append.js" defer></script>
    <script src="assets/script/gestion.js" defer></script>
</main>