{{-- Botón para abrir modal de archivos --}}
<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalArchivos{{ $tipoHistoria }}{{ $historiaId ?? 'temp' }}">
    <i class="fas fa-paperclip me-1"></i>
    Archivos
    <span id="contador-archivos-{{ $tipoHistoria }}-{{ $historiaId ?? 'temp' }}" class="badge bg-primary ms-1 d-none">0</span>
</button>

{{-- Modal para gestionar archivos --}}
<div class="modal fade" id="modalArchivos{{ $tipoHistoria }}{{ $historiaId ?? 'temp' }}" tabindex="-1" aria-labelledby="modalArchivos{{ $tipoHistoria }}{{ $historiaId ?? 'temp' }}Label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalArchivos{{ $tipoHistoria }}{{ $historiaId ?? 'temp' }}Label">
                    <i class="fas fa-paperclip me-2"></i>Archivos Adjuntos
                    <small class="text-muted">(Imágenes, PDFs, Videos - Max. 50MB)</small>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Zona de subida --}}
                <div class="mb-4">
                    <div class="upload-area border-2 border-dashed rounded p-4 text-center" 
                         style="border-color: #dee2e6; transition: all 0.3s;"
                         ondragover="handleDragOver(event)" 
                         ondrop="handleDrop(event, '{{ $tipoHistoria }}', {{ $historiaId ?? 'null' }})"
                         ondragleave="handleDragLeave(event)">
                        
                        <input type="file" 
                               id="archivo-input-{{ $tipoHistoria }}-{{ $historiaId ?? 'temp' }}" 
                               class="d-none" 
                               accept=".jpg,.jpeg,.png,.pdf,.mp4,.mov,.avi"
                               onchange="subirArchivo(this, '{{ $tipoHistoria }}', {{ $historiaId ?? 'null' }})">
                        
                        <div class="upload-content">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                            <p class="mb-2">Arrastra archivos aquí o <a href="#" onclick="document.getElementById('archivo-input-{{ $tipoHistoria }}-{{ $historiaId ?? "temp" }}').click(); return false;" class="text-primary">selecciona archivos</a></p>
                            <small class="text-muted">JPG, PNG, PDF, MP4, MOV, AVI (máx. 50MB)</small>
                        </div>
                        
                        <div class="upload-loading d-none">
                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                            <span>Subiendo archivo...</span>
                        </div>
                    </div>
                </div>

                {{-- Lista de archivos --}}
                <div id="lista-archivos-{{ $tipoHistoria }}" class="archivos-container">
                    {{-- Los archivos se cargan dinámicamente vía JavaScript --}}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- Estilos CSS --}}
<style>
.upload-area:hover {
    border-color: #0d6efd !important;
    background-color: #f8f9fa;
}

.upload-area.drag-over {
    border-color: #0d6efd !important;
    background-color: #e3f2fd;
}

.archivo-item {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 8px;
    transition: all 0.3s;
}

.archivo-item:hover {
    background-color: #f8f9fa;
    border-color: #0d6efd;
}

.archivo-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.archivo-icon-small {
    width: 30px;
    height: 30px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
}

