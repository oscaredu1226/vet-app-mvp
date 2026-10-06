const RAZAS_CACHE_KEY = 'razas_cache_v1';
const RAZAS_URL = '/data/razas.json';

async function cargarRazasCached() {
    try {
        // Intentar cargar desde cache
        const cached = localStorage.getItem(RAZAS_CACHE_KEY);
        if (cached) {
            return JSON.parse(cached);
        }

        // Si no hay cache, hacer fetch
        const response = await fetch(RAZAS_URL, { cache: 'no-store' });
        if (!response.ok) {
            console.error('Error al cargar razas:', response.status);
            return [];
        }

        const data = await response.json();
        
        // Guardar en cache
        localStorage.setItem(RAZAS_CACHE_KEY, JSON.stringify(data));
        
        return data;
    } catch (error) {
        console.error('Error cargando razas:', error);
        return [];
    }
}

async function poblarSelectRazas(selectEl, especie = null, selectedValue = null) {
    try {
        const razas = await cargarRazasCached();
        
        // Filtrar por especie si se especifica
        const razasFiltradas = especie 
            ? razas.filter(r => r.especie.toLowerCase() === especie.toLowerCase())
            : razas;

        // Generar opciones
        const opciones = razasFiltradas
            .map(r => {
                // Comparación robusta: trim y normalize
                const isSelected = r.nombre.trim() === (selectedValue ? selectedValue.trim() : '') ? ' selected' : '';
                return `<option value="${escapeHtml(r.nombre)}"${isSelected}>${escapeHtml(r.nombre)}</option>`;
            })
            .join('');

        // Actualizar select
        selectEl.innerHTML = '<option value="">Seleccionar raza...</option>' + opciones;
    } catch (error) {
        console.error('Error poblando select de razas:', error);
        selectEl.innerHTML = '<option value="">Error cargando razas</option>';
    }
}

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    // Listener para cambio de especie en formularios de mascotas
    const especieSelects = document.querySelectorAll('#especie, #edit_especie, select[name="especie"]');
    
    especieSelects.forEach(especieEl => {
        especieEl.addEventListener('change', function() {
            const form = this.closest('form');
            const razaEl = form.querySelector('#raza, #edit_raza, select[name="raza"]');
            if (razaEl) {
                const especie = this.value || null;
                poblarSelectRazas(razaEl, especie);
            }
        });
    });

    // Poblar razas al cargar si ya hay especie seleccionada
    especieSelects.forEach(especieEl => {
        if (especieEl.value) {
            const form = especieEl.closest('form');
            const razaEl = form.querySelector('#raza, #edit_raza, select[name="raza"]');
            if (razaEl) {
                const selectedRaza = razaEl.getAttribute('data-selected') || razaEl.value;
                poblarSelectRazas(razaEl, especieEl.value, selectedRaza);
            }
        }
    });
});

if (typeof jQuery !== 'undefined') {
    (function($) {
        $(document).ready(function() {
            
            // Al abrir modal de nueva mascota
            $('#mascotaModal').on('show.bs.modal', function(e) {
                const razaEl = document.getElementById('raza');
                const especieEl = document.getElementById('especie');
                
                if (razaEl && especieEl) {
                    const especie = especieEl.value || null;
                    poblarSelectRazas(razaEl, especie);
                }
            });

            $('#mascotaEditModal').on('shown.bs.modal', function() {
                const razaEl = this.querySelector('#edit_raza, #raza, select[name="raza"]');
                const especieEl = this.querySelector('#edit_especie, #especie, select[name="especie"]');
                
                if (razaEl && especieEl) {
                    // Mostrar TODAS las razas (pasar null como especie)
                    const selectedRaza = razaEl.getAttribute('data-selected') || razaEl.value;
                    poblarSelectRazas(razaEl, null, selectedRaza);
                }
            });

            // Para modal de mascotas por cliente
            $('#mascotaModal').on('shown.bs.modal', function() {
                const razaEl = this.querySelector('#raza');
                const especieEl = this.querySelector('#especie');
                
                if (razaEl && especieEl && especieEl.value) {
                    poblarSelectRazas(razaEl, especieEl.value);
                }
            });
        });
    })(jQuery);
}

function limpiarCacheRazas() {
    localStorage.removeItem(RAZAS_CACHE_KEY);
    console.log('Cache de razas eliminado. Recargue la página para obtener datos frescos.');
}

window.cargarRazasCached = cargarRazasCached;
window.poblarSelectRazas = poblarSelectRazas;
window.limpiarCacheRazas = limpiarCacheRazas;
