<dialog id="modalRegistrarUsuario" class="modal-crud">
    <div class="action-card">
        <h3 class="action-card__title">Registro</h3>
        
        <form id="registroUsuarioForm" class="action-card__form--registrar-usuarios">
            <div class="action-card__form--grid">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="form" value="registrar_usuario">
                
                    <label for="cedula" class="action-card__label">Cédula de Identidad
                        <input type="tel" placeholder="12345678" inputmode="numeric" class="action-card__input" id="cedula" name="cedula" value="<?php echo isset($inputs['cedula']) ? $inputs['cedula'] : ''; ?>" required>
                    </label>

                    <label for="tipo" class="action-card__label">Tipo de Usuario
                        <select class="action-card__select" id="tipo" name="tipo" required>
                            <?php 
                            $tipos = $tipos ?? [];
                            $t = isset($inputs['tipo']) ? (string)$inputs['tipo'] : ''; 
                            ?>
                            <option value="" <?php echo ($t === '') ? 'selected' : ''; ?> disabled>Seleccione...</option>
                            <?php 
                            foreach ($tipos as $tipo): 
                                $idTipoStr = (string)$tipo['id_tipo'];
                            ?>
                                <option value="<?php echo e($tipo['id_tipo']); ?>" <?php echo ($t === $idTipoStr) ? 'selected' : ''; ?>><?php echo e($tipo['nombre_tipo']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                <label for="nombre" class="action-card__label">Nombres
                    <input placeholder="Iris" type="text" class="action-card__input" id="nombre" name="nombre" value="<?php echo isset($inputs['nombre']) ? $inputs['nombre'] : ''; ?>" required>
                </label>
                <label for="apellido" class="action-card__label">Apellidos
                    <input type="text" placeholder="Out" class="action-card__input" id="apellido" name="apellido" value="<?php echo isset($inputs['apellido']) ? $inputs['apellido'] : ''; ?>" required>
                </label>
                
                <label for="fecha_nacimiento" class="action-card__label">Fecha de Nacimiento
                    <input type="date" class="action-card__input" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo isset($inputs['fecha_nacimiento']) ? $inputs['fecha_nacimiento'] : ''; ?>" required>
                </label>
                <label for="tlfprincipal" class="action-card__label">Teléfono Principal
                    <input type="text" placeholder="04121234567" class="action-card__input" id="tlfprincipal" name="tlfprincipal" value="<?php echo isset($inputs['tlfprincipal']) ? $inputs['tlfprincipal'] : ''; ?>" required>
                </label>
                <label for="tlfemergencia" class="action-card__label">Teléfono de Emergencia
                    <input type="text" placeholder="04241234567" class="action-card__input" id="tlfemergencia" name="tlfemergencia" value="<?php echo isset($inputs['tlfemergencia']) ? $inputs['tlfemergencia'] : ''; ?>" required>
                </label>
                <label for="nombre_contacto_emergencia" class="action-card__label">Nombre del Contacto de Emergencia
                    <input type="text" placeholder="Reze" class="action-card__input" id="nombre_contacto_emergencia" name="nombre_contacto_emergencia" value="<?php echo isset($inputs['nombre_contacto_emergencia']) ? $inputs['nombre_contacto_emergencia'] : ''; ?>" required>
                </label>
                
                <label for="direccion" class="action-card__label">Dirección
                    <input type="text" placeholder="Los pocitos" class="action-card__input" id="direccion" name="direccion" value="<?php echo isset($inputs['direccion']) ? $inputs['direccion'] : ''; ?>" required>
                </label>

                <?php if (isset($tieneGestionarRolesPermisos)): ?>
                <label for="rol" class="action-card__label">Rol de Acceso
                    <select class="action-card__select" id="rol" name="rol" required>
                        <?php 
                        $rolesList = isset($userModel) ? $userModel->obtenerRoles() : [];
                        $defRol = isset($userModel) ? $userModel->obtenerRolDefecto() : 1;
                        $rInput = isset($inputs['rol']) ? (int)$inputs['rol'] : $defRol;
                        foreach ($rolesList as $rolOpt): 
                        ?>
                            <option value="<?= e($rolOpt['id_rol']) ?>" <?= $rInput === (int)$rolOpt['id_rol'] ? 'selected' : '' ?>><?= e($rolOpt['nombre_rol']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <?php endif; ?>

                <label for="nucleo_id" class="action-card__label">Núcleo
                    <select class="action-card__select" id="nucleo_id" name="nucleo_id">
                        <option value="" selected>No aplica / Seleccione...</option>
                        <?php if (!empty($nucleos)): ?>
                            <?php foreach ($nucleos as $n): ?>
                                <option value="<?= $n['id_nucleo'] ?>"><?= e($n['nombre_nucleo']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </label>

                <label for="pnf_id" class="action-card__label">PNF (Carrera)
                    <select class="action-card__select" id="pnf_id" name="pnf_id" disabled>
                        <option value="" selected>No aplica / Seleccione...</option>
                    </select>
                </label>
            </div>

            <?php $s = isset($inputs['sexo']) ? $inputs['sexo'] : ''; ?>
            <label> Masculino
                <input type="radio" name="sexo" id="sexo_m" value="1" <?php echo ($s === '1') ? 'checked' : ''; ?> required>
            </label>
        
            <label> Femenino
                <input type="radio" name="sexo" id="sexo_f" value="2" <?php echo ($s === '2') ? 'checked' : ''; ?>>
            </label>
            <div class="action-card__button-grid">
            <button type="reset" class="action-card__button action-card__button--red">Limpiar Formulario</button>
            <button type="submit" class="action-card__button">Guardar en Sistema</button>
            </div>
        </form>
    </div>
    <svg class="modal-crud__boton-cerrar" name="modalBotonCerrar" data-modal="modalRegistrarUsuario" fill="#000000" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 460.775 460.775" xml:space="preserve">
        <path d="M285.08,230.397L456.218,59.27c6.076-6.077,6.076-15.911,0-21.986L423.511,4.565c-2.913-2.911-6.866-4.55-10.992-4.55c-4.127,0-8.08,1.639-10.993,4.55l-171.138,171.14L59.25,4.565c-2.913-2.911-6.866-4.55-10.993-4.55c-4.126,0-8.08,1.639-10.992,4.55L4.558,37.284c-6.077,6.075-6.077,15.909,0,21.986l171.138,171.128L4.575,401.505c-6.074,6.077-6.074,15.911,0,21.986l32.709,32.719c2.911,2.911,6.865,4.55,10.992,4.55c4.127,0,8.08-1.639,10.994-4.55l171.117-171.12l171.118,171.12c2.913,2.911,6.866,4.55,10.993,4.55c4.128,0,8.081-1.639,10.992-4.55l32.709-32.719c6.074-6.075,6.074-15.909,0-21.986L285.08,230.397z"/>
    </svg>
</dialog>