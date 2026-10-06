<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>VetApp</title>

    <!-- Bootstrap 5 (Cargado vía npm) -->
    <!-- Font Awesome (Cargado vía CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
    <style>
        /* Ajuste para que el calendario no se vea plano */
        .flatpickr-calendar {
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        /* Hacemos que el input readonly parezca normal */
        .form-control.flatpickr-input[readonly] {
            background-color: #fff;
        }
        /* Dropdown de Grooming con fondo negro */
        #groomingDropdown + .dropdown-menu {
            background-color: #000 !important;
            border: 1px solid #333;
        }
        #groomingDropdown + .dropdown-menu .dropdown-item {
            color: #fff !important;
        }
        #groomingDropdown + .dropdown-menu .dropdown-item:hover {
            background-color: #333 !important;
            color: #fff !important;
        }
        #groomingDropdown + .dropdown-menu .dropdown-item.active {
            background-color: #444 !important;
            color: #fff !important;
        }
    </style>
    
    <!-- === LA CORRECCIÓN MÁGICA === -->
    <!-- Esto le dice a Laravel que cargue los CSS y JS desde el servidor VITE (npm run dev) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Espacio para estilos de páginas específicas -->
    @stack('styles')
</head>
<body>
    <!-- Barra Superior (Top Navbar) -->
    <nav class="navbar navbar-expand-lg fixed-top top-navbar">
        <div class="container-fluid">
            <!-- Botón para toggle sidebar en móviles/tablets -->
            <button class="btn btn-outline-light sidebar-toggle me-3" type="button" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Brand/Logo en barra superior -->
            <span class="navbar-brand mb-0 h1 text-white ms-5 d-none d-md-block">
                <i class="fas fa-heartbeat me-2"></i>
                VetApp
            </span>
            
            <!-- Elementos del lado derecho -->
            <div class="d-flex align-items-center me-3">
                @auth {{-- Mostrar solo si el usuario ha iniciado sesión --}}
{{-- MVP_POSTERIOR: BEGIN Cola medica y notificaciones
                    <!-- Cola Médica -->
                    <div class="dropdown me-2">
                        <button class="btn btn-outline-light position-relative" type="button" id="colaMedicaDropdownBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Cola Médica">
                            <i class="fas fa-stethoscope"></i>
                            <span class="badge bg-success position-absolute top-0 start-100 translate-middle rounded-pill" id="colaMedicaBadge" style="display: none;">0</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="colaMedicaDropdownBtn" id="colaMedicaDropdown">
                            <li class="dropdown-item text-muted text-center py-3">
                                <i class="fas fa-spinner fa-spin me-2"></i>Cargando...
                            </li>
                        </ul>
                    </div>

                    <!-- Notificaciones (Contador de Eventos) -->
                    <a href="{{ route('eventos.index') }}" class="btn btn-outline-light me-2 position-relative" title="Calendario y Eventos">
                        <i class="fas fa-bell"></i>
                        <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill" id="contadorEventos">0</span>
                    </a>
                    

MVP_POSTERIOR: END Cola medica y notificaciones --}}
<a href="{{ route('eventos.index') }}" class="btn btn-outline-light me-2 position-relative" title="Citas pendientes de hoy">
                        <i class="fas fa-bell"></i>
                        <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill" id="contadorEventos">0</span>
                    </a>
                    <!-- Dropdown de Usuario -->
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i>
                            <span class="d-none d-sm-inline">{{ Auth::user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <!-- Formulario de Logout -->
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); this.closest('form').submit();">
                                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                                    </a>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endauth
                
                @guest {{-- Mostrar si el usuario NO ha iniciado sesión --}}
                    <a href="{{ route('login') }}" class="btn btn-outline-light me-2">Login</a>
                @endguest
            </div>
        </div>
    </nav>

    <!-- Overlay para sidebar en móviles -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    @auth {{-- Mostrar el Sidebar solo si el usuario ha iniciado sesión --}}
    <!-- Sidebar (Barra Lateral) -->
    <div class="sidebar" id="sidebar">
        <!-- Header del sidebar (Logo) -->
        <div class="sidebar-header">
            <div class="text-center mb-3">
{{-- MVP_POSTERIOR: BEGIN Edicion del logo
                <div class="logo-container position-relative d-inline-block @if(auth()->user()->isAdmin()) logo-editable @endif" 
                     @if(auth()->user()->isAdmin()) 
                         style="cursor: pointer;" 
                         title="Clic para cambiar el logo"
                     @endif>

MVP_POSTERIOR: END Edicion del logo --}}
                <div class="logo-container position-relative d-inline-block">
                    @php
                        $logo_src = asset('images/usuario/user.svg'); // Ruta por defecto
                        try {
                            $logo_record = \Illuminate\Support\Facades\DB::table('logo')->where('id', 1)->first();
                            if ($logo_record && !empty($logo_record->imagen)) {
                                $logo_src = 'data:image/jpeg;base64,' . $logo_record->imagen;
                            }
                        } catch (\Exception $e) { /* Ignorar error si la tabla no existe */ }
                    @endphp
                    <img src="{{ $logo_src }}" alt="Logo" class="logo-circle" id="logoImage">
{{-- MVP_POSTERIOR: BEGIN Boton de edicion del logo
                    @if(auth()->user()->isAdmin())
                        <div class="logo-overlay">
                            <i class="fas fa-camera"></i>
                        </div>
                    @endif
MVP_POSTERIOR: END Boton de edicion del logo --}}
                </div>
            </div>
            <div class="brand-text">VetApp</div>
        </div>
        
        <!-- Menú de navegación (Rutas de Laravel) -->
        <nav class="nav flex-column" id="menu">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="fas fa-home"></i> <span>Inicio</span>
            </a>
            <a class="nav-link {{ request()->routeIs('eventos.*') ? 'active' : '' }}" href="{{ route('eventos.index') }}">
                <i class="fas fa-calendar-alt"></i> <span>Calendario y citas</span>
            </a>
            @if(auth()->user()->isAdmin())
            {{-- MVP_POSTERIOR: BEGIN Menu usuarios.index
<a class="nav-link {{ request()->routeIs('usuarios.index') ? 'active' : '' }}" href="{{ route('usuarios.index') }}">
                <i class="fas fa-user-shield"></i> <span>Usuarios</span>
            </a>
MVP_POSTERIOR: END Menu usuarios.index --}}
            @endif
            @if(auth()->user()->puede_acceder_ventas)
            {{-- MVP_POSTERIOR: BEGIN Menu ventas.index
<a class="nav-link {{ request()->routeIs('ventas.index') ? 'active' : '' }}" href="{{ route('ventas.index') }}">
                <i class="fas fa-shopping-cart"></i> <span>Ventas</span>
            </a>
MVP_POSTERIOR: END Menu ventas.index --}}
            @endif
            <a class="nav-link {{ request()->routeIs('clientes.index') ? 'active' : '' }}" href="{{ route('clientes.index') }}">
                <i class="fas fa-users"></i> <span>Clientes</span>
            </a>
            <a class="nav-link {{ (request()->routeIs('mascotas.index') || request()->routeIs('historia.show')) ? 'active' : '' }}" href="{{ route('mascotas.index') }}">
                <i class="fas fa-paw"></i> <span>Mascotas</span>
            </a>
            {{-- MVP_POSTERIOR: BEGIN Menu examenes.index
<a class="nav-link {{ request()->routeIs('examenes.index') ? 'active' : '' }}" href="{{ route('examenes.index') }}">
                <i class="fas fa-vial"></i> <span>Exámenes</span>
            </a>
MVP_POSTERIOR: END Menu examenes.index --}}
            {{-- MVP_POSTERIOR: BEGIN Menu productos.index
<a class="nav-link {{ request()->routeIs('productos.index') ? 'active' : '' }}" href="{{ route('productos.index') }}">
                <i class="fas fa-box-open"></i> <span>Productos</span>
            </a>
MVP_POSTERIOR: END Menu productos.index --}}
            {{-- MVP_POSTERIOR: BEGIN Menu servicios.index
<a class="nav-link {{ request()->routeIs('servicios.index') ? 'active' : '' }}" href="{{ route('servicios.index') }}">
                <i class="fas fa-tools"></i> <span>Servicios</span>
            </a>
MVP_POSTERIOR: END Menu servicios.index --}}
            
            {{-- Menú Grooming con submenu desplegable --}}
            {{-- MVP_POSTERIOR: BEGIN Menu grooming
<div class="nav-item">
                <a class="nav-link dropdown-toggle {{ request()->routeIs('grooming.*') ? 'active' : '' }}" 
                   href="#" 
                   id="groomingDropdown" 
                   role="button" 
                   data-bs-toggle="dropdown" 
                   aria-expanded="false">
                    <i class="fas fa-cut"></i> <span>Grooming</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="groomingDropdown">
                    <li>
                        <a class="dropdown-item {{ request()->routeIs('grooming.turnos-hoy') ? 'active' : '' }}" 
                           href="{{ route('grooming.turnos-hoy') }}">
                            <i class="fas fa-clock me-2"></i> Turnos de Hoy
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ request()->routeIs('grooming.programados') ? 'active' : '' }}" 
                           href="{{ route('grooming.programados') }}">
                            <i class="fas fa-calendar-alt me-2"></i> Programados
                        </a>
                    </li>
                </ul>
            </div>
MVP_POSTERIOR: END Menu grooming --}}
            
            @if(auth()->user()->puede_acceder_caja)
            {{-- MVP_POSTERIOR: BEGIN Menu caja.index
<a class="nav-link {{ request()->routeIs('caja.index') ? 'active' : '' }}" href="{{ route('caja.index') }}">
                <i class="fas fa-cash-register"></i> <span>Caja</span>
            </a>
MVP_POSTERIOR: END Menu caja.index --}}
            @endif
            @if(auth()->user()->puede_acceder_egresos)
            {{-- MVP_POSTERIOR: BEGIN Menu egresos.index
<a class="nav-link {{ request()->routeIs('egresos.index') ? 'active' : '' }}" href="{{ route('egresos.index') }}">
                <i class="fas fa-money-bill-wave"></i> <span>Egresos</span>
            </a>
MVP_POSTERIOR: END Menu egresos.index --}}
            @endif
            {{-- MVP_POSTERIOR: BEGIN Menu configuracion.index
<a class="nav-link {{ request()->routeIs('configuracion.index') ? 'active' : '' }}" href="{{ route('configuracion.index') }}">
                <i class="fas fa-cog"></i> <span>Configuración</span>
            </a>
MVP_POSTERIOR: END Menu configuracion.index --}}
            @if(auth()->user()->isAdmin())
            {{-- MVP_POSTERIOR: BEGIN Menu caja.reportes
<a class="nav-link {{ request()->routeIs('caja.reportes') ? 'active' : '' }}" href="{{ route('caja.reportes') }}">
                <i class="fas fa-file-excel"></i> <span>Reportes</span>
            </a>
MVP_POSTERIOR: END Menu caja.reportes --}}
            @endif
        </nav>
    </div>
    @endauth

    <!-- Contenido Principal -->
    <main class="main-content @guest py-5 @endguest">
        <div class="container-fluid @auth py-4 @endauth">
            @yield('content')
        </div>
    </main>

