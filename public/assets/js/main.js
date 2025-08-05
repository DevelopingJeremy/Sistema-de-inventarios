// Dashboard JavaScript Functions

// Dark mode toggle
let darkModeInitialized = false;

function initDarkMode() {
    // Evitar inicialización múltiple
    if (darkModeInitialized) {
        return;
    }
    
    const darkModeToggle = document.getElementById('darkModeToggle');
    const body = document.body;
    
    // Check if user has a saved preference
    const savedDarkMode = localStorage.getItem('darkMode');
    
    if (savedDarkMode === 'true') {
        body.classList.add('dark-mode');
        updateDarkModeIcon(true);
    }
    
    if (darkModeToggle) {
        // Remover event listeners existentes para evitar duplicados
        const newToggle = darkModeToggle.cloneNode(true);
        darkModeToggle.parentNode.replaceChild(newToggle, darkModeToggle);
        
        newToggle.addEventListener('click', function() {
            body.classList.toggle('dark-mode');
            const isDarkMode = body.classList.contains('dark-mode');
            
            // Save preference to localStorage
            localStorage.setItem('darkMode', isDarkMode);
            
            // Update icon
            updateDarkModeIcon(isDarkMode);
            
            // Forzar actualización de estilos para elementos dinámicos
            forceDarkModeUpdate(isDarkMode);
        });
        
        darkModeInitialized = true;
    }
}

// Función para forzar actualización de estilos en dark mode
function forceDarkModeUpdate(isDarkMode) {
    // Actualizar inputs y selects
    const inputs = document.querySelectorAll('.form-input, .form-select, .form-textarea');
    inputs.forEach(input => {
        if (isDarkMode) {
            input.style.backgroundColor = 'var(--bg-secondary)';
            input.style.color = 'var(--text-primary)';
            input.style.borderColor = 'var(--border-color)';
        } else {
            input.style.backgroundColor = '';
            input.style.color = '';
            input.style.borderColor = '';
        }
    });
    
    // Actualizar contenedores de formularios
    const containers = document.querySelectorAll('.form-container, .form-section, .product-detail-container');
    containers.forEach(container => {
        if (isDarkMode) {
            container.style.backgroundColor = 'var(--card-bg)';
            container.style.borderColor = 'var(--border-color)';
        } else {
            container.style.backgroundColor = '';
            container.style.borderColor = '';
        }
    });
    
    // Actualizar títulos y textos
    const titles = document.querySelectorAll('.section-title, .form-label, .page-title, .page-subtitle');
    titles.forEach(title => {
        if (isDarkMode) {
            title.style.color = 'var(--text-primary)';
        } else {
            title.style.color = '';
        }
    });
    
    // Actualizar valores de detalles
    const values = document.querySelectorAll('.detail-value, .detail-label');
    values.forEach(value => {
        if (isDarkMode) {
            if (value.classList.contains('detail-label')) {
                value.style.color = 'var(--text-secondary)';
            } else {
                value.style.color = 'var(--text-primary)';
            }
        } else {
            value.style.color = '';
        }
    });
}

// Update dark mode icon
function updateDarkModeIcon(isDarkMode) {
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        const icon = darkModeToggle.querySelector('i');
        if (icon) {
            icon.className = isDarkMode ? 'fas fa-sun' : 'fas fa-moon';
        }
    }
}

// Time filters functionality
function initTimeFilters() {
    document.querySelectorAll('.time-filter').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.time-filter').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

// Modal functions
function openModal() {
    document.getElementById('addProductModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('addProductModal').style.display = 'none';
    document.getElementById('addProductForm').reset();
}

// Close modal when clicking outside
function initModalOutsideClick() {
    window.onclick = function(event) {
        const modal = document.getElementById('addProductModal');
        if (event.target === modal) {
            closeModal();
        }
    }
}

// Save product function
function saveProduct() {
    const form = document.getElementById('addProductForm');
    const formData = new FormData(form);
    
    // Here you would typically send the data to your backend
    console.log('Producto a guardar:', Object.fromEntries(formData));
    
    // For demo purposes, we'll just close the modal
    closeModal();
}

// Search and filter functionality
function filterProducts() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const categoryFilter = document.getElementById('categoryFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const supplierFilter = document.getElementById('supplierFilter').value;
    
    const rows = document.querySelectorAll('#productsTableBody tr');
    
    rows.forEach(row => {
        const productName = row.cells[0].textContent.toLowerCase();
        const category = row.cells[1].textContent;
        const status = row.cells[4].textContent;
        const supplier = row.cells[5].textContent;
        
        const matchesSearch = productName.includes(searchTerm);
        const matchesCategory = !categoryFilter || category === categoryFilter;
        const matchesStatus = !statusFilter || status === statusFilter;
        const matchesSupplier = !supplierFilter || supplier === supplierFilter;
        
        if (matchesSearch && matchesCategory && matchesStatus && matchesSupplier) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Initialize search and filter event listeners
function initSearchAndFilters() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    const supplierFilter = document.getElementById('supplierFilter');
    
    if (searchInput) {
        searchInput.addEventListener('input', filterProducts);
    }
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterProducts);
    }
    if (statusFilter) {
        statusFilter.addEventListener('change', filterProducts);
    }
    if (supplierFilter) {
        supplierFilter.addEventListener('change', filterProducts);
    }
}

// Initialize "Nuevo Producto" button
function initAddProductButton() {
    const addProductBtn = document.querySelector('.btn-primary');
    if (addProductBtn) {
        addProductBtn.addEventListener('click', openModal);
    }
}

// Product actions (view, edit, delete)
function initProductActions() {
    // View product - Functionality moved to individual pages
    // Edit product - Functionality moved to individual pages  
    // Delete product - Functionality moved to individual pages
}

// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('es-CR', {
        style: 'currency',
        currency: 'CRC'
    }).format(amount);
}

