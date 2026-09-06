function calcularEdadJS(fechaNacimiento) {
    if (!fechaNacimiento || fechaNacimiento.trim() === "") return "No registrado";
    
    const nacimiento = new Date(fechaNacimiento);
    const actual = new Date();
    
    if (nacimiento > actual) return "Fecha invalida";
    
    let edad = actual.getFullYear() - nacimiento.getFullYear();
    const mes = actual.getMonth() - nacimiento.getMonth();
    
    if (mes < 0 || (mes === 0 && actual.getDate() < nacimiento.getDate())) {
        edad--;
    }
    
    return edad;
}

const inputBuscarC = document.getElementById('inputBuscarConsulta');
const cuerpoTablaConsultas = document.getElementById('cuerpoTablaConsultas');
const modalActualizarConsulta = document.getElementById('modalActualizarConsulta');
const btnCargarMas = document.getElementById('btnCargarMasConsultas');

function checkCargarMasVisibility() {
    if (!btnCargarMas || !cuerpoTablaConsultas) return;
    const rowsCount = cuerpoTablaConsultas.querySelectorAll('tr:not(.no-registros)').length;
    if (rowsCount > 0 && rowsCount % 20 === 0) {
        btnCargarMas.style.display = 'block';
    } else {
        btnCargarMas.style.display = 'none';
    }
}

// Run initial visibility check on page load
checkCargarMasVisibility();

if (inputBuscarC && cuerpoTablaConsultas) {
    inputBuscarC.addEventListener('input', function() {
        const textoBusqueda = inputBuscarC.value.trim();
        const tokenCSRF = document.querySelector('input[name="csrf_token"]')?.value || '';

        fetch(`api/consulta?query=${encodeURIComponent(textoBusqueda)}`)
        .then(response => response.json())
        .then(res => {
            const consultas = Array.isArray(res) ? res : (res.data || []);
            cuerpoTablaConsultas.innerHTML = '';

            if (consultas.length === 0) {
                cuerpoTablaConsultas.innerHTML = `<tr class="no-registros"><td colspan="7" style="text-align:center; padding: 30px; color: #666;">No hay ninguna consulta asociada a ese usuario.</td></tr>`;
                if (btnCargarMas) btnCargarMas.style.display = 'none';
                return;
            }

            consultas.forEach(c => {
                const fila = document.createElement('tr');
                fila.style.borderBottom = '1px solid #eee';

                const dateObj = new Date(c.fecha_consulta);
                const formattedDate = !isNaN(dateObj) ? 
                    `${String(dateObj.getDate()).padStart(2, '0')}/${String(dateObj.getMonth() + 1).padStart(2, '0')}/${dateObj.getFullYear()} ${String(dateObj.getHours()).padStart(2, '0')}:${String(dateObj.getMinutes()).padStart(2, '0')}` : 
                    c.fecha_consulta;

                const pacienteNombre = `${c.paciente_nombre || ''} ${c.paciente_apellido || ''}`.trim();
                const medicoNombre = `${c.medico_nombre || ''} ${c.medico_apellido || ''}`.trim();

                let sintomasHtml = '<span style="color: #999;">Ninguno</span>';
                if (c.sintomas && c.sintomas.length > 0) {
                    sintomasHtml = c.sintomas.join(', ');
                }

                let diagsHtml = '<span style="color: #999;">Sin diagnóstico</span>';
                if (c.diagnosticos && c.diagnosticos.length > 0) {
                    diagsHtml = '';
                    c.diagnosticos.forEach(d => {
                        diagsHtml += `<div style="margin-bottom: 2px;"><strong style="color: #b91c1c;">${d.codigo_icd_diagnostico}</strong> - ${d.patologia || 'Sin detalle'}</div>`;
                    });
                }

                fila.innerHTML = `
                    <td style="padding: 10px; font-size: 0.9em; white-space: nowrap;">${formattedDate}</td>
                    <td style="padding: 10px; font-size: 0.9em;">
                        <strong>${pacienteNombre}</strong>
                        <div style="font-size: 0.8em; color: #666;">C.I. ${c.id_usuario}</div>
                    </td>
                    <td style="padding: 10px; font-size: 0.9em;">${medicoNombre}</td>
                    <td style="padding: 10px; font-size: 0.9em;">${c.motivo_de_visita}</td>
                    <td style="padding: 10px; font-size: 0.9em;">${sintomasHtml}</td>
                    <td style="padding: 10px; font-size: 0.9em;">${diagsHtml}</td>
                    <td style="padding: 10px; font-size: 0.9em; display: flex; gap: 5px;">
                        <button class="ver-detalles-consulta action-card__button" data-id="${c.id}" style="background: #4a5568; color: #fff;">Ver detalles</button>
                        ${ES_MEDICO_O_DIRECTOR ? `<button class="editar-consulta action-card__button" data-id="${c.id}">Actualizar</button>` : ''}
                    </td>
                `;
                cuerpoTablaConsultas.appendChild(fila);
            });

            checkCargarMasVisibility();
        })
        .catch(error => console.error("Error al buscar consultas:", error));
    });
}

