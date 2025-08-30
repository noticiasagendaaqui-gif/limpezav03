// Admin panel JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize admin features
    initDataTables();
    initCharts();
    initDatePickers();
    initStatusUpdates();
    initExportFunctions();
    
    // Initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});

// Initialize data tables with sorting and pagination
function initDataTables() {
    const tables = document.querySelectorAll('.data-table');
    
    tables.forEach(table => {
        // Add sorting functionality
        const headers = table.querySelectorAll('th[data-sortable]');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                sortTable(table, this);
            });
        });
        
        // Add pagination if needed
        addPagination(table);
    });
}

// Sort table by column
function sortTable(table, header) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const columnIndex = Array.from(header.parentNode.children).indexOf(header);
    const isAscending = header.classList.contains('sort-asc');
    
    // Clear all sort classes
    header.parentNode.querySelectorAll('th').forEach(th => {
        th.classList.remove('sort-asc', 'sort-desc');
    });
    
    // Add sort class to current header
    header.classList.add(isAscending ? 'sort-desc' : 'sort-asc');
    
    // Sort rows
    rows.sort((a, b) => {
        const aValue = a.children[columnIndex].textContent.trim();
        const bValue = b.children[columnIndex].textContent.trim();
        
        // Try to parse as numbers or dates
        const aNum = parseFloat(aValue.replace(/[^\d.-]/g, ''));
        const bNum = parseFloat(bValue.replace(/[^\d.-]/g, ''));
        
        let comparison = 0;
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            comparison = aNum - bNum;
        } else {
            comparison = aValue.localeCompare(bValue);
        }
        
        return isAscending ? -comparison : comparison;
    });
    
    // Reorder rows in table
    rows.forEach(row => tbody.appendChild(row));
}

// Add pagination to table
function addPagination(table) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const rowsPerPage = 20;
    
    if (rows.length <= rowsPerPage) return;
    
    let currentPage = 1;
    const totalPages = Math.ceil(rows.length / rowsPerPage);
    
    // Create pagination container
    const paginationContainer = document.createElement('div');
    paginationContainer.className = 'pagination-container flex justify-between items-center mt-4';
    
    // Create pagination info
    const paginationInfo = document.createElement('div');
    paginationInfo.className = 'pagination-info text-sm text-secondary-600';
    
    // Create pagination controls
    const paginationControls = document.createElement('div');
    paginationControls.className = 'pagination-controls flex space-x-2';
    
    paginationContainer.appendChild(paginationInfo);
    paginationContainer.appendChild(paginationControls);
    table.parentNode.appendChild(paginationContainer);
    
    function updatePagination() {
        // Show/hide rows
        rows.forEach((row, index) => {
            const startIndex = (currentPage - 1) * rowsPerPage;
            const endIndex = startIndex + rowsPerPage;
            row.style.display = (index >= startIndex && index < endIndex) ? '' : 'none';
        });
        
        // Update pagination info
        const startItem = (currentPage - 1) * rowsPerPage + 1;
        const endItem = Math.min(currentPage * rowsPerPage, rows.length);
        paginationInfo.textContent = `Mostrando ${startItem} a ${endItem} de ${rows.length} resultados`;
        
        // Update pagination controls
        paginationControls.innerHTML = '';
        
        // Previous button
        const prevBtn = createPaginationButton('Anterior', currentPage > 1, () => {
            currentPage--;
            updatePagination();
        });
        paginationControls.appendChild(prevBtn);
        
        // Page numbers
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            const pageBtn = createPaginationButton(i.toString(), true, () => {
                currentPage = i;
                updatePagination();
            });
            
            if (i === currentPage) {
                pageBtn.classList.add('bg-primary-600', 'text-white');
            }
            
            paginationControls.appendChild(pageBtn);
        }
        
        // Next button
        const nextBtn = createPaginationButton('Próximo', currentPage < totalPages, () => {
            currentPage++;
            updatePagination();
        });
        paginationControls.appendChild(nextBtn);
    }
    
    function createPaginationButton(text, enabled, onClick) {
        const button = document.createElement('button');
        button.textContent = text;
        button.className = `px-3 py-2 text-sm border border-secondary-300 rounded hover:bg-secondary-50 ${enabled ? '' : 'opacity-50 cursor-not-allowed'}`;
        
        if (enabled) {
            button.addEventListener('click', onClick);
        }
        
        return button;
    }
    
    updatePagination();
}

// Initialize charts for dashboard
function initCharts() {
    // Simple chart implementation using Chart.js if available
    if (typeof Chart !== 'undefined') {
        initBookingsChart();
        initServicesChart();
    }
}