function formatNumber(number) {
    return new Intl.NumberFormat('es-CR').format(number);
}

// Initialize all functions when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initDarkMode();
    initTimeFilters();
    initModalOutsideClick();
    initSearchAndFilters();
    initAddProductButton();
    initProductActions();
    initTooltips();
    
    console.log('Dashboard inicializado correctamente');
});

// Export functions for global access
window.openModal = openModal;
window.closeModal = closeModal;
window.saveProduct = saveProduct;
window.filterProducts = filterProducts;

// Productos específicos
// Función para ver producto
function verProducto(id) {
    window.location.href = 'ver-producto.php?id=' + id;
}

// Función para editar producto
function editarProducto(id) {
    window.location.href = 'editar-producto.php?id=' + id;
}

// Función para eliminar producto
function eliminarProducto(id, nombre) {
    showConfirmAlert(
        '¿Eliminar producto?',
        `¿Estás seguro de que quieres eliminar el producto "${nombre}"?\n\nEsta acción no se puede deshacer.`,
        function() {
            // Crear formulario temporal para enviar la petición
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '../../src/inventario/productos/eliminar-producto.php';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'id_producto';
            input.value = id;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    );
}

// Función para aplicar todos los filtros
function aplicarFiltros() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    if (!searchInput || !categoryFilter || !statusFilter) {
        console.warn('Algunos elementos de filtro no encontrados');
        return;
    }
    
    const searchTerm = searchInput.value.toLowerCase().trim();
    const categoryValue = categoryFilter.value.toLowerCase().trim();
    const statusValue = statusFilter.value.toLowerCase().trim();
    
    const rows = document.querySelectorAll('#productsTableBody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        // Obtener datos específicos de cada columna con mejor manejo de errores
        const productNameElement = row.querySelector('td:nth-child(1) div div');
        const productName = productNameElement ? productNameElement.textContent.toLowerCase() : '';
        
        const categoryElement = row.querySelector('td:nth-child(2)');
        const category = categoryElement ? categoryElement.textContent.toLowerCase() : '';
        
        const priceElement = row.querySelector('td:nth-child(3)');
        const price = priceElement ? priceElement.textContent.toLowerCase() : '';
        
        const stockElement = row.querySelector('td:nth-child(4)');
        const stock = stockElement ? stockElement.textContent.toLowerCase() : '';
        
        const statusElement = row.querySelector('td:nth-child(5) .status-badge');
        const status = statusElement ? statusElement.textContent.toLowerCase() : '';
        
        const codesElement = row.querySelector('td:nth-child(6)');
        const codes = codesElement ? codesElement.textContent.toLowerCase() : '';
        
        const supplierElement = row.querySelector('td:nth-child(7)');
        const supplier = supplierElement ? supplierElement.textContent.toLowerCase() : '';
        
        // Combinar todos los campos para búsqueda
        const allFields = `${productName} ${category} ${price} ${stock} ${status} ${codes} ${supplier}`;
        
        // Búsqueda mejorada: buscar por términos individuales
        let matchesSearch = true;
        if (searchTerm && searchTerm.length > 0) {
            const searchTerms = searchTerm.split(' ').filter(term => term.length > 0);
            matchesSearch = searchTerms.every(term => allFields.includes(term));
        }
        
        // Verificar filtros con mejor manejo de valores vacíos
        const matchesCategory = categoryValue.length === 0 || category.includes(categoryValue);
        const matchesStatus = statusValue.length === 0 || status.includes(statusValue);
        
        const shouldShow = matchesSearch && matchesCategory && matchesStatus;
        row.style.display = shouldShow ? '' : 'none';
        
        if (shouldShow) {
            visibleCount++;
        }
    });
    
    // Debug: mostrar información de búsqueda en consola
    if (searchTerm) {
        console.log('Búsqueda:', searchTerm);
        console.log('Productos visibles:', visibleCount);
        console.log('Total productos:', rows.length);
    }
    
    // Mostrar mensaje si no hay resultados
    showNoResultsMessage(visibleCount, searchTerm || categoryValue || statusValue);
}

