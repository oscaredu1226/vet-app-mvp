// Cola Médica en Tiempo Real
let colaMedicaActiva = false;

// Función para cargar la cola médica
function cargarColaMedica() {
    fetch('/cola-medica')
        .then(response => response.json())
        .then(data => {
            actualizarColaMedica(data);
        })
        .catch(error => console.error('Error cargando cola médica:', error));
}

// Actualizar UI de la cola médica
function actualizarColaMedica(cola) {
    const badge = document.getElementById('colaMedicaBadge');
    const dropdown = document.getElementById('colaMedicaDropdown');
    
    if (!badge || !dropdown) return;
    
    // Actualizar badge con la cantidad
    badge.textContent = cola.length;
    badge.style.display = cola.length > 0 ? 'block' : 'none';
    
    // Limpiar y actualizar el dropdown
    dropdown.innerHTML = '';
    
    if (cola.length === 0) {
        dropdown.innerHTML = `
            <li class="dropdown-item text-muted text-center py-3">
                <i class="fas fa-info-circle me-2"></i>No hay mascotas en cola
            </li>
        `;
    } else {
        cola.forEach(item => {
            const mascota = item.mascota;
            const cliente = mascota.cliente;
            const tiempoEspera = calcularTiempoEspera(item.fecha_ingreso);
            
            const li = document.createElement('li');
            li.innerHTML = `
                <a class="dropdown-item cola-item" href="/mascotas/${mascota.id_mascota}/historia" style="border-bottom: 1px solid #eee;">
                    <div class="d-flex align-items-center py-2">
                        <div class="flex-shrink-0">
                            <i class="fas fa-paw text-primary fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-bold">${mascota.nombre}</div>
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>${cliente.nombre} ${cliente.apellido}
                            </small>
                            <br>
                            <small class="text-info">
                                <i class="fas fa-clock me-1"></i>${tiempoEspera}
                            </small>
                        </div>
                    </div>
                </a>
            `;
            dropdown.appendChild(li);
        });
    }
}

// Calcular tiempo de espera
function calcularTiempoEspera(fechaIngreso) {
    const ahora = new Date();
    const ingreso = new Date(fechaIngreso);
    const diff = Math.floor((ahora - ingreso) / 1000 / 60); // minutos
    
    if (diff < 1) return 'Recién ingresado';
    if (diff < 60) return `${diff} min`;
    
    const horas = Math.floor(diff / 60);
    const minutos = diff % 60;
    return `${horas}h ${minutos}min`;
}

// Agregar mascota a la cola
function agregarAColaMedica(idMascota, motivo = '') {
    fetch('/cola-medica/agregar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            id_mascota: idMascota,
            motivo: motivo
        })
    })
    .then(async response => {
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.includes("application/json")) {
            return response.json();
        } else {
            const text = await response.text();
            console.error('Server response is not JSON:', text);
            throw new Error('El servidor devolvió una respuesta no válida');
        }
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            });
            cargarColaMedica();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Ocurrió un error al agregar a la cola médica'
        });
    });
}

// Terminar atención
function terminarAtencion(idMascota) {
    fetch(`/cola-medica/terminar/${idMascota}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Atención terminada',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            });
            cargarColaMedica();
            
            // Ocultar el botón si estamos en la historia clínica
            const btnTerminar = document.getElementById('btnTerminarAtencion');
            if (btnTerminar) {
                btnTerminar.style.display = 'none';
            }
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ocurrió un error al terminar la atención'
        });
    });
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    cargarColaMedica();
});

// Configurar Laravel Echo si está disponible
if (typeof Echo !== 'undefined') {
    console.log('Conectando a canal cola-medica...');
    Echo.channel('cola-medica')
        .listen('.cola.actualizada', () => {
            console.log('Evento recibido: cola actualizada');
            cargarColaMedica();
        });
    
    // Verificar estado de conexión
    Echo.connector.pusher.connection.bind('connected', () => {
        console.log('✅ Conectado a Reverb WebSocket');
        cargarColaMedica(); // Recargar al conectar
    });
    
    Echo.connector.pusher.connection.bind('disconnected', () => {
        console.log('❌ Desconectado de Reverb WebSocket');
    });
    
    Echo.connector.pusher.connection.bind('error', (err) => {
        console.error('Error en WebSocket:', err);
    });
} else {
    console.error('⚠️ Laravel Echo no está disponible. Asegúrate de que Reverb esté corriendo.');
}