{{-- MVP_POSTERIOR: BEGIN Personalizacion del logo
    <!-- Modal para Cambiar Logo (Global) -->
    @auth
    @if(auth()->user()->isAdmin())
    <div class="modal fade" id="logoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="logoModalLabel">
                        <i class="fas fa-image me-2"></i>Cambiar Logo del Sistema
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="logoForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="logoFile" class="form-label fw-bold">
                                <i class="fas fa-upload me-2"></i>Seleccionar imagen
                            </label>
                            <input type="file" 
                                   class="form-control" 
                                   id="logoFile" 
                                   name="logo" 
                                   accept="image/jpeg,image/png,image/gif,image/webp" 
                                   required>
                            <small class="text-muted">
                                Formatos permitidos: JPG, PNG, GIF, WEBP. Tamaño máximo: 2MB
                            </small>
                        </div>
                        <div id="logoPreview" class="text-center d-none mt-3">
                            <p class="text-muted mb-2">Vista previa:</p>
                            <img src="" class="img-fluid rounded shadow" style="max-height: 200px; border: 3px solid #dee2e6;" alt="Vista previa">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" id="btnGuardarLogo">
                        <i class="fas fa-save me-2"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endauth


MVP_POSTERIOR: END Personalizacion del logo --}}    <!-- Scripts Globales -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Variables de Laravel para JavaScript -->
    <script>
        const BASE_URL = "{{ url('/') }}";
        const CSRF_TOKEN = "{{ csrf_token() }}";
        