if (btnCargarMas && cuerpoTablaConsultas) {
    btnCargarMas.addEventListener('click', function() {
        const query = inputBuscarC ? inputBuscarC.value.trim() : '';
        const offset = cuerpoTablaConsultas.querySelectorAll('tr:not(.no-registros)').length;
        const tokenCSRF = document.querySelector('input[name="csrf_token"]')?.value || '';

        fetch(`api/consulta?query=${encodeURIComponent(query)}&offset=${offset}`)
        .then(response => response.json())
        .then(res => {
            const consultas = Array.isArray(res) ? res : (res.data || []);
            if (consultas.length === 0) {
                btnCargarMas.style.display = 'none';
                return;
            }

            consultas.forEach(c => {
                const fila = document.createElement('tr');
                fila.style.borderBottom = '1px solid #eee';

                const dateObj = new Date(c.fecha_consulta);
                const formattedDate = !isNaN(dateObj) ? 
                    `${String(dateObj.getDate()).padStart(2, '0')}/${String(dateObj.getMonth() + 1).padStart(2, '0')}/${dateObj.getFullYear()} ${String(dateObj.getHours()).padStart(2, '0')}:${String(dateObj.getMinutes()).padStart(2, '0')}` : 
                    c.fecha_consulta;

                const pacienteNombre = `${c.paciente_nombre || ''} ${c.paciente_apellido || ''}`.trim();
                const medicoNombre = `${c.medico_nombre || ''} ${c.medico_apellido || ''}`.trim();

                let sintomasHtml = '<span style="color: #999;">Ninguno</span>';
                if (c.sintomas && c.sintomas.length > 0) {
                    sintomasHtml = c.sintomas.join(', ');
                }

                let diagsHtml = '<span style="color: #999;">Sin diagnóstico</span>';
                if (c.diagnosticos && c.diagnosticos.length > 0) {
                    diagsHtml = '';
                    c.diagnosticos.forEach(d => {
                        diagsHtml += `<div style="margin-bottom: 2px;"><strong style="color: #b91c1c;">${d.codigo_icd_diagnostico}</strong> - ${d.patologia || 'Sin detalle'}</div>`;
                    });
                }

                fila.innerHTML = `
                    <td style="padding: 10px; font-size: 0.9em; white-space: nowrap;">${formattedDate}</td>
                    <td style="padding: 10px; font-size: 0.9em;">
                        <strong>${pacienteNombre}</strong>
                        <div style="font-size: 0.8em; color: #666;">C.I. ${c.id_usuario}</div>
                    </td>
                    <td style="padding: 10px; font-size: 0.9em;">${medicoNombre}</td>
                    <td style="padding: 10px; font-size: 0.9em;">${c.motivo_de_visita}</td>
                    <td style="padding: 10px; font-size: 0.9em;">${sintomasHtml}</td>
                    <td style="padding: 10px; font-size: 0.9em;">${diagsHtml}</td>
                    <td style="padding: 10px; font-size: 0.9em; display: flex; gap: 5px;">
                        <button class="ver-detalles-consulta action-card__button" data-id="${c.id}" style="background: #4a5568; color: #fff;">Ver detalles</button>
                        ${ES_MEDICO_O_DIRECTOR ? `<button class="editar-consulta action-card__button" data-id="${c.id}">Actualizar</button>` : ''}
                    </td>
                `;
                cuerpoTablaConsultas.appendChild(fila);
            });

            checkCargarMasVisibility();
        })
        .catch(error => console.error("Error al cargar más consultas:", error));
    });
}