.tipo-imagen { background-color: #e3f2fd; color: #1976d2; }
.tipo-documento { background-color: #ffebee; color: #d32f2f; }
.tipo-video { background-color: #e8f5e8; color: #388e3c; }
</style>

{{-- JavaScript para manejo de archivos --}}
<script>
// Solo cargar las funciones una vez para evitar redeclaraciones
if (typeof window.archivoUploadLoaded === 'undefined') {
    window.archivoUploadLoaded = true;

// Drag and Drop
function handleDragOver(e) {
    e.preventDefault();
    e.currentTarget.classList.add('drag-over');
}

function handleDragLeave(e) {
    e.preventDefault();
    e.currentTarget.classList.remove('drag-over');
}

function handleDrop(e, tipoHistoria, historiaId) {
    e.preventDefault();
    e.currentTarget.classList.remove('drag-over');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        const inputId = `archivo-input-${tipoHistoria}-${historiaId || 'temp'}`;
        const input = document.getElementById(inputId);
        if (input) {
            // Crear un nuevo FileList con el archivo arrastrado
            const dt = new DataTransfer();
            dt.items.add(files[0]);
            input.files = dt.files;
            subirArchivo(input, tipoHistoria, historiaId);
        }
    }
}

// Subir archivo
function subirArchivo(input, tipoHistoria, historiaId) {
    const file = input.files[0];
    if (!file) return;

    console.log('Subiendo archivo:', file.name, 'Tipo Historia:', tipoHistoria, 'Historia ID:', historiaId);

    // Verificar tamaño (50MB = 52428800 bytes)
    if (file.size > 52428800) {
        Swal.fire('Error', 'El archivo supera el límite de 50MB', 'error');
        input.value = '';
        return;
    }

    const formData = new FormData();
    formData.append('archivo', file);
    formData.append('historia_type', tipoHistoria);
    
    // Solo agregar historia_id si existe (no es null o undefined)
    if (historiaId) {
        formData.append('historia_id', historiaId);
    }

    // Obtener token CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    console.log('CSRF Token element:', csrfToken);
    let tokenValue = null;
    if (csrfToken) {
        tokenValue = csrfToken.getAttribute('content');
        console.log('CSRF Token value:', tokenValue);
    } else {
        console.error('No se encontró el token CSRF');
        console.log('Available meta tags:', document.querySelectorAll('meta'));
        Swal.fire('Error', 'Error de configuración. Token CSRF no encontrado.', 'error');
        return;
    }

    // Mostrar loading
    const uploadArea = input.closest('.upload-area');
    const uploadContent = uploadArea.querySelector('.upload-content');
    const uploadLoading = uploadArea.querySelector('.upload-loading');
    
    uploadContent.classList.add('d-none');
    uploadLoading.classList.remove('d-none');

    console.log('Enviando archivo al servidor...');

    fetch('/historias/archivos', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': tokenValue,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Respuesta del servidor:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Datos recibidos:', data);
        if (data.success) {
            Swal.fire('¡Éxito!', data.message, 'success');
            cargarArchivos(tipoHistoria, historiaId);
            // También actualizar en el historial si existe
            if (typeof cargarArchivosHistorial === 'function') {
                cargarArchivosHistorial(tipoHistoria, historiaId);
            }
        } else {
            Swal.fire('Error', data.message || 'Error desconocido', 'error');
        }
    })
    .catch(error => {
        console.error('Error en fetch:', error);
        Swal.fire('Error', 'Error de conexión al servidor', 'error');
    })
    .finally(() => {
        // Ocultar loading
        uploadContent.classList.remove('d-none');
        uploadLoading.classList.add('d-none');
        input.value = '';
    });
}

// Cargar archivos existentes
function cargarArchivos(tipoHistoria, historiaId) {
    const params = new URLSearchParams({
        historia_type: tipoHistoria
    });
    
    // Solo agregar historia_id si existe (buscar archivos de historia específica vs temporales)
    if (historiaId) {
        params.append('historia_id', historiaId);
    }

    fetch(`/historias/archivos?${params}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarArchivos(data.archivos, tipoHistoria, historiaId);
            actualizarContadorArchivos(data.archivos.length, tipoHistoria, historiaId);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Mostrar lista de archivos
function mostrarArchivos(archivos, tipoHistoria, historiaId = null) {
    // Intentar encontrar el contenedor de archivos (puede estar en modal o en formulario)
    const modalId = `modalArchivos${tipoHistoria}${historiaId || 'temp'}`;
    const modal = document.getElementById(modalId);
    
    let container = null;
    if (modal) {
        // Buscar contenedor dentro del modal
        container = modal.querySelector('.archivos-container, #lista-archivos');
        if (!container) {
            // Crear contenedor en el modal si no existe
            const modalBody = modal.querySelector('.modal-body');
            if (modalBody) {
                const existingContainer = modalBody.querySelector('.archivos-container');
                if (!existingContainer) {
                    const newContainer = document.createElement('div');
                    newContainer.className = 'archivos-container mt-3';
                    newContainer.innerHTML = '<h6 class="mb-3">Archivos Subidos:</h6><div class="archivos-list"></div>';
                    modalBody.appendChild(newContainer);
                }
                container = modalBody.querySelector('.archivos-list');
            }
        }
    }
    
    // Si no se encontró en modal, buscar en el historial
    if (!container && historiaId) {
        container = document.getElementById(`archivos-list-${tipoHistoria}-${historiaId}`);
    }
    
    if (!container) {
        console.warn(`No se encontró contenedor de archivos para ${tipoHistoria} ${historiaId || 'temp'}`);
        return;
    }
    
    // Actualizar contador
    const contadorId = `contador-archivos-${tipoHistoria}-${historiaId || 'temp'}`;
    const contador = document.getElementById(contadorId);
    if (contador) {
        if (archivos.length > 0) {
            contador.textContent = archivos.length;
            contador.classList.remove('d-none');
        } else {
            contador.classList.add('d-none');
        }
    }
    
    // Mostrar archivos
    if (archivos.length === 0) {
        container.innerHTML = '<p class="text-muted mb-0">No hay archivos adjuntos.</p>';
        return;
    }

    const html = archivos.map(archivo => `
        <div class="archivo-item d-flex align-items-center">
            <div class="archivo-icon me-3 ${getTipoClaseArchivo(archivo.tipo)}">
                <i class="fas ${getIconoArchivo(archivo.tipo)}"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-1">${archivo.nombre}</h6>
                <small class="text-muted">
                    ${archivo.tipo.charAt(0).toUpperCase() + archivo.tipo.slice(1)} • 
                    ${archivo.tamaño}
                </small>
            </div>
            <div class="archivo-actions">
                <a href="${archivo.url}" target="_blank" class="btn btn-sm btn-outline-primary me-2" title="Ver archivo">
                    <i class="fas fa-eye"></i>
                </a>
                <button class="btn btn-sm btn-outline-danger" 
                        onclick="eliminarArchivo(${archivo.id}, '${tipoHistoria}', ${historiaId || 'null'})" 
                        title="Eliminar archivo">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `).join('');

    container.innerHTML = html;
}

// Funciones auxiliares para archivos
function getIconoArchivo(tipo) {
    switch (tipo) {
        case 'imagen': return 'fa-image';
        case 'documento': return 'fa-file-pdf';
        case 'video': return 'fa-video';
        default: return 'fa-file';
    }
}

function getTipoClaseArchivo(tipo) {
    switch (tipo) {
        case 'imagen': return 'tipo-imagen';
        case 'documento': return 'tipo-documento';
        case 'video': return 'tipo-video';
        default: return 'tipo-documento';
    }
}

// Eliminar archivo
function eliminarArchivo(archivoId, tipoHistoria, historiaId) {
    Swal.fire({
        title: '¿Eliminar archivo?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Obtener token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            const headers = {
                'Content-Type': 'application/json'
            };
            
            if (csrfToken) {
                headers['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
            }

            fetch(`/historias/archivos/${archivoId}`, {
                method: 'DELETE',
                headers: headers
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('¡Eliminado!', data.message, 'success');
                    cargarArchivos(tipoHistoria, historiaId);
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Error al eliminar el archivo', 'error');
            });
        }
    });
}

// Utilidades para iconos
function getIconClass(tipo) {
    switch (tipo) {
        case 'imagen': return 'fas fa-image';
        case 'documento': return 'fas fa-file-pdf';
        case 'video': return 'fas fa-video';
        default: return 'fas fa-file';
    }
}

function getIconBackground(tipo) {
    switch (tipo) {
        case 'imagen': return 'tipo-imagen';
        case 'documento': return 'tipo-documento';
        case 'video': return 'tipo-video';
        default: return 'tipo-documento';
    }
}

// Actualizar contador de archivos en el botón
function actualizarContadorArchivos(cantidad, tipoHistoria, historiaId) {
    const badgeId = `contador-archivos-${tipoHistoria}-${historiaId || 'temp'}`;
    const badge = document.querySelector('#' + badgeId);
    if (badge) {
        if (cantidad > 0) {
            badge.textContent = cantidad;
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    }
}

} // Fin del bloque archivoUploadLoaded

// Inicialización específica para este componente - siempre ejecutar
(function() {
    const componentTipo = '{{ $tipoHistoria }}';
    const componentHistoriaId = {{ $historiaId ?? 'null' }};
    
    // Solo cargar archivos si estamos en modo edición (no en historial)
    if (componentHistoriaId && typeof cargarArchivos === 'function') {
        document.addEventListener('DOMContentLoaded', function() {
            cargarArchivos(componentTipo, componentHistoriaId);
        });
    }
})();
</script>