{{-- MVP_POSTERIOR: BEGIN Script de personalizacion del logo
        @auth
        @if(auth()->user()->isAdmin())
        // Funcionalidad para cambiar el logo (solo administradores)
        document.addEventListener('DOMContentLoaded', function() {
            const logoContainer = document.querySelector('.logo-container.logo-editable');
            const logoModal = new bootstrap.Modal(document.getElementById('logoModal'));
            const logoForm = document.getElementById('logoForm');
            const logoFile = document.getElementById('logoFile');
            const logoPreview = document.getElementById('logoPreview');
            const btnGuardarLogo = document.getElementById('btnGuardarLogo');
            const logoImage = document.getElementById('logoImage');
            
            // Abrir modal al hacer clic en el logo
            if (logoContainer) {
                logoContainer.addEventListener('click', function() {
                    logoModal.show();
                    logoFile.value = ''; // Limpiar input
                    logoPreview.classList.add('d-none'); // Ocultar preview
                });
            }
            
            // Vista previa de la imagen seleccionada
            if (logoFile) {
                logoFile.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            logoPreview.querySelector('img').src = e.target.result;
                            logoPreview.classList.remove('d-none');
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            // Guardar el nuevo logo
            if (btnGuardarLogo) {
                btnGuardarLogo.addEventListener('click', function() {
                    const formData = new FormData(logoForm);
                    
                    if (!logoFile.files[0]) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Atención',
                            text: 'Por favor selecciona una imagen'
                        });
                        return;
                    }
                    
                    // Deshabilitar botón mientras se procesa
                    btnGuardarLogo.disabled = true;
                    btnGuardarLogo.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';
                    
                    fetch('{{ route('logo.update') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CSRF_TOKEN
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Actualizar la imagen del logo
                            logoImage.src = data.image_data;
                            
                            // Cerrar modal
                            logoModal.hide();
                            
                            // Mostrar mensaje de éxito
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
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
                            text: 'Ocurrió un error al actualizar el logo'
                        });
                    })
                    .finally(() => {
                        // Rehabilitar botón
                        btnGuardarLogo.disabled = false;
                        btnGuardarLogo.innerHTML = 'Guardar';
                    });
                });
            }
        });
        @endif
        @endauth
        

