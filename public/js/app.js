// Funciones JavaScript para el sistema
document.addEventListener('DOMContentLoaded', function() {
    // ========== LAZY LOADING NATIVO ==========
    // Agregar loading="lazy" a todas las imágenes
    const images = document.querySelectorAll('img:not([loading="lazy"])');
    images.forEach(img => {
        if (!img.classList.contains('no-lazy')) {
            img.loading = 'lazy';
            img.setAttribute('decoding', 'async');
        }
    });

    // ========== INTERSECTION OBSERVER PARA LAZY LOADING AVANZADO ==========
    // Lazy load para elementos con data-src
    const observerOptions = {
        root: null,
        rootMargin: '50px',
        threshold: 0.01
    };

    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                const src = img.dataset.src;
                const srcset = img.dataset.srcset;

                if (src) {
                    img.src = src;
                }
                if (srcset) {
                    img.srcset = srcset;
                }

                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    }, observerOptions);

    // Observar todas las imágenes lazy
    document.querySelectorAll('img.lazy').forEach(img => {
        imageObserver.observe(img);
    });

    // ========== LAZY LOADING PARA CONTENIDO DINÁMICO ==========
    // Cargar secciones bajo demanda
    const contentObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const section = entry.target;
                const url = section.dataset.lazyLoad;

                if (url && !section.classList.contains('loaded')) {
                    loadSectionData(url, section);
                    section.classList.add('loaded');
                    contentObserver.unobserve(section);
                }
            }
        });
    }, observerOptions);

    document.querySelectorAll('[data-lazy-load]').forEach(section => {
        contentObserver.observe(section);
    });

    // ========== LAZY LOADING PARA TABLAS GRANDES ==========
    // Carga paginada de tablas
    const tableObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const table = entry.target;
                const url = table.dataset.lazyLoadTable;
                const page = parseInt(table.dataset.page || 1);

                if (url && !table.classList.contains('loading')) {
                    loadTablePage(url, table, page);
                    table.classList.add('loading');
                }
            }
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('table[data-lazy-load-table]').forEach(table => {
        tableObserver.observe(table);
    });

    // Inicializar tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Formatear números como moneda
    window.formatCurrency = function(amount) {
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0
        }).format(amount);
    };

    // Formatear fechas
    window.formatDate = function(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-CO');
    };

    // Mostrar alertas
    window.showAlert = function(message, type = 'success') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        const container = document.querySelector('.container');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
        }

        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    };

    // Calcular total automáticamente en formularios
    const calcularTotales = function() {
        // Calcular total de venta
        const kilosVenta = document.getElementById('kilos-vendidos');
        const precioVenta = document.getElementById('precio-venta');
        const totalVenta = document.getElementById('total-venta');

        if (kilosVenta && precioVenta && totalVenta) {
            const calcularVenta = () => {
                const kilos = parseFloat(kilosVenta.value) || 0;
                const precio = parseFloat(precioVenta.value) || 0;
                totalVenta.value = (kilos * precio).toFixed(2);
            };

            kilosVenta.addEventListener('input', calcularVenta);
            precioVenta.addEventListener('input', calcularVenta);
        }

        // Calcular total de jornal
        const horasJornal = document.getElementById('horas-trabajadas');
        const tarifaJornal = document.getElementById('tarifa-hora');
        const totalJornal = document.getElementById('total-jornal');

        if (horasJornal && tarifaJornal && totalJornal) {
            const calcularJornal = () => {
                const horas = parseFloat(horasJornal.value) || 0;
                const tarifa = parseFloat(tarifaJornal.value) || 0;
                totalJornal.value = (horas * tarifa).toFixed(2);
            };

            horasJornal.addEventListener('input', calcularJornal);
            tarifaJornal.addEventListener('input', calcularJornal);
        }
    };

    calcularTotales();

    // Manejar envío de formularios con AJAX
    const forms = document.querySelectorAll('form[data-ajax="true"]');
    forms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const action = this.getAttribute('action') || window.location.href;
            
            try {
                const response = await fetch(action, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    if (result.redirect) {
                        setTimeout(() => {
                            window.location.href = result.redirect;
                        }, 1500);
                    } else {
                        this.reset();
                    }
                } else {
                    showAlert(result.message, 'danger');
                }
            } catch (error) {
                showAlert('Error al procesar la solicitud', 'danger');
                console.error('Error:', error);
            }
        });
    });
});

// Función para confirmar eliminaciones
window.confirmDelete = function(message = '¿Está seguro de que desea eliminar este registro?') {
    return confirm(message);
};

// Función para cargar datos dinámicamente
window.loadData = async function(url, containerId) {
    try {
        const response = await fetch(url);
        const data = await response.json();
        
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = data.html || data;
        }
        
        return data;
    } catch (error) {
        console.error('Error loading data:', error);
        showAlert('Error al cargar los datos', 'danger');
    }
};

// ========== FUNCIONES DE LAZY LOADING ==========

/**
 * Carga contenido de una sección bajo demanda
 */
window.loadSectionData = async function(url, section) {
    const loader = document.createElement('div');
    loader.className = 'text-center p-4';
    loader.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>';
    section.appendChild(loader);

    try {
        const response = await fetch(url);
        const data = await response.json();

        loader.remove();

        if (data.html) {
            section.innerHTML = data.html;
        } else {
            section.innerHTML = JSON.stringify(data);
        }

        // Reinicializar tooltips después de cargar
        const tooltips = section.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(el => new bootstrap.Tooltip(el));

        return data;
    } catch (error) {
        console.error('Error loading section:', error);
        loader.innerHTML = '<div class="alert alert-danger">Error al cargar el contenido</div>';
    }
};

/**
 * Carga página de tabla bajo demanda
 */
window.loadTablePage = async function(url, table, page = 1) {
    const tbody = table.querySelector('tbody');
    if (!tbody) return;

    const loader = document.createElement('tr');
    loader.innerHTML = '<td colspan="100%" class="text-center p-4"><div class="spinner-border text-primary" role="status"></div></td>';
    tbody.appendChild(loader);

    try {
        const response = await fetch(`${url}?page=${page}`);
        const data = await response.json();

        loader.remove();

        if (data.rows && Array.isArray(data.rows)) {
            data.rows.forEach(row => {
                const tr = document.createElement('tr');
                tr.innerHTML = row;
                tbody.appendChild(tr);
            });
        }

        // Actualizar atributo de página
        table.dataset.page = page + 1;

        return data;
    } catch (error) {
        console.error('Error loading table page:', error);
        loader.innerHTML = '<tr><td colspan="100%" class="alert alert-danger">Error al cargar datos</td></tr>';
    }
};

/**
 * Fuerza la carga de una imagen lazy
 */
window.loadImage = function(img) {
    if (img.dataset.src) {
        img.src = img.dataset.src;
    }
    if (img.dataset.srcset) {
        img.srcset = img.dataset.srcset;
    }
    img.classList.remove('lazy');
};

/**
 * Prefetch de recursos para mejorar rendimiento
 */
window.prefetchResource = function(url) {
    const link = document.createElement('link');
    link.rel = 'prefetch';
    link.href = url;
    document.head.appendChild(link);
};

/**
 * Preload de recursos críticos
 */
window.preloadResource = function(url, as = 'script') {
    const link = document.createElement('link');
    link.rel = 'preload';
    link.as = as;
    link.href = url;
    document.head.appendChild(link);
};