// Función para mostrar mensaje cuando no hay resultados
function showNoResultsMessage(visibleCount, hasFilters) {
    const tableBody = document.querySelector('#productsTableBody');
    if (!tableBody) return;
    
    let noResultsRow = tableBody.querySelector('.no-results-row');
    
    if (visibleCount === 0 && hasFilters) {
        if (!noResultsRow) {
            noResultsRow = document.createElement('tr');
            noResultsRow.className = 'no-results-row';
            noResultsRow.innerHTML = `
                <td colspan="8" style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-search" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                    <div>No se encontraron productos</div>
                    <div style="font-size: 0.9rem; margin-top: 8px;">Intenta con otros términos de búsqueda</div>
                </td>
            `;
            tableBody.appendChild(noResultsRow);
        }
    } else if (noResultsRow) {
        noResultsRow.remove();
    }
}

// Función debounce para mejorar el rendimiento de la búsqueda
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Función para mostrar alertas de éxito
function showSuccessAlert(message) {
    Swal.fire({
        icon: 'success',
        title: '¡Éxito!',
        text: message,
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#007bff'
    });
}

// Función para mostrar alertas de error
function showErrorAlert(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#dc3545'
    });
}

// Función para mostrar alertas de confirmación
function showConfirmAlert(title, message, callback) {
    Swal.fire({
        icon: 'warning',
        title: title,
        text: message,
        showCancelButton: true,
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            callback();
        }
    });
}

// Inicializar filtros de productos
function initProductFilters() {
    // Event listeners para todos los filtros
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    if (searchInput) {
        searchInput.addEventListener('input', debounce(aplicarFiltros, 300));
    }
    if (categoryFilter) {
        categoryFilter.addEventListener('change', aplicarFiltros);
    }
    if (statusFilter) {
        statusFilter.addEventListener('change', aplicarFiltros);
    }
    
    // Función para limpiar filtros
    function clearProductFilters() {
        if (searchInput) searchInput.value = '';
        if (categoryFilter) categoryFilter.value = '';
        if (statusFilter) statusFilter.value = '';
        aplicarFiltros();
    }
    
    // Agregar botón de limpiar filtros si no existe
    const filtersSection = document.querySelector('.filters-section');
    if (filtersSection && !document.getElementById('clearProductFiltersBtn')) {
        const clearButton = document.createElement('button');
        clearButton.id = 'clearProductFiltersBtn';
        clearButton.type = 'button';
        clearButton.className = 'btn-secondary';
        clearButton.innerHTML = '<i class="fas fa-times"></i> Limpiar';
        clearButton.onclick = clearProductFilters;
        clearButton.style.marginLeft = '10px';
        
        const filterGroup = filtersSection.querySelector('.filter-group');
        if (filterGroup) {
            filterGroup.appendChild(clearButton);
        }
    }
    
    // Ejecutar filtros al cargar la página para asegurar estado inicial correcto
    aplicarFiltros();
    
    // Debug: verificar si hay productos de accesorios
    setTimeout(() => {
        const rows = document.querySelectorAll('#productsTableBody tr');
        let accesoriosCount = 0;
        rows.forEach(row => {
            const categoryElement = row.querySelector('td:nth-child(2)');
            if (categoryElement && categoryElement.textContent.toLowerCase().includes('accesorios')) {
                accesoriosCount++;
                console.log('Producto de accesorios encontrado:', row.querySelector('td:nth-child(1) div div')?.textContent);
            }
        });
        console.log('Total productos de accesorios:', accesoriosCount);
    }, 1000);
}

// Verificar si hay mensajes de éxito o error en la URL
function checkUrlMessages() {
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');
    const error = urlParams.get('error');
    const message = urlParams.get('message');

    if (success === '1' && message) {
        showSuccessAlert(decodeURIComponent(message));
    } else if (error === '1' && message) {
        showErrorAlert(decodeURIComponent(message));
    }
}

// Exportar funciones específicas de productos
window.verProducto = verProducto;
window.editarProducto = editarProducto;
window.eliminarProducto = eliminarProducto;
window.aplicarFiltros = aplicarFiltros;
window.showSuccessAlert = showSuccessAlert;
window.showErrorAlert = showErrorAlert;
window.showConfirmAlert = showConfirmAlert;
window.initProductFilters = initProductFilters;
window.checkUrlMessages = checkUrlMessages;

// Agregar producto específicos
// Vista previa de imagen
function initImagePreview() {
    const imageInput = document.querySelector('input[name="imagen_archivo"]');
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Vista previa">`;
                }
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '<i class="fas fa-image" style="font-size: 48px; color: #ddd;"></i>';
            }
        });
    }
}

