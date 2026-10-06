/**
 * Script para formatear fechas en formato DD-MM-AAAA
 * Permite escritura manual y conversión automática para envío al servidor
 */

(function() {
    'use strict';

    // Función para formatear fecha mientras se escribe
    function formatearFecha(input) {
        let valor = input.value.replace(/\D/g, ''); // Eliminar todo excepto números
        
        if (valor.length >= 2) {
            valor = valor.substring(0, 2) + '-' + valor.substring(2);
        }
        if (valor.length >= 5) {
            valor = valor.substring(0, 5) + '-' + valor.substring(5);
        }
        if (valor.length > 10) {
            valor = valor.substring(0, 10);
        }
        
        input.value = valor;
    }

    // Función para validar fecha DD-MM-AAAA
    function validarFecha(fechaStr) {
        if (!fechaStr || fechaStr.trim() === '') return true; // Permitir vacío si no es required
        
        const regex = /^(\d{2})-(\d{2})-(\d{4})$/;
        const match = fechaStr.match(regex);
        
        if (!match) return false;
        
        const dia = parseInt(match[1], 10);
        const mes = parseInt(match[2], 10);
        const anio = parseInt(match[3], 10);
        
        // Validar rangos básicos
        if (mes < 1 || mes > 12) return false;
        if (dia < 1 || dia > 31) return false;
        if (anio < 1900 || anio > 2100) return false;
        
        // Validar días por mes
        const diasPorMes = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        
        // Año bisiesto
        if ((anio % 4 === 0 && anio % 100 !== 0) || anio % 400 === 0) {
            diasPorMes[1] = 29;
        }
        
        if (dia > diasPorMes[mes - 1]) return false;
        
        return true;
    }

    // Función para convertir DD-MM-AAAA a YYYY-MM-DD
    function convertirAFormatoISO(fechaStr) {
        if (!fechaStr || fechaStr.trim() === '') return '';
        
        const regex = /^(\d{2})-(\d{2})-(\d{4})$/;
        const match = fechaStr.match(regex);
        
        if (!match) return fechaStr; // Retornar sin cambios si no coincide
        
        const dia = match[1];
        const mes = match[2];
        const anio = match[3];
        
        return `${anio}-${mes}-${dia}`;
    }

    // Función para convertir YYYY-MM-DD a DD-MM-AAAA
    function convertirAFormatoLocal(fechaStr) {
        if (!fechaStr || fechaStr.trim() === '') return '';
        
        const regex = /^(\d{4})-(\d{2})-(\d{2})$/;
        const match = fechaStr.match(regex);
        
        if (!match) return fechaStr;
        
        const anio = match[1];
        const mes = match[2];
        const dia = match[3];
        
        return `${dia}-${mes}-${anio}`;
    }

    // Inicializar cuando el DOM esté listo
    function inicializar() {
        // Agregar event listeners a todos los campos con clase fecha-input
        const camposFecha = document.querySelectorAll('.fecha-input');
        
        camposFecha.forEach(function(input) {
            // Formatear mientras escribe
            input.addEventListener('input', function(e) {
                formatearFecha(e.target);
            });
            
            // Validar al perder el foco
            input.addEventListener('blur', function(e) {
                const valor = e.target.value.trim();
                if (valor && !validarFecha(valor)) {
                    e.target.classList.add('is-invalid');
                    
                    // Mostrar mensaje de error si no existe
                    let errorDiv = e.target.nextElementSibling;
                    if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
                        errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback';
                        errorDiv.textContent = 'Formato de fecha inválido. Use DD-MM-AAAA';
                        e.target.parentNode.insertBefore(errorDiv, e.target.nextSibling);
                    }
                } else {
                    e.target.classList.remove('is-invalid');
                    const errorDiv = e.target.nextElementSibling;
                    if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                        errorDiv.remove();
                    }
                }
            });
        });
        
        // Interceptar envío de formularios
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                const camposFechaEnForm = form.querySelectorAll('.fecha-input');
                let hayErrores = false;
                
                camposFechaEnForm.forEach(function(input) {
                    const valor = input.value.trim();
                    
                    // Validar si el campo es requerido o tiene valor
                    if (input.hasAttribute('required') || valor) {
                        if (!validarFecha(valor)) {
                            input.classList.add('is-invalid');
                            hayErrores = true;
                            
                            // Mostrar mensaje de error
                            let errorDiv = input.nextElementSibling;
                            if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
                                errorDiv = document.createElement('div');
                                errorDiv.className = 'invalid-feedback';
                                errorDiv.textContent = 'Formato de fecha inválido. Use DD-MM-AAAA';
                                input.parentNode.insertBefore(errorDiv, input.nextSibling);
                            }
                        } else {
                            // Convertir a formato ISO antes de enviar
                            // Crear un input hidden con el valor en formato ISO
                            const hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = input.name;
                            hiddenInput.value = convertirAFormatoISO(valor);
                            
                            // Cambiar el nombre del input original para que no se envíe
                            input.name = input.name + '_display';
                            input.setAttribute('data-original-name', input.name.replace('_display', ''));
                            
                            // Agregar el hidden al formulario
                            form.appendChild(hiddenInput);
                        }
                    }
                });
                
                if (hayErrores) {
                    e.preventDefault();
                    
                    // Scroll al primer error
                    const primerError = form.querySelector('.is-invalid');
                    if (primerError) {
                        primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        primerError.focus();
                    }
                }
            });
        });
    }

    // Ejecutar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inicializar);
    } else {
        inicializar();
    }

    // Re-inicializar cuando se cargue contenido dinámico (modales, AJAX, etc.)
    window.reinicializarFechas = inicializar;

    // Exponer funciones para uso externo
    window.FechaFormatter = {
        formatear: formatearFecha,
        validar: validarFecha,
        convertirAISO: convertirAFormatoISO,
        convertirALocal: convertirAFormatoLocal,
        reinicializar: inicializar
    };
})();