MVP_POSTERIOR: END Script de personalizacion del logo --}}        // Manejo del sidebar responsive
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            // Función para cerrar el sidebar
            function closeSidebar() {
                if (sidebar) {
                    sidebar.classList.remove('active');
                }
                if (sidebarOverlay) {
                    sidebarOverlay.classList.remove('active');
                }
            }
            
            // Función para abrir el sidebar
            function openSidebar() {
                if (sidebar) {
                    sidebar.classList.add('active');
                }
                if (sidebarOverlay) {
                    sidebarOverlay.classList.add('active');
                }
            }
            
            // Toggle del sidebar
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (sidebar && sidebar.classList.contains('active')) {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                });
            }
            
            // Cerrar sidebar al hacer clic en overlay
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    closeSidebar();
                });
            }
            
            // Cerrar sidebar al hacer clic en un enlace
            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    // Solo cerrar en móviles/tablets
                    if (window.innerWidth < 992) {
                        closeSidebar();
                    }
                });
            });
            
            // Cerrar sidebar cuando se redimensiona la ventana
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 992) {
                    closeSidebar();
                }
            });
        });
        
        
        // Cargar el contador de eventos (solo al inicio)
        function loadEventCounter() {
            const contador = document.getElementById('contadorEventos');
            if (!contador) return;
            
            fetch(BASE_URL + '/contador-eventos')
                .then(response => response.json())
                .then(data => {
                    contador.textContent = data.count || 0;
                })
                .catch(error => console.log('Error loading events:', error));
        }
        
        // Iniciar el contador solo una vez al cargar
        function initEventCounter() {
            if (document.getElementById('contadorEventos')) {
                loadEventCounter(); // Solo carga inicial
                console.log('✅ Contador de eventos cargado (sin polling automático)');
            }
        }
        
        // Iniciar al cargar la página
        window.addEventListener('load', initEventCounter);


    </script>
    
    {{-- Tu JS global (de app.js) no se carga aquí, se importa en resources/js/app.js --}}
    
    <!-- Script para formatear fechas DD-MM-AAAA -->
    <script src="{{ asset('js/fecha-formatter.js') }}"></script>
    
    <!-- Cola Médica ya está incluida en app.js compilado con Vite -->
    
    <!-- Inicializar dropdowns de Bootstrap manualmente -->
    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                if (typeof bootstrap !== 'undefined') {
                    const dropdownElements = document.querySelectorAll('[data-bs-toggle="dropdown"]');
                    dropdownElements.forEach(function(element) {
                        new bootstrap.Dropdown(element);
                    });
                }
            }, 300);
        });
    </script>
    
    <!-- Espacio para scripts de páginas específicas -->
    <script src="{{ asset('js/citas-horario.js') }}"></script>
    @stack('scripts')

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // 1. Para campos de SOLO FECHA (ej. Fecha de Nacimiento)
            flatpickr("input[type='date']", {
                locale: "es",
                altInput: true,           // Muestra la fecha bonita al usuario
                altFormat: "d/m/Y",       // Ej: 25/11/2025
                dateFormat: "Y-m-d",      // Lo que se envía a la base de datos
                allowInput: true,         // Permite escribir si quieren
                theme: "material_blue"
            });

            // 2. Para campos de FECHA Y HORA (ej. Consultas, Vacunas)
            document.querySelectorAll("input[type='datetime-local']").forEach(function(input) {
                const esCita = input.name === 'proxima_cita';
                flatpickr(input, {
                enableTime: true,
                minuteIncrement: esCita ? 30 : 5,
                locale: "es",
                altInput: true,
                altFormat: "d/m/Y h:i K", // Ej: 25/11/2025 05:30 PM
                dateFormat: "Y-m-d\\TH:i", // Formato compatible con Laravel
                time_24hr: false,         // Usamos AM/PM que es más elegante
                allowInput: true,
                theme: "material_blue"
                });
            });
        });
    </script>
</body>
</html>
