document.addEventListener('DOMContentLoaded', function() {
    
    // =========================================================================
    // FUNCIÓN PARA REFRESCAR LA TABLA DESDE EL SERVIDOR (TRAE LOS IDS REALES)
    // =========================================================================
    function actualizarTablaCompleta() {
        fetch(window.location.href)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const nuevaTabla = doc.getElementById('contenedor-tabla-dinamica');
                const contenedorActual = document.getElementById('contenedor-tabla-dinamica');
                
                if (nuevaTabla && contenedorActual) {
                    contenedorActual.innerHTML = nuevaTabla.innerHTML;

                    const tablaObjetivo = document.getElementById('contenedor-tabla-dinamica');
                    if (tablaObjetivo) {
                        tablaObjetivo.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            })
            .catch(err => console.error("Error al refrescar la tabla:", err));
    }

    // =========================================================================
    // FUNCIÓN AUXILIAR INTERNA: COLOCA EL ERROR DENTRO DEL FORMULARIO (MODALES)
    // =========================================================================
    function mostrarErrorEnFormulario(formulario, mensaje) {
        let contenedorModal = formulario.querySelector('.alert-container-modal');
        if (!contenedorModal) {
            contenedorModal = document.createElement('div');
            contenedorModal.className = 'alert-container-modal';
            formulario.insertBefore(contenedorModal, formulario.firstChild);
        }

        const idAlertaError = 'err_modal_' + Date.now();

        contenedorModal.innerHTML = `
            <div id="${idAlertaError}" class="action-card" style="padding: 1rem; border-left: 5px solid #e74c3c; background: #fdfdfd; width: 100%; box-sizing: border-box; margin-bottom: 1rem;">
                <p style="margin: 0; font-weight: bold; display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <span>${mensaje}</span>
                    <span style="cursor: pointer; font-size: 1.2rem; padding: 0 5px;" onclick="this.closest('.action-card').remove()">×</span>
                </p>
            </div>
        `;

        setTimeout(() => {
            const errActivo = document.getElementById(idAlertaError);
            if (errActivo) errActivo.remove();
        }, 4000);
    }

    // =========================================================================
    // 1. CONTROL DE APERTURA DE MODAL "REGISTRAR PNF"
    // =========================================================================
    document.addEventListener('click', function(e) {
        const btnReg = e.target.closest('[data-modal="modalRegistrarPNF"]');
        if (btnReg) {
            e.preventDefault();
            const modalReg = document.getElementById('modalRegistrarPNF');
            if (modalReg) {
                modalReg.showModal(); 
                modalReg.style.opacity = '1';
            }
        }
    });

    // =========================================================================
    // 2. ESCUCHAR EL BOTÓN "ACTUALIZAR" EN LA TABLA Y RELLENAR MODAL
    // =========================================================================
    document.addEventListener('click', function(event) {
        const boton = event.target.closest('.editar-pnf');
        if (boton) {
            event.preventDefault();
            const idPnf = boton.getAttribute('data-id');
            const nombrePnf = boton.getAttribute('data-nombre');
            const modalEditar = document.getElementById('modalActualizarPNF');
            
            if (modalEditar) {
                document.getElementById('edit_id_pnf').value = idPnf;
                document.getElementById('edit_nombre_pnf').value = nombrePnf || '';
                modalEditar.setAttribute('data-fila-id', idPnf);
                
                modalEditar.showModal();
                modalEditar.style.opacity = '1';
            }
        }
    });

    // =========================================================================
    // 3. ENVIAR EL FORMULARIO DE REGISTRO (NUEVO PNF)
    // =========================================================================
    const formRegistrarPNF = document.getElementById('formRegistrarPNF');

    if (formRegistrarPNF) {
        formRegistrarPNF.addEventListener('submit', function(e) {
            e.preventDefault(); 

            const formData = new FormData(this);
            const modalReg = document.getElementById('modalRegistrarPNF');

            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    formRegistrarPNF.reset();
                    const contenedorModal = formRegistrarPNF.querySelector('.alert-container-modal');
                    if (contenedorModal) contenedorModal.innerHTML = '';
                    
                    if (modalReg) {
                        modalReg.style.opacity = '0';
                        modalReg.close();
                    }

                    actualizarTablaCompleta();
                    
                    // Pequeña tregua de 50ms para evitar bloqueos con el cierre de la modal
                    setTimeout(() => {
                        mostrarAlertaSeccion(data.message || "¡PNF registrado con éxito!", "success");
                    }, 50);

                } else {
                    mostrarErrorEnFormulario(this, data.message || "Ocurrió un error.");
                }
            })
            .catch(error => {
                console.error("Error en el registro:", error);
                mostrarErrorEnFormulario(this, "Ocurrió un error al procesar el registro.");
            });
        });
    }

    // =========================================================================
    // 4. ENVIAR EL FORMULARIO DE ACTUALIZACIÓN (PNF)
    // =========================================================================
    const formActualizarPNF = document.getElementById('formActualizarPNF');

    if (formActualizarPNF) {
        formActualizarPNF.addEventListener('submit', function(e) {
            e.preventDefault(); 

            const formData = new FormData(this);
            const modalEditar = document.getElementById('modalActualizarPNF');

            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const contenedorModal = formActualizarPNF.querySelector('.alert-container-modal');
                    if (contenedorModal) contenedorModal.innerHTML = '';
                    
                    if (modalEditar) {
                        modalEditar.style.opacity = '0';
                        modalEditar.close();
                    }
                    
                    actualizarTablaCompleta();
                    
                    setTimeout(() => {
                        mostrarAlertaSeccion(data.message || "¡PNF actualizado con éxito!", "success");
                    }, 50);

                } else {
                    mostrarErrorEnFormulario(this, data.message || "Ocurrió un error.");
                }
            })
            .catch(error => {
                console.error("Error en la actualización:", error);
                mostrarErrorEnFormulario(this, "Ocurrió un error al procesar la actualización.");
            });
        });
    }

    // =========================================================================
    // 5. LIMPIAR ATRIBUTOS Y ALERTAS INTERNAS AL CERRAR MODALES
    // =========================================================================
    document.querySelectorAll('dialog').forEach(modal => {
        modal.addEventListener('close', () => {
            modal.style.opacity = '0';
            const contenedorModal = modal.querySelector('.alert-container-modal');
            if (contenedorModal) contenedorModal.innerHTML = '';
        });
    });

    // =========================================================================
    // FUNCIÓN PARA PINTAR LAS NOTIFICACIONES DE PNF (AUTO-BORRADO SEGURO)
    // =========================================================================
    function mostrarAlertaSeccion(mensaje, tipo) {
        const contenedor = document.getElementById('alert-container-pnf');
        if (!contenedor) return;
        
        const idAlertaPnf = 'alert_pnf_' + Date.now();

        contenedor.innerHTML = `
            <div id="${idAlertaPnf}" class="action-card" style="padding: 1rem; border-left: 5px solid ${tipo === 'success' ? '#2ecc71' : '#e74c3c'}; background: #fdfdfd; width: 100%; box-sizing: border-box; margin-bottom: 1rem;">
                <p style="margin: 0; font-weight: bold; display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <span>${mensaje}</span>
                    <span style="cursor: pointer; font-size: 1.2rem; padding: 0 5px;" onclick="this.closest('.action-card').remove()">×</span>
                </p>
            </div>
        `;

        // Se remueve de manera segura únicamente este mensaje tras 4 segundos
        setTimeout(() => {
            const alertaActiva = document.getElementById(idAlertaPnf);
            if (alertaActiva) alertaActiva.remove();
        }, 4000);
    }

    // =========================================================================
    // INTERCEPTAR EL FORMULARIO DE ELIMINACIÓN CON FETCH (PNF)
    // =========================================================================
    document.addEventListener('submit', function(e) {
        const formEliminar = e.target.closest('.form-eliminar-pnf');
        
        if (formEliminar) {
            e.preventDefault(); 

            if (!confirm('¿Seguro que deseas eliminar este PNF?')) {
                return;
            }

            const formData = new FormData(formEliminar);

            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    actualizarTablaCompleta();
                    mostrarAlertaSeccion(data.message || "¡PNF eliminado con éxito!", "success");
                } else {
                    mostrarAlertaSeccion(data.message || "Ocurrió un error al intentar eliminar.", "error");
                }
            })
            .catch(error => {
                console.error("Error en la eliminación:", error);
                mostrarAlertaSeccion("Ocurrió un error al procesar la eliminación.", "error");
            });
        }
    });
});

