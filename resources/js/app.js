// 1. Importar jQuery PRIMERO (antes de Bootstrap)
import $ from 'jquery';
window.$ = window.jQuery = $;

// 2. Importa Bootstrap JavaScript completo (que necesita jQuery)
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// 3. Importa el archivo bootstrap de Laravel (para axios, etc)
import './bootstrap';

// 4. Importa cola-medica después de que Echo esté disponible
// MVP_POSTERIOR: Cola medica en tiempo real
// MVP_POSTERIOR | import './cola-medica';