// Validación del formulario de producto
function initProductFormValidation() {
    const productForm = document.getElementById('productForm');
    if (productForm) {
        productForm.addEventListener('submit', function(e) {
            const precio = document.querySelector('input[name="precio"]').value;
            const stock = document.querySelector('input[name="stock"]').value;
            
            if (parseFloat(precio) < 0) {
                e.preventDefault();
                return;
            }
            
            if (parseInt(stock) < 0) {
                e.preventDefault();
                return;
            }
        });
    }
}

// Inicializar funcionalidades de agregar producto
function initAddProductPage() {
    initImagePreview();
    initProductFormValidation();
    initCodeValidation();
}

// Función para validar códigos únicos
function initCodeValidation() {
    const codigoBarrasInput = document.querySelector('input[name="codigo_barras"]');
    const codigoInternoInput = document.querySelector('input[name="codigo_interno"]');
    
    if (codigoBarrasInput) {
        codigoBarrasInput.addEventListener('blur', function() {
            validateUniqueCode(this.value, 'barras');
        });
    }
    
    if (codigoInternoInput) {
        codigoInternoInput.addEventListener('blur', function() {
            validateUniqueCode(this.value, 'interno');
        });
    }
}

// Función para validar códigos únicos
function validateUniqueCode(code, type) {
    if (!code.trim()) return;
    
    // Crear un indicador visual
    const input = document.querySelector(`input[name="codigo_${type}"]`);
    if (!input) return;
    
    // Mostrar indicador de carga
    input.style.borderColor = '#ffc107';
    
    // Simular validación (en un caso real, harías una petición AJAX)
    // Por ahora, solo validamos que no esté vacío
    setTimeout(() => {
        if (code.trim() === '') {
            input.style.borderColor = '#dc3545';
            showFieldError(input, 'Este campo es obligatorio');
        } else {
            input.style.borderColor = '#28a745';
            hideFieldError(input);
        }
    }, 500);
}

// Función para mostrar error en campo
function showFieldError(input, message) {
    // Remover error anterior
    hideFieldError(input);
    
    // Crear elemento de error
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.style.color = '#dc3545';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    errorDiv.textContent = message;
    
    // Insertar después del input
    input.parentNode.appendChild(errorDiv);
}

// Función para ocultar error en campo
function hideFieldError(input) {
    const existingError = input.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

// Exportar funciones específicas de agregar producto
window.initAddProductPage = initAddProductPage;
window.initImagePreview = initImagePreview;
window.initProductFormValidation = initProductFormValidation;
window.initCodeValidation = initCodeValidation;
window.validateUniqueCode = validateUniqueCode;
window.showFieldError = showFieldError;
window.hideFieldError = hideFieldError;
window.forceDarkModeUpdate = forceDarkModeUpdate;

// Autenticación específicos
// Focus en el campo de correo al cargar la página de login
function initLoginFocus() {
    const correoInput = document.getElementById('correo');
    if (correoInput) {
        correoInput.focus();
    }
}

// Mostrar alerta de error de autenticación
function showAuthError(message) {
    Swal.fire({
        icon: "error",
        title: "Error de autenticación",
        text: message,
        confirmButtonColor: '#007bff'
    });
}

// Inicializar funcionalidades de autenticación
function initAuthPage() {
    initLoginFocus();
}

// Exportar funciones específicas de autenticación
window.initAuthPage = initAuthPage;
window.showAuthError = showAuthError;
window.initLoginFocus = initLoginFocus;

// Empresa específicos
// Mostrar/ocultar campo de otra categoría
function mostrarCampoOtro() {
    const select = document.getElementById('categoriaSelect');
    const campoOtro = document.getElementById('campoOtro');
    const otraCategoriaInput = document.getElementById('otraCategoriaInput');

    if (select.value === 'Otro') {
        campoOtro.style.display = 'block';
        otraCategoriaInput.required = true;
    } else {
        campoOtro.style.display = 'none';
        otraCategoriaInput.required = false;
        otraCategoriaInput.value = '';
    }
}

// Actualizar label del archivo cuando se selecciona
function initFileUpload() {
    const logoInput = document.getElementById('logoInput');
    if (logoInput) {
        logoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const label = document.querySelector('.file-upload-label span');
            
            if (file) {
                label.textContent = file.name;
                document.querySelector('.file-upload-label').style.borderColor = 'var(--success-color)';
                document.querySelector('.file-upload-label').style.background = 'rgba(40, 167, 69, 0.05)';
            } else {
                label.textContent = 'Haz clic para seleccionar un logo';
                document.querySelector('.file-upload-label').style.borderColor = 'var(--border-color)';
                document.querySelector('.file-upload-label').style.background = 'var(--bg-primary)';
            }
        });
    }
}