if (cuerpoTablaConsultas && modalActualizarConsulta) {
    cuerpoTablaConsultas.addEventListener('click', function(event) {
        if (event.target.classList.contains('ver-detalles-consulta')) {
            event.preventDefault();
            const idConsulta = event.target.getAttribute('data-id');
            const tokenCSRF = document.querySelector('input[name="csrf_token"]')?.value || '';
            const modalVer = document.getElementById('modalVerDetallesConsulta');

            fetch(`api/consulta/${idConsulta}`)
            .then(response => response.json())
            .then(res => {
                const consulta = res.data || res;
                if (consulta.error || res.status === 'error') {
                    alert(consulta.error || res.message);
                    return;
                }

                const dateObj = new Date(consulta.fecha_consulta);
                const formattedDate = !isNaN(dateObj) ? 
                    `${String(dateObj.getDate()).padStart(2, '0')}/${String(dateObj.getMonth() + 1).padStart(2, '0')}/${dateObj.getFullYear()} ${String(dateObj.getHours()).padStart(2, '0')}:${String(dateObj.getMinutes()).padStart(2, '0')}` : 
                    consulta.fecha_consulta;

                document.getElementById('det_fecha').textContent = formattedDate;
                document.getElementById('det_paciente').textContent = `${consulta.paciente_nombre || ''} ${consulta.paciente_apellido || ''} (C.I. ${consulta.id_usuario})`;
                document.getElementById('det_medico').textContent = `${consulta.medico_nombre || ''} ${consulta.medico_apellido || ''}`;
                document.getElementById('det_motivo').textContent = consulta.motivo_de_visita || 'Ninguno';
                document.getElementById('det_observaciones').textContent = consulta.observaciones || 'Ninguna';
                document.getElementById('det_medicamento').textContent = consulta.medicamento_suministrado || 'Ninguno';

                const sintomasSpan = document.getElementById('det_sintomas');
                if (consulta.sintomas && consulta.sintomas.length > 0) {
                    sintomasSpan.textContent = consulta.sintomas.join(', ');
                } else {
                    sintomasSpan.innerHTML = '<span style="color: #999;">Ninguno</span>';
                }

                const diagnosticosDiv = document.getElementById('det_diagnosticos');
                diagnosticosDiv.innerHTML = '';
                if (consulta.diagnosticos && consulta.diagnosticos.length > 0) {
                    consulta.diagnosticos.forEach(d => {
                        const div = document.createElement('div');
                        div.style.marginBottom = '4px';
                        div.innerHTML = `<strong style="color: #b91c1c;">${d.codigo_icd_diagnostico}</strong> - ${d.patologia || 'Sin detalle'}`;
                        diagnosticosDiv.appendChild(div);
                    });
                } else {
                    diagnosticosDiv.innerHTML = '<span style="color: #999;">Sin diagnóstico</span>';
                }

                modalVer.showModal();
                setTimeout(() => {
                    modalVer.style.opacity = '1';
                }, 50);
            })
            .catch(error => console.error("Error al cargar detalles de la consulta:", error));
        }

        if (event.target.classList.contains('editar-consulta')) {
            event.preventDefault();
            
            const idConsulta = event.target.getAttribute('data-id');
            const tokenCSRF = document.querySelector('input[name="csrf_token"]').value;

            fetch(`api/consulta/${idConsulta}`)
            .then(response => response.json())
            .then(res => {
                const consulta = res.data || res;
                if (consulta.error || res.status === 'error') {
                    alert(consulta.error || res.message);
                    return;
                }

                // Hide search and show only the edit form
                const editForm = document.getElementById("formulario-edicion-consulta");
                const searchSection = document.getElementById("seccion-busqueda-paciente-actualizar");
                const listContainer = document.getElementById("consultas-lista-actualizar");
                const condInfo = document.getElementById("paciente-condiciones-info-actualizar");

                if (editForm) editForm.style.display = "block";
                if (searchSection) searchSection.style.display = "none";
                if (listContainer) listContainer.innerHTML = "";
                if (condInfo) condInfo.style.display = "none";

                if (typeof loadConsultaIntoEditForm === 'function') {
                    loadConsultaIntoEditForm(consulta);
                }

                modalActualizarConsulta.showModal();
                setTimeout(() => {
                    modalActualizarConsulta.style.opacity = "1";
                }, 500);
            })
            .catch(error => console.error("Error al cargar datos de la consulta:", error));
        }
    });
}

// Handle top menu "Actualizar consulta" button click to show search section and hide form
const btnActualizarConsultaTop = document.querySelector('[data-modal="modalActualizarConsulta"]');
if (btnActualizarConsultaTop) {
    btnActualizarConsultaTop.addEventListener('click', function() {
        const editForm = document.getElementById("formulario-edicion-consulta");
        const searchSection = document.getElementById("seccion-busqueda-paciente-actualizar");
        const listContainer = document.getElementById("consultas-lista-actualizar");
        const condInfo = document.getElementById("paciente-condiciones-info-actualizar");
        const searchInput = document.getElementById("paciente-search-actualizar");
        const hiddenInput = document.getElementById("cedula_paciente_actualizar");

        if (editForm) editForm.style.display = "none";
        if (searchSection) searchSection.style.display = "block";
        if (listContainer) listContainer.innerHTML = "";
        if (condInfo) condInfo.style.display = "none";
        if (searchInput) searchInput.value = "";
        if (hiddenInput) hiddenInput.value = "";
    });
}

