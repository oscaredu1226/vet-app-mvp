// --- 1. LÓGICA DE FILAS MÉDICOS ---
function agregarFilaMedico(tableId, isCirujano) {
    const tbody = document.querySelector(`#${tableId} tbody`);
    const rowCount = tbody.rows.length;
    const row = tbody.insertRow();
    
    // Nombre - Solo letras y espacios
    const cell1 = row.insertCell(0);
    cell1.innerHTML = `<input type="text" class="form-control form-control-sm nombre-medico" required placeholder="Nombre completo" pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\\s]+" title="Solo se permiten letras y espacios">`;
    
    // Cargo - Solo letras y espacios
    const cell2 = row.insertCell(1);
    cell2.innerHTML = `<input type="text" class="form-control form-control-sm cargo-medico" required placeholder="${isCirujano ? 'Cirujano' : 'Anestesista'}" pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\\s]+" title="Solo se permiten letras y espacios">`;
    
    // CMV - Solo números, máximo 7 dígitos
    const cell3 = row.insertCell(2);
    cell3.innerHTML = `<input type="text" class="form-control form-control-sm cmv-medico" placeholder="CMV (Opcional)" pattern="\\d{1,7}" maxlength="7" title="Solo se permiten números (máximo 7 dígitos)">`;
    
    // Botón Eliminar
    const cell4 = row.insertCell(3);
    if (rowCount > 0) {
        cell4.innerHTML = `<button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="this.closest('tr').remove()"><i class="fas fa-trash-alt"></i></button>`;
    }
}

// --- 2. LÓGICA DE TRATAMIENTOS INTRAOPERATORIOS ---
function agregarTratamiento() {
    const input = document.getElementById('input_medicamento');
    const nombre = input.value.trim();
    
    if (!nombre) {
        Swal.fire('Atención', 'Ingrese el nombre del medicamento', 'warning');
        return;
    }
    
    const tbody = document.querySelector('#tablaTratamientos tbody');
    const row = tbody.insertRow();
    
    row.innerHTML = `
        <td>
            <input type="text" class="form-control form-control-sm tratamiento-nombre" value="${nombre}" readonly>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm tratamiento-dosis" required placeholder="Ej: 50mg">
        </td>
        <td>
            <select class="form-select form-select-sm tratamiento-via">
                <option value="">Seleccione (Opcional)</option>
                <option value="IV (Intravenosa)">IV (Intravenosa)</option>
                <option value="IM (Intramuscular)">IM (Intramuscular)</option>
                <option value="SC (Subcutánea)">SC (Subcutánea)</option>
                <option value="ID (Intradérmica)">ID (Intradérmica)</option>
                <option value="IO (Intraósea)">IO (Intraósea)</option>
                <option value="Epidural">Epidural</option>
                <option value="Intratecal">Intratecal</option>
            </select>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm tratamiento-volumen" placeholder="Ej: 2ml">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="this.closest('tr').remove()"><i class="fas fa-trash-alt"></i></button>
        </td>
    `;
    
    input.value = '';
    input.focus();
}