// Inicializar funcionalidades de empresa
function initCompanyPage() {
    initFileUpload();
}

// Exportar funciones específicas de empresa
window.mostrarCampoOtro = mostrarCampoOtro;
window.initFileUpload = initFileUpload;
window.initCompanyPage = initCompanyPage;

// Función para inicializar tooltips
function initTooltips() {
    const tooltipIcons = document.querySelectorAll('.tooltip-icon');
    
    tooltipIcons.forEach(icon => {
        const tooltip = icon.getAttribute('data-tooltip');
        
        // Crear tooltip personalizado
        icon.addEventListener('mouseenter', function(e) {
            // Remover tooltip existente si hay uno
            if (icon.tooltipElement) {
                icon.tooltipElement.remove();
            }
            
            const tooltipElement = document.createElement('div');
            tooltipElement.className = 'custom-tooltip';
            tooltipElement.textContent = tooltip;
            tooltipElement.style.cssText = `
                position: fixed;
                background: rgba(0, 0, 0, 0.95);
                color: white;
                padding: 10px 14px;
                border-radius: 8px;
                font-size: 13px;
                max-width: 280px;
                z-index: 10000;
                pointer-events: none;
                white-space: normal;
                word-wrap: break-word;
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.1);
            `;
            
            document.body.appendChild(tooltipElement);
            
            // Posicionar tooltip de forma responsiva
            const rect = icon.getBoundingClientRect();
            const tooltipRect = tooltipElement.getBoundingClientRect();
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            
            let left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
            let top = rect.top - tooltipRect.height - 10;
            
            // Ajustar si se sale por la izquierda
            if (left < 10) {
                left = 10;
            }
            
            // Ajustar si se sale por la derecha
            if (left + tooltipRect.width > viewportWidth - 10) {
                left = viewportWidth - tooltipRect.width - 10;
            }
            
            // Ajustar si se sale por arriba
            if (top < 10) {
                top = rect.bottom + 10;
            }
            
            // Ajustar si se sale por abajo
            if (top + tooltipRect.height > viewportHeight - 10) {
                top = rect.top - tooltipRect.height - 10;
            }
            
            tooltipElement.style.left = left + 'px';
            tooltipElement.style.top = top + 'px';
            
            // Guardar referencia para remover
            icon.tooltipElement = tooltipElement;
        });
        
        icon.addEventListener('mouseleave', function() {
            if (icon.tooltipElement) {
                icon.tooltipElement.remove();
                icon.tooltipElement = null;
            }
        });
        
        // También remover tooltip al hacer scroll o resize
        window.addEventListener('scroll', function() {
            if (icon.tooltipElement) {
                icon.tooltipElement.remove();
                icon.tooltipElement = null;
            }
        });
        
        window.addEventListener('resize', function() {
            if (icon.tooltipElement) {
                icon.tooltipElement.remove();
                icon.tooltipElement = null;
            }
        });
    });
}

// Exportar función de tooltips
window.initTooltips = initTooltips;

// Funcionalidad del menú móvil
function initMobileMenu() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.querySelector('.sidebar');
    
    // Limpiar estado del sidebar al cargar nueva página
    localStorage.removeItem('sidebarOpen');
    
    // Crear overlay si no existe
    let overlay = document.querySelector('.sidebar-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }
    
    // Función para cerrar sidebar
    function closeSidebar() {
        if (sidebar && sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
            if (overlay) overlay.classList.remove('show');
            document.body.style.overflow = '';
            if (sidebar) {
                sidebar.style.left = '0';
                sidebar.style.transform = 'translateX(-100%)';
            }
        }
    }
    
    // Función para abrir sidebar
    function openSidebar() {
        if (sidebar && !sidebar.classList.contains('show')) {
            sidebar.classList.add('show');
            if (overlay) overlay.classList.add('show');
            document.body.style.overflow = 'hidden';
            if (sidebar) {
                sidebar.style.left = '0';
                sidebar.style.transform = 'translateX(0)';
            }
        }
    }
    
    if (mobileMenuToggle && sidebar) {
        // Remover event listeners anteriores para evitar duplicados
        const newMobileMenuToggle = mobileMenuToggle.cloneNode(true);
        mobileMenuToggle.parentNode.replaceChild(newMobileMenuToggle, mobileMenuToggle);
        
        newMobileMenuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (sidebar.classList.contains('show')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
        
        // Agregar botón de cerrar dentro del sidebar
        const sidebarHeader = sidebar.querySelector('.sidebar-header');
        if (sidebarHeader && !sidebarHeader.querySelector('.close-sidebar-btn')) {
            const closeBtn = document.createElement('button');
            closeBtn.className = 'close-sidebar-btn';
            closeBtn.innerHTML = '<i class="fas fa-times"></i>';
            closeBtn.style.cssText = `
                position: absolute;
                top: 10px;
                right: 10px;
                background: none;
                border: none;
                color: var(--text-primary);
                font-size: 1.2rem;
                cursor: pointer;
                padding: 5px;
                border-radius: 4px;
                transition: all 0.3s ease;
                z-index: 1001;
            `;
            closeBtn.onclick = closeSidebar;
            sidebarHeader.style.position = 'relative';
            sidebarHeader.appendChild(closeBtn);
        }
        
        // Cerrar sidebar al hacer clic en overlay
        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
        }
        
        // Cerrar sidebar con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('show')) {
                closeSidebar();
            }
        });
        
        // Cerrar sidebar al hacer clic en enlaces del sidebar
        const sidebarLinks = sidebar.querySelectorAll('a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                // Pequeño delay para que se vea la transición
                setTimeout(() => {
                    closeSidebar();
                }, 100);
            });
        });
        
        // Manejar cambio de tamaño de ventana
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                const currentWidth = window.innerWidth;
                const isMobile = currentWidth <= 768;
                
                // Cerrar sidebar si cambiamos de móvil a desktop
                if (!isMobile && sidebar.classList.contains('show')) {
                    closeSidebar();
                }
            }, 250);
        });
    }
    
    // Asegurar que el sidebar esté cerrado al cargar la página
    if (sidebar) {
        sidebar.classList.remove('show');
        sidebar.style.transform = 'translateX(-100%)';
    }
    if (overlay) {
        overlay.classList.remove('show');
    }
    document.body.style.overflow = '';
}