// =========================================================================
// 1. REFRESCAR TABLA DE NÚCLEOS
// =========================================================================
function actualizarTablaNucleos() {
    fetch(window.location.href)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            const nuevaTabla = doc.getElementById('cuerpoTablaNucleos');
            const contenedorActual = document.getElementById('cuerpoTablaNucleos');
            
            if (nuevaTabla && contenedorActual) {
                contenedorActual.innerHTML = nuevaTabla.innerHTML;

                const tablaObjetivo = document.getElementById('cuerpoTablaNucleos').closest('table') || contenedorActual;
                if (tablaObjetivo) {
                    tablaObjetivo.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        })
        .catch(err => console.error("Error al refrescar la tabla de núcleos:", err));
}

// =========================================================================
// 2. APERTURA DE MODAL "REGISTRAR NÚCLEO"
// =========================================================================
document.addEventListener('click', function(e) {
    const btnRegNucleo = e.target.closest('[data-modal="modalRegistrarNucleo"]');
    if (btnRegNucleo) {
        e.preventDefault();
        const modal = document.getElementById('modalRegistrarNucleo');
        if (modal) { 
            modal.showModal(); 
            modal.style.opacity = '1'; 
        }
    }
});

// =========================================================================
// 3. BOTÓN "ACTUALIZAR" (RELLENAR MODAL EDICIÓN NÚCLEO)
// =========================================================================
document.addEventListener('click', function(event) {
    const botonNucleo = event.target.closest('.editar-nucleo');
    if (botonNucleo) {
        event.preventDefault();
        const idNucleo = botonNucleo.getAttribute('data-id');
        const nombreNucleo = botonNucleo.getAttribute('data-nombre');
        const modalEditarNucleo = document.getElementById('modalActualizarNucleo');
        
        if (modalEditarNucleo) {
            document.getElementById('edit_id_nucleo').value = idNucleo;
            document.getElementById('edit_nombre_nucleo').value = nombreNucleo || '';
            modalEditarNucleo.showModal();
            modalEditarNucleo.style.opacity = '1';
        }
    }
});

