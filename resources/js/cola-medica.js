// Cola Médica en Tiempo Real
let colaMedicaActiva = false;

// Función para reproducir sonido de notificación
function reproducirSonidoNotificacion() {
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        
        // Crear una secuencia de tres tonos más largos y fuertes
        const duracion = 0.25; // duración más larga
        const frecuencia1 = 800; // Hz
        const frecuencia2 = 1000; // Hz
        const frecuencia3 = 1200; // Hz más agudo para llamar atención
        
        // Primer tono
        const oscillator1 = audioContext.createOscillator();
        const gainNode1 = audioContext.createGain();
        oscillator1.connect(gainNode1);
        gainNode1.connect(audioContext.destination);
        oscillator1.frequency.value = frecuencia1;
        oscillator1.type = 'square'; // Tipo square es más penetrante
        gainNode1.gain.setValueAtTime(1.0, audioContext.currentTime);
        gainNode1.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + duracion);
        oscillator1.start(audioContext.currentTime);
        oscillator1.stop(audioContext.currentTime + duracion);
        
        // Segundo tono
        const oscillator2 = audioContext.createOscillator();
        const gainNode2 = audioContext.createGain();
        oscillator2.connect(gainNode2);
        gainNode2.connect(audioContext.destination);
        oscillator2.frequency.value = frecuencia2;
        oscillator2.type = 'square';
        gainNode2.gain.setValueAtTime(1.0, audioContext.currentTime + duracion + 0.05);
        gainNode2.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + duracion * 2 + 0.05);
        oscillator2.start(audioContext.currentTime + duracion + 0.05);
        oscillator2.stop(audioContext.currentTime + duracion * 2 + 0.05);
        
        // Tercer tono (más agudo para llamar la atención)
        const oscillator3 = audioContext.createOscillator();
        const gainNode3 = audioContext.createGain();
        oscillator3.connect(gainNode3);
        gainNode3.connect(audioContext.destination);
        oscillator3.frequency.value = frecuencia3;
        oscillator3.type = 'square';
        gainNode3.gain.setValueAtTime(1.0, audioContext.currentTime + duracion * 2 + 0.1);
        gainNode3.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + duracion * 3 + 0.1);
        oscillator3.start(audioContext.currentTime + duracion * 2 + 0.1);
        oscillator3.stop(audioContext.currentTime + duracion * 3 + 0.1);
    } catch (error) {
        console.error('Error al reproducir sonido:', error);
    }
}

// Exponer globalmente
window.reproducirSonidoNotificacion = reproducirSonidoNotificacion;

// Función para cargar la cola médica
function cargarColaMedica() {
    fetch('/cola-medica', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            actualizarColaMedica(data);
        })
        .catch(error => console.error('Error cargando cola médica:', error));
}

// Exponer globalmente para uso en onclick
window.cargarColaMedica = cargarColaMedica;

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
        
        // Agregar botón "Terminar Todo" al final
        const liBoton = document.createElement('li');
        liBoton.innerHTML = `
            <div class="dropdown-item text-center" style="border-top: 2px solid #dee2e6;">
                <button class="btn btn-danger btn-sm w-100" onclick="terminarTodasAtenciones()">
                    <i class="fas fa-check-double me-2"></i>Terminar Todo
                </button>
            </div>
        `;
        dropdown.appendChild(liBoton);
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
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw { message: data.message, isServerError: true };
            }
            return data;
        } else {
            const text = await response.text();
            console.error('Server response is not JSON:', text);
            throw new Error('El servidor devolvió una respuesta no válida');
        }
    })
    .then(data => {
        // Éxito: actualizar UI sin mostrar mensaje
        cargarColaMedica();
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.isServerError ? error.message : (error.message || 'Ocurrió un error al agregar a la cola médica')
        });
    });
}

// Exponer funciones globalmente para uso en onclick
window.agregarAColaMedica = agregarAColaMedica;

// Terminar atención
function terminarAtencion(idMascota) {
    fetch(`/cola-medica/terminar/${idMascota}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(async response => {
        const data = await response.json();
        if (!response.ok || !data.success) {
            throw { message: data.message, isServerError: true };
        }
        return data;
    })
    .then(data => {
        // Éxito: actualizar UI sin mostrar mensaje
        cargarColaMedica();
        
        // Ocultar el botón si estamos en la historia clínica
        const btnTerminar = document.getElementById('btnTerminarAtencion');
        if (btnTerminar) {
            btnTerminar.style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.isServerError ? error.message : 'Ocurrió un error al terminar la atención'
        });
    });
}

// Exponer funciones globalmente para uso en onclick
window.terminarAtencion = terminarAtencion;

// Terminar todas las atenciones
function terminarTodasAtenciones() {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Se terminarán todas las atenciones de la cola médica',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, terminar todo',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/cola-medica/terminar-todo', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw { message: data.message, isServerError: true };
                }
                return data;
            })
            .then(data => {
                cargarColaMedica();
                Swal.fire({
                    icon: 'success',
                    title: 'Completado',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                });
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.isServerError ? error.message : 'Ocurrió un error al terminar las atenciones'
                });
            });
        }
    });
}

// Exponer funciones globalmente para uso en onclick
window.terminarTodasAtenciones = terminarTodasAtenciones;

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    cargarColaMedica();
});

// Configurar Laravel Echo para tiempo real
if (typeof Echo !== 'undefined') {
    console.log('Conectando a canal cola-medica...');
    Echo.channel('cola-medica')
        .listen('.cola.actualizada', (event) => {
            console.log('Evento recibido: cola actualizada');
            
            // Reproducir sonido de notificación
            reproducirSonidoNotificacion();
            
            // Actualizar la cola médica
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

