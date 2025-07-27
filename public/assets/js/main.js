// Dashboard JavaScript Functions

// Dark mode toggle
function initDarkMode() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    const body = document.body;
    
    // Check if user has a saved preference
    const savedDarkMode = localStorage.getItem('darkMode');
    if (savedDarkMode === 'true') {
        body.classList.add('dark-mode');
        updateDarkModeIcon(true);
    }
    
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            body.classList.toggle('dark-mode');
            const isDarkMode = body.classList.contains('dark-mode');
            
            // Save preference to localStorage
            localStorage.setItem('darkMode', isDarkMode);
            
            // Update icon
            updateDarkModeIcon(isDarkMode);
        });
    }
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
    alert('Producto guardado exitosamente!');
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
    // View product
    document.querySelectorAll('.btn-view').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const productName = row.cells[0].querySelector('div div').textContent;
            alert(`Viendo detalles de: ${productName}`);
        });
    });
    
    // Edit product
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const productName = row.cells[0].querySelector('div div').textContent;
            alert(`Editando: ${productName}`);
        });
    });
    
    // Delete product
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const productName = row.cells[0].querySelector('div div').textContent;
            if (confirm(`¿Estás seguro de que quieres eliminar: ${productName}?`)) {
                row.remove();
                alert('Producto eliminado exitosamente');
            }
        });
    });
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
    
    console.log('Dashboard inicializado correctamente');
});

// Export functions for global access
window.openModal = openModal;
window.closeModal = closeModal;
window.saveProduct = saveProduct;
window.filterProducts = filterProducts; 