// =========================================================================
// 4. PROCESAR REGISTRO Y ACTUALIZACIÓN DE NÚCLEOS (MÉTODO REUTILIZABLE)
// =========================================================================
const configurarEnvioFormularioNucleo = (idForm, idModal, msgExitoDefault) => {
    const form = document.getElementById(idForm);
    if (!form) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const datos = Object.fromEntries(formData.entries())
        const modal = document.getElementById(idModal);

    try {
        const response = await fetch("api/nucleos", {
            method: "PUT",
            headers: {"Content-Type":"application/json"},
            body: JSON.stringify(formData)

        });
        const result = await response.json();
        if (!response.ok){throw new Error("Error en la consulta")}
        if (result.status === "ok"){
            const data = result.message 
            form.reset();
            const contenedorModal = form.querySelector('.alert-container-modal');
            if (contenedorModal) contenedorModal.innerHTML = '';
            
            if (modal) {
                modal.style.opacity = '0';
                modal.close();
            }
                
            actualizarTablaNucleos(); 
            
            setTimeout(() => {
                mostrarAlertaNucleo(data.message || msgExitoDefault, "success");
            }, 50);
        } else {
            let contenedorModal = form.querySelector('.alert-container-modal');
            if (!contenedorModal) {
                contenedorModal = document.createElement('div');
                contenedorModal.className = 'alert-container-modal';
                form.insertBefore(contenedorModal, form.firstChild);
            }
            
            const idErrNuc = 'err_nuc_' + Date.now();
            contenedorModal.innerHTML = `
                <div id="${idErrNuc}" class="action-card" style="padding: 1rem; border-left: 5px solid #e74c3c; background: #fdfdfd; width: 100%; box-sizing: border-box; margin-bottom: 1rem;">
                    <p style="margin: 0; font-weight: bold; display: flex; justify-content: space-between; align-items: center; width: 100%;">
                        <span>${result.message || "Ocurrió un error."}</span>
                        <span style="cursor: pointer; font-size: 1.2rem; padding: 0 5px;" onclick="this.closest('.action-card').remove()">×</span>
                    </p>
                </div>
            `;
            
            setTimeout(() => {
                const errActivo = document.getElementById(idErrNuc);
                if (errActivo) errActivo.remove();
            }, 4000); 
        }  
    } catch(error) {
            console.error(`Error en formulario ${idForm}:`, error);
            let contenedorModal = form.querySelector('.alert-container-modal');
            if (!contenedorModal) {
                contenedorModal = document.createElement('div');
                contenedorModal.className = 'alert-container-modal';
                form.insertBefore(contenedorModal, form.firstChild);
            }
            contenedorModal.innerHTML = `
                <div class="action-card" style="padding: 1rem; border-left: 5px solid #e74c3c; background: #fdfdfd; width: 100%; box-sizing: border-box; margin-bottom: 1rem;">
                    <p style="margin: 0; font-weight: bold; display: flex; justify-content: space-between; align-items: center; width: 100%;">
                        <span>Ocurrió un error al procesar la solicitud.</span>
                        <span style="cursor: pointer; font-size: 1.2rem; padding: 0 5px;" onclick="this.closest('.action-card').remove()">×</span>
                    </p>
                </div>
            `;
        };
    });
};