document.addEventListener('click', function(event) {
    if (event.target.classList.contains('editar-condicion')) {
        event.preventDefault();
        
        const idCondicion = event.target.getAttribute('data-id');
        const nombreCondicion = event.target.getAttribute('data-nombre');
        const descripcionCondicion = event.target.getAttribute('data-descripcion');
        
        const modalEditar = document.getElementById('modalEditarCondicion');
        if (modalEditar) {
            document.getElementById('edit_id_condicion').value = idCondicion;
            document.getElementById('edit_nombre_condicion').value = nombreCondicion;
            document.getElementById('edit_descripcion_condicion').value = descripcionCondicion;
            
            modalEditar.showModal();
            setTimeout(() => {
                modalEditar.style.opacity = '1';
            }, 50);
        }
    }
});

const inputBuscarCondicion = document.getElementById('inputBuscarCondicion');
const cuerpoTablaCondiciones = document.getElementById('cuerpoTablaCondiciones');

if (inputBuscarCondicion && cuerpoTablaCondiciones) {
    const filas = Array.from(cuerpoTablaCondiciones.querySelectorAll('tr'));
    const esVacia = filas.length === 1 && filas[0].querySelector('.td-tabla-vacia') && !filas[0].classList.contains('fila-vacia-sugerida');
    
    if (!esVacia) {
        function filtrarCondiciones() {
            const query = inputBuscarCondicion.value.toLowerCase().trim();
            let mostrados = 0;
            
            filas.forEach(fila => {
                if (fila.classList.contains('fila-vacia-sugerida')) return;
                
                const nombre = fila.children[1]?.textContent.toLowerCase() || "";
                const descripcion = fila.children[2]?.textContent.toLowerCase() || "";
                const coincide = nombre.includes(query) || descripcion.includes(query);
                
                if (coincide && mostrados < 10) {
                    fila.style.display = "";
                    mostrados++;
                } else {
                    fila.style.display = "none";
                }
            });
            
            let rowVacio = cuerpoTablaCondiciones.querySelector('.fila-vacia-sugerida');
            if (mostrados === 0 && query !== "") {
                if (!rowVacio) {
                    rowVacio = document.createElement('tr');
                    rowVacio.className = 'fila-vacia-sugerida';
                    rowVacio.innerHTML = '<td colspan="4" class="td-tabla-vacia" style="text-align:center;">No se encontraron condiciones que coincidan.</td>';
                    cuerpoTablaCondiciones.appendChild(rowVacio);
                } else {
                    rowVacio.style.display = "";
                }
            } else if (rowVacio) {
                rowVacio.style.display = "none";
            }
        }
        
        inputBuscarCondicion.addEventListener('input', filtrarCondiciones);
        filtrarCondiciones();
    }
}

function cargarPnfsPorNucleo(idNucleo, selectPnfElement, pnfSeleccionado = null) {
    if (!selectPnfElement) return;

    selectPnfElement.innerHTML = '<option value="">No aplica / Seleccione...</option>';
    selectPnfElement.disabled = true;

    if (!idNucleo || idNucleo === "") {
        return;
    }

    const tokenCSRF = document.querySelector('input[name="csrf_token"]').value;

    fetch(`api/nucleos/pnfs/${idNucleo}`)
    .then(response => response.json())
    .then(res => {
        const pnfs = Array.isArray(res) ? res : (res.data || []);
        if (Array.isArray(pnfs) && pnfs.length > 0) {
            selectPnfElement.disabled = false;
            pnfs.forEach(pnf => {
                const opt = document.createElement('option');
                opt.value = pnf.id_pnf;
                opt.textContent = pnf.nombre_pnf;
                if (pnfSeleccionado && String(pnf.id_pnf) === String(pnfSeleccionado)) {
                    opt.selected = true;
                }
                selectPnfElement.appendChild(opt);
            });
        }
    })
    .catch(error => console.error("Error al cargar PNFs:", error));
}

const selectNucleoReg = document.getElementById('nucleo_id');
const selectPnfReg = document.getElementById('pnf_id');
if (selectNucleoReg && selectPnfReg) {
    selectNucleoReg.addEventListener('change', function() {
        cargarPnfsPorNucleo(this.value, selectPnfReg);
    });
}

const selectNucleoEdit = document.getElementById('edit_nucleo');
const selectPnfEdit = document.getElementById('edit_pnf');
if (selectNucleoEdit && selectPnfEdit) {
    selectNucleoEdit.addEventListener('change', function() {
        cargarPnfsPorNucleo(this.value, selectPnfEdit);
    });
}