// --- 3. CARGAR DATOS EXISTENTES (MODO EDICIÓN) ---
function cargarDatosExistentes() {
    // Cargar Cirujanos
    const cirujanos_data = document.getElementById('cirujanos_data').value;
    if (cirujanos_data) {
        try {
            const cirujanos = JSON.parse(cirujanos_data);
            if (cirujanos && cirujanos.length > 0) {
                cirujanos.forEach((cirujano, index) => {
                    agregarFilaMedico('tablaCirujanos', true);
                    const tbody = document.querySelector('#tablaCirujanos tbody');
                    const row = tbody.rows[index];
                    if (row) {
                        row.querySelector('.nombre-medico').value = cirujano.nombre || '';
                        row.querySelector('.cargo-medico').value = cirujano.cargo || '';
                        row.querySelector('.cmv-medico').value = cirujano.cmv || '';
                    }
                });
            } else {
                agregarFilaMedico('tablaCirujanos', true);
            }
        } catch(e) {
            agregarFilaMedico('tablaCirujanos', true);
        }
    } else {
        agregarFilaMedico('tablaCirujanos', true);
    }
    
    // Cargar Anestesistas
    const anestesistas_data = document.getElementById('anestesistas_data').value;
    if (anestesistas_data) {
        try {
            const anestesistas = JSON.parse(anestesistas_data);
            if (anestesistas && anestesistas.length > 0) {
                anestesistas.forEach((anestesista, index) => {
                    agregarFilaMedico('tablaAnestesistas', false);
                    const tbody = document.querySelector('#tablaAnestesistas tbody');
                    const row = tbody.rows[index];
                    if (row) {
                        row.querySelector('.nombre-medico').value = anestesista.nombre || '';
                        row.querySelector('.cargo-medico').value = anestesista.cargo || '';
                        row.querySelector('.cmv-medico').value = anestesista.cmv || '';
                    }
                });
            } else {
                agregarFilaMedico('tablaAnestesistas', false);
            }
        } catch(e) {
            agregarFilaMedico('tablaAnestesistas', false);
        }
    } else {
        agregarFilaMedico('tablaAnestesistas', false);
    }
    
    // Cargar Tratamientos
    const tratamiento_aplicado = document.getElementById('tratamiento_aplicado').value;
    if (tratamiento_aplicado) {
        const lineas = tratamiento_aplicado.split('\n');
        lineas.forEach(linea => {
            // Formato esperado: "Medicamento: XXX, Dosis: YYY, Vía: ZZZ, Volumen: WWW"
            // Hacemos Vía y Volumen opcionales para compatibilidad con registros antiguos
            const match = linea.match(/Medicamento: (.+?), Dosis: (.+?)(?:, Vía: (.+?))?(?:, Volumen: (.+))?$/);
            if (match) {
                const tbody = document.querySelector('#tablaTratamientos tbody');
                const row = tbody.insertRow();
                const via = match[3] ? match[3].trim() : 'IV (Intravenosa)';
                const volumen = match[4] ? match[4].trim() : '';
                row.innerHTML = `
                    <td><input type="text" class="form-control form-control-sm tratamiento-nombre" value="${match[1]}" readonly></td>
                    <td><input type="text" class="form-control form-control-sm tratamiento-dosis" value="${match[2]}" required></td>
                    <td>
                        <select class="form-select form-select-sm tratamiento-via">
                            <option value="">Seleccione (Opcional)</option>
                            <option value="IV (Intravenosa)" ${via === 'IV (Intravenosa)' ? 'selected' : ''}>IV (Intravenosa)</option>
                            <option value="IM (Intramuscular)" ${via === 'IM (Intramuscular)' ? 'selected' : ''}>IM (Intramuscular)</option>
                            <option value="SC (Subcutánea)" ${via === 'SC (Subcutánea)' ? 'selected' : ''}>SC (Subcutánea)</option>
                            <option value="ID (Intradérmica)" ${via === 'ID (Intradérmica)' ? 'selected' : ''}>ID (Intradérmica)</option>
                            <option value="IO (Intraósea)" ${via === 'IO (Intraósea)' ? 'selected' : ''}>IO (Intraósea)</option>
                            <option value="Epidural" ${via === 'Epidural' ? 'selected' : ''}>Epidural</option>
                            <option value="Intratecal" ${via === 'Intratecal' ? 'selected' : ''}>Intratecal</option>
                        </select>
                    </td>
                    <td><input type="text" class="form-control form-control-sm tratamiento-volumen" value="${volumen}"></td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="this.closest('tr').remove()"><i class="fas fa-trash-alt"></i></button></td>
                `;
            }
        });
    }
}