// Inicializar menú móvil cuando se carga el DOM
document.addEventListener('DOMContentLoaded', function() {
    initMobileMenu();
    
    // Asegurar que el sidebar esté en el estado correcto
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (sidebar) {
        sidebar.classList.remove('show');
        sidebar.style.transform = 'translateX(-100%)';
        sidebar.style.left = '0';
    }
    
    if (overlay) {
        overlay.classList.remove('show');
    }
    
    document.body.style.overflow = '';
});

// Limpiar estado del sidebar antes de que se descargue la página
window.addEventListener('beforeunload', function() {
    localStorage.removeItem('sidebarOpen');
    localStorage.removeItem('wasMobile');
});

// Exportar función del menú móvil
window.initMobileMenu = initMobileMenu;

// ========================================
// FUNCIONES ESPECÍFICAS DE INVENTARIO
// ========================================

// ========================================
// FUNCIONES DE PRODUCTOS
// ========================================

// Función para inicializar filtros de productos (versión mejorada)
function initProductFilters() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    const stockFilter = document.getElementById('stockFilter');
    const sortFilter = document.getElementById('sortFilter');
    const productRows = document.querySelectorAll('.product-row');

    // Función para filtrar productos
    function filterProducts() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedCategory = categoryFilter.value.toLowerCase();
        const selectedStatus = statusFilter.value.toLowerCase();
        const selectedStock = stockFilter.value.toLowerCase();
        const selectedSort = sortFilter.value;

        let visibleProducts = [];

        productRows.forEach(row => {
            const nombre = row.dataset.nombre;
            const categoria = row.dataset.categoria.toLowerCase();
            const estado = row.dataset.estado.toLowerCase();
            const stock = parseInt(row.dataset.stock);
            const precio = parseFloat(row.dataset.precio);
            const codigoBarras = row.dataset.codigoBarras.toLowerCase();
            const codigoInterno = row.dataset.codigoInterno.toLowerCase();
            const proveedor = row.dataset.proveedor;

            // Filtros
            let showRow = true;

            // Filtro de búsqueda
            if (searchTerm && !(
                nombre.includes(searchTerm) ||
                categoria.includes(searchTerm) ||
                codigoBarras.includes(searchTerm) ||
                codigoInterno.includes(searchTerm) ||
                proveedor.includes(searchTerm)
            )) {
                showRow = false;
            }

            // Filtro de categoría
            if (selectedCategory && categoria !== selectedCategory) {
                showRow = false;
            }

            // Filtro de estado
            if (selectedStatus && estado !== selectedStatus) {
                showRow = false;
            }

            // Filtro de stock
            if (selectedStock) {
                if (selectedStock === 'con_stock' && stock <= 0) {
                    showRow = false;
                } else if (selectedStock === 'sin_stock' && stock > 0) {
                    showRow = false;
                } else if (selectedStock === 'stock_bajo' && stock > 5) {
                    showRow = false;
                }
            }

            if (showRow) {
                row.style.display = '';
                visibleProducts.push(row);
            } else {
                row.style.display = 'none';
            }
        });

        // Ordenar productos
        sortProducts(visibleProducts, selectedSort);
    }

    // Función para ordenar productos
    function sortProducts(products, sortType) {
        const tbody = document.getElementById('productsTableBody');
        
        products.sort((a, b) => {
            switch (sortType) {
                case 'fecha_desc':
                    return 0; // Ya están ordenados por fecha
                case 'fecha_asc':
                    return 0; // Mantener orden original
                case 'nombre_asc':
                    return a.dataset.nombre.localeCompare(b.dataset.nombre);
                case 'nombre_desc':
                    return b.dataset.nombre.localeCompare(a.dataset.nombre);
                case 'precio_asc':
                    return parseFloat(a.dataset.precio) - parseFloat(b.dataset.precio);
                case 'precio_desc':
                    return parseFloat(b.dataset.precio) - parseFloat(a.dataset.precio);
                case 'stock_asc':
                    return parseInt(a.dataset.stock) - parseInt(b.dataset.stock);
                case 'stock_desc':
                    return parseInt(b.dataset.stock) - parseInt(a.dataset.stock);
                default:
                    return 0;
            }
        });

        // Reordenar en el DOM
        products.forEach(product => {
            tbody.appendChild(product);
        });
    }

    // Event listeners
    if (searchInput) searchInput.addEventListener('input', filterProducts);
    if (categoryFilter) categoryFilter.addEventListener('change', filterProducts);
    if (statusFilter) statusFilter.addEventListener('change', filterProducts);
    if (stockFilter) stockFilter.addEventListener('change', filterProducts);
    if (sortFilter) sortFilter.addEventListener('change', filterProducts);
}