// Initialize date pickers
function initDatePickers() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    
    dateInputs.forEach(input => {
        // Set min date to today for future bookings
        if (input.name === 'data_preferida') {
            input.min = new Date().toISOString().split('T')[0];
        }
        
        // Format date display
        input.addEventListener('change', function() {
            if (this.value) {
                const date = new Date(this.value);
                const formatted = date.toLocaleDateString('pt-BR');
                this.setAttribute('data-formatted', formatted);
            }
        });
    });
}

// Initialize status update functionality
function initStatusUpdates() {
    const statusSelects = document.querySelectorAll('.status-select');
    
    statusSelects.forEach(select => {
        select.addEventListener('change', function() {
            const bookingId = this.getAttribute('data-booking-id');
            const newStatus = this.value;
            
            updateBookingStatus(bookingId, newStatus);
        });
    });
}

// Update booking status via AJAX
function updateBookingStatus(bookingId, newStatus) {
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('booking_id', bookingId);
    formData.append('status', newStatus);
    
    fetch('agendamentos.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(html => {
        // Reload page to show updated status
        window.location.reload();
    })
    .catch(error => {
        console.error('Error updating status:', error);
        alert('Erro ao atualizar status');
    });
}

// Initialize export functions
function initExportFunctions() {
    const exportButtons = document.querySelectorAll('.export-btn');
    
    exportButtons.forEach(button => {
        button.addEventListener('click', function() {
            const type = this.getAttribute('data-export-type');
            const table = this.getAttribute('data-table');
            
            exportData(type, table);
        });
    });
}

// Export data to CSV
function exportData(type, tableName) {
    const table = document.querySelector(`[data-table="${tableName}"]`);
    if (!table) return;
    
    if (type === 'csv') {
        exportToCSV(table, tableName);
    } else if (type === 'print') {
        printTable(table);
    }
}

function exportToCSV(table, filename) {
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [];
        const cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length - 1; j++) { // Skip last column (actions)
            let cellData = cols[j].textContent.trim();
            cellData = cellData.replace(/"/g, '""'); // Escape quotes
            row.push('"' + cellData + '"');
        }
        
        csv.push(row.join(','));
    }
    
    downloadCSV(csv.join('\n'), filename);
}

function downloadCSV(csvContent, filename) {
    const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `${filename}_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

function printTable(table) {
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Relatório LimpaBrasil</title>');
    printWindow.document.write('<style>');
    printWindow.document.write('body { font-family: Arial, sans-serif; margin: 20px; }');
    printWindow.document.write('table { width: 100%; border-collapse: collapse; }');
    printWindow.document.write('th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }');
    printWindow.document.write('th { background-color: #f2f2f2; font-weight: bold; }');
    printWindow.document.write('tr:nth-child(even) { background-color: #f9f9f9; }');
    printWindow.document.write('.no-print { display: none; }');
    printWindow.document.write('</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write('<h1>Relatório LimpaBrasil</h1>');
    printWindow.document.write('<p>Gerado em: ' + new Date().toLocaleString('pt-BR') + '</p>');
    
    // Clone table and remove action columns
    const tableClone = table.cloneNode(true);
    const actionCells = tableClone.querySelectorAll('.no-print, th:last-child, td:last-child');
    actionCells.forEach(cell => cell.remove());
    
    printWindow.document.write(tableClone.outerHTML);
    printWindow.document.write('</body></html>');
    
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
}

// Search functionality for admin tables
function initAdminSearch() {
    const searchInputs = document.querySelectorAll('.admin-search');
    
    searchInputs.forEach(input => {
        const tableId = input.getAttribute('data-target');
        const table = document.getElementById(tableId);
        
        if (!table) return;
        
        input.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    });
}

// Dashboard statistics updater
function updateDashboardStats() {
    fetch('../api/dashboard-stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update stat cards
                Object.keys(data.stats).forEach(key => {
                    const element = document.querySelector(`[data-stat="${key}"]`);
                    if (element) {
                        element.textContent = data.stats[key];
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error updating dashboard stats:', error);
        });
}

// Auto-refresh dashboard every 5 minutes
setInterval(updateDashboardStats, 5 * 60 * 1000);

// Utility functions for admin panel
function confirmDelete(message = 'Tem certeza que deseja excluir este item?') {
    return confirm(message);
}

function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
}

// Initialize admin search on load
document.addEventListener('DOMContentLoaded', function() {
    initAdminSearch();
});

// Export admin utilities
window.AdminPanel = {
    confirmDelete,
    showModal,
    hideModal,
    updateDashboardStats,
    exportToCSV,
    printTable
};