// --- 4. INICIALIZACIÓN Y EVENTOS ---
document.addEventListener('DOMContentLoaded', function() {
    // Siempre cargar datos (si existen) o crear filas vacías
    cargarDatosExistentes();
    
    // Permitir agregar tratamiento con Enter
    const inputMedicamento = document.getElementById('input_medicamento');
    if (inputMedicamento) {
        inputMedicamento.addEventListener('keydown', function(e) {
            if(e.key === 'Enter') {
                e.preventDefault();
                agregarTratamiento();
            }
        });
    }
    
    // Envío del formulario
    const form = document.getElementById('formCirugia');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Procesar Médicos Cirujanos
            const cirujanos = [];
            document.querySelectorAll('#tablaCirujanos tbody tr').forEach(tr => {
                cirujanos.push({
                    nombre: tr.querySelector('.nombre-medico').value,
                    cargo: tr.querySelector('.cargo-medico').value,
                    cmv: tr.querySelector('.cmv-medico').value
                });
            });
            document.getElementById('cirujanos_data').value = JSON.stringify(cirujanos);

            // Procesar Médicos Anestesistas
            const anestesistas = [];
            document.querySelectorAll('#tablaAnestesistas tbody tr').forEach(tr => {
                anestesistas.push({
                    nombre: tr.querySelector('.nombre-medico').value,
                    cargo: tr.querySelector('.cargo-medico').value,
                    cmv: tr.querySelector('.cmv-medico').value
                });
            });
            document.getElementById('anestesistas_data').value = JSON.stringify(anestesistas);

            // Procesar Tratamientos
            let tratamientosStr = [];
            const tratamientoRows = document.querySelectorAll('#tablaTratamientos tbody tr');
            
            // Validación: Debe haber al menos un tratamiento
            if(tratamientoRows.length === 0) {
                Swal.fire('Atención', 'El tratamiento intraoperatorio es obligatorio. Agregue al menos un medicamento.', 'warning');
                return;
            }

            tratamientoRows.forEach(tr => {
                const med = tr.querySelector('.tratamiento-nombre').value;
                const dosis = tr.querySelector('.tratamiento-dosis').value;
                const via = tr.querySelector('.tratamiento-via').value;
                const vol = tr.querySelector('.tratamiento-volumen').value;
                tratamientosStr.push(`Medicamento: ${med}, Dosis: ${dosis}, Vía: ${via}, Volumen: ${vol}`);
            });
            document.getElementById('tratamiento_aplicado').value = tratamientosStr.join('\n');

            // Enviar formulario
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: data.message,
                        icon: 'success',
                        timer: 1000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = data.redirect;
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Error inesperado', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }

    // --- AUTOCOMPLETE PARA TIPO DE CIRUGÍA ---
    const tipoCirugiaSearch = document.getElementById('tipo_cirugia_search');
    const tipoCirugiaHidden = document.getElementById('tipo_cirugia');
    const tipoCirugiaResults = document.getElementById('tipo_cirugia_results');

    if (tipoCirugiaSearch) {
        tipoCirugiaSearch.addEventListener('input', function() {
            const query = this.value.trim();
            tipoCirugiaHidden.value = query; // Actualizar valor oculto mientras escribe
            
            if (query.length < 2) {
                tipoCirugiaResults.style.display = 'none';
                return;
            }

            fetch(`${BASE_URL}/buscar-tipos-cirugia?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(tipos => {
                    if (tipos.length > 0) {
                        tipoCirugiaResults.innerHTML = '';
                        tipos.forEach(tipo => {
                            const item = document.createElement('a');
                            item.href = '#';
                            item.className = 'list-group-item list-group-item-action';
                            item.textContent = tipo.nombre;
                            item.addEventListener('click', function(e) {
                                e.preventDefault();
                                tipoCirugiaSearch.value = tipo.nombre;
                                tipoCirugiaHidden.value = tipo.nombre;
                                tipoCirugiaResults.style.display = 'none';
                            });
                            tipoCirugiaResults.appendChild(item);
                        });
                        tipoCirugiaResults.style.display = 'block';
                    } else {
                        tipoCirugiaResults.innerHTML = '<div class="list-group-item text-muted">Presione Enter para agregar este tipo</div>';
                        tipoCirugiaResults.style.display = 'block';
                    }
                })
                .catch(() => {
                    tipoCirugiaResults.style.display = 'none';
                });
        });

        // Agregar nuevo tipo al presionar Enter
        tipoCirugiaSearch.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const valor = this.value.trim();
                if (valor.length >= 2) {
                    tipoCirugiaHidden.value = valor;
                    tipoCirugiaResults.style.display = 'none';
                }
            }
        });

        // Cerrar resultados al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!tipoCirugiaSearch.contains(e.target) && !tipoCirugiaResults.contains(e.target)) {
                tipoCirugiaResults.style.display = 'none';
            }
        });
    }
});