configurarEnvioFormularioNucleo('formRegistrarNucleo', 'modalRegistrarNucleo', "¡Núcleo registrado con éxito!");
configurarEnvioFormularioNucleo('formActualizarNucleo', 'modalActualizarNucleo', "¡Núcleo actualizado con éxito!");

// =========================================================================
// 5. INTERCEPTAR ELIMINACIÓN DE NÚCLEOS
// =========================================================================
document.addEventListener('submit', function(e) {
    const formEliminarNucleo = e.target.closest('.form-eliminar-nucleo');
    if (formEliminarNucleo) {
        e.preventDefault();
        if (!confirm('¿Seguro que deseas eliminar este núcleo?')) return;

        fetch('index.php', { method: 'POST', body: new FormData(formEliminarNucleo) })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                actualizarTablaNucleos();
                mostrarAlertaNucleo(data.message || "¡Núcleo eliminado!", "success");
            } else {
                mostrarAlertaNucleo(data.message || "Error al eliminar.", "error");
            }
        }).catch(err => console.error(err));
    }
});

// =========================================================================
// 6. ALERTAS EXCLUSIVAS PARA NÚCLEOS (AUTO-BORRADO SEGURO)
// =========================================================================
function mostrarAlertaNucleo(mensaje, tipo) {
    const contenedor = document.getElementById('alert-container-nucleo');
    if (!contenedor) return;
    
    const idAlertaNucleo = 'alert_nuc_gen_' + Date.now();

    contenedor.innerHTML = `
        <div id="${idAlertaNucleo}" class="action-card" style="padding: 1rem; border-left: 5px solid ${tipo === 'success' ? '#2ecc71' : '#e74c3c'}; background: #fdfdfd; width: 100%; box-sizing: border-box; margin-bottom: 1rem;">
            <p style="margin: 0; font-weight: bold; display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <span>${mensaje}</span>
                <span style="cursor: pointer; font-size: 1.2rem; padding: 0 5px;" onclick="this.closest('.action-card').remove()">×</span>
            </p>
        </div>
    `;

    setTimeout(() => {
        const alertaActiva = document.getElementById(idAlertaNucleo);
        if (alertaActiva) alertaActiva.remove();
    }, 4000);
}