// Función para nuevo movimiento
function nuevoMovimiento(id, nombre) {
    Swal.fire({
        title: 'Nuevo Movimiento',
        text: `¿Qué tipo de movimiento deseas realizar para "${nombre}"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Entrada',
        cancelButtonText: 'Salida',
        showDenyButton: true,
        denyButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `nuevo-movimiento.php?producto=${id}&tipo=entrada`;
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            window.location.href = `nuevo-movimiento.php?producto=${id}&tipo=salida`;
        }
    });
}

// Función para descargar PDF de productos
function descargarPDFProductos() {
    window.open('../../src/inventario/generar-pdf-productos.php', '_blank');
}

// ========================================
// FUNCIONES DE CATEGORÍAS
// ========================================

// Función para inicializar filtros de categorías
function initCategoryFilters() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const productFilter = document.getElementById('productFilter');
    const sortFilter = document.getElementById('sortFilter');
    const categoryRows = document.querySelectorAll('.category-row');

    // Función para filtrar categorías
    function filterCategories() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedStatus = statusFilter.value.toLowerCase();
        const selectedProduct = productFilter.value.toLowerCase();
        const selectedSort = sortFilter.value;

        let visibleCategories = [];

        categoryRows.forEach(row => {
            const nombre = row.dataset.nombre;
            const descripcion = row.dataset.descripcion;
            const estado = row.dataset.estado.toLowerCase();
            const productos = parseInt(row.dataset.productos);

            // Filtros
            let showRow = true;

            // Filtro de búsqueda
            if (searchTerm && !(
                nombre.includes(searchTerm) ||
                descripcion.includes(searchTerm)
            )) {
                showRow = false;
            }

            // Filtro de estado
            if (selectedStatus && estado !== selectedStatus) {
                showRow = false;
            }

            // Filtro de productos
            if (selectedProduct) {
                if (selectedProduct === 'con_productos' && productos === 0) {
                    showRow = false;
                } else if (selectedProduct === 'sin_productos' && productos > 0) {
                    showRow = false;
                }
            }

            if (showRow) {
                row.style.display = '';
                visibleCategories.push(row);
            } else {
                row.style.display = 'none';
            }
        });

        // Ordenar categorías
        sortCategories(visibleCategories, selectedSort);
    }

    // Función para ordenar categorías
    function sortCategories(categories, sortType) {
        const tbody = document.getElementById('categoriesTableBody');
        
        categories.sort((a, b) => {
            switch (sortType) {
                case 'nombre_asc':
                    return a.dataset.nombre.localeCompare(b.dataset.nombre);
                case 'nombre_desc':
                    return b.dataset.nombre.localeCompare(a.dataset.nombre);
                case 'fecha_desc':
                    return parseInt(b.dataset.fecha) - parseInt(a.dataset.fecha);
                case 'fecha_asc':
                    return parseInt(a.dataset.fecha) - parseInt(b.dataset.fecha);
                case 'productos_desc':
                    return parseInt(b.dataset.productos) - parseInt(a.dataset.productos);
                case 'productos_asc':
                    return parseInt(a.dataset.productos) - parseInt(a.dataset.productos);
                default:
                    return 0;
            }
        });

        // Reordenar en el DOM
        categories.forEach(category => {
            tbody.appendChild(category);
        });
    }

    // Event listeners
    if (searchInput) searchInput.addEventListener('input', filterCategories);
    if (statusFilter) statusFilter.addEventListener('change', filterCategories);
    if (productFilter) productFilter.addEventListener('change', filterCategories);
    if (sortFilter) sortFilter.addEventListener('change', filterCategories);
}

// Funciones de acciones de categorías
function verCategoria(id) {
    window.location.href = `ver-categoria.php?id=${id}`;
}

function editarCategoria(id) {
    window.location.href = `editar-categoria.php?id=${id}`;
}

function eliminarCategoria(id, nombre, totalProductos) {
    if (totalProductos > 0) {
        // Si tiene productos, mostrar advertencia especial
        Swal.fire({
            title: '⚠️ Categoría con productos',
            html: `
                <p>La categoría <strong>"${nombre}"</strong> tiene <strong>${totalProductos} productos</strong> asociados.</p>
                <p>Si la eliminas, todos los productos quedarán sin categoría.</p>
                <p><strong>¿Estás seguro de que deseas continuar?</strong></p>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar de todos modos',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                enviarEliminacionCategoria(id);
            }
        });
    } else {
        // Si no tiene productos, confirmación normal
        Swal.fire({
            title: '¿Eliminar categoría?',
            text: `¿Estás seguro de que deseas eliminar "${nombre}"? Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                enviarEliminacionCategoria(id);
            }
        });
    }
}

function enviarEliminacionCategoria(id) {
    // Crear formulario para enviar la solicitud
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '../../src/inventario/categorias/eliminar-categoria.php';
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'id_categoria';
    input.value = id;
    
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

// Función para descargar PDF de categorías
function descargarPDFCategorias() {
    window.open('../../src/inventario/generar-pdf-categorias.php', '_blank');
}

// ========================================
// FUNCIONES DE MOVIMIENTOS
// ========================================

// Función para ver detalles del movimiento
function verMovimiento(id) {
    window.location.href = `ver-movimiento.php?id=${id}`;
}

// Función para descargar PDF de movimientos
function descargarPDFMovimientos() {
    window.open('../../src/inventario/generar-pdf-movimientos.php', '_blank');
}

// ========================================
// FUNCIONES DE AJUSTES
// ========================================

// Función para aplicar filtros automáticamente (ajustes)
function applyFilters() {
    const form = document.querySelector('.filters-section');
    if (form) {
        form.submit();
    }
}

// Función para inicializar filtros automáticos de ajustes
function initAdjustmentFilters() {
    const searchInput = document.querySelector('input[name="producto"]');
    const filterSelect = document.querySelector('select[name="tipo"]');
    const dateDesde = document.querySelector('input[name="fecha_desde"]');
    const dateHasta = document.querySelector('input[name="fecha_hasta"]');
    
    // Event listeners para filtros automáticos
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(applyFilters, 500); // Esperar 500ms después de que el usuario deje de escribir
        });
    }
    
    if (filterSelect) {
        filterSelect.addEventListener('change', applyFilters);
    }
    
    if (dateDesde) {
        dateDesde.addEventListener('change', applyFilters);
    }
    
    if (dateHasta) {
        dateHasta.addEventListener('change', applyFilters);
    }
}

// Función para descargar PDF de ajustes
function descargarPDFAjustes() {
    window.open('../../src/inventario/generar-pdf-ajustes.php', '_blank');
}

// ========================================
// FUNCIONES DE GRÁFICAS
// ========================================

// Inicializar gráfica de distribución
function initDistribucionChart() {
    const ctx = document.getElementById('distribucionChart');
    if (!ctx) {
        console.log('Canvas no encontrado');
        return;
    }

    try {
        // Los datos se pasan desde PHP, por lo que esta función debe ser llamada
        // desde el archivo PHP con los datos específicos
        console.log('Función initDistribucionChart llamada');
    } catch (error) {
        console.error('Error al inicializar la gráfica:', error);
        ctx.style.display = 'none';
        const placeholder = document.createElement('div');
        placeholder.style.cssText = 'height: 200px; background: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6c757d;';
        placeholder.textContent = 'Error al cargar la gráfica';
        ctx.parentNode.appendChild(placeholder);
    }
}

// ========================================
// EXPORTAR FUNCIONES DE INVENTARIO
// ========================================

// Productos
window.initProductFilters = initProductFilters;
window.nuevoMovimiento = nuevoMovimiento;
window.descargarPDFProductos = descargarPDFProductos;

// Categorías
window.initCategoryFilters = initCategoryFilters;
window.verCategoria = verCategoria;
window.editarCategoria = editarCategoria;
window.eliminarCategoria = eliminarCategoria;
window.descargarPDFCategorias = descargarPDFCategorias;

// Movimientos
window.verMovimiento = verMovimiento;
window.descargarPDFMovimientos = descargarPDFMovimientos;

// Ajustes
window.applyFilters = applyFilters;
window.initAdjustmentFilters = initAdjustmentFilters;
window.descargarPDFAjustes = descargarPDFAjustes;

// Gráficas
window.initDistribucionChart = initDistribucionChart; 