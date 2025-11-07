</div>
    </div>
    
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Scripts personalizados -->
    <script>
        /**
         * LabClinics Connect - Scripts del Admin
         */
        
        // ========================================
        // Toggle sidebar en móvil
        // ========================================
        const toggleButton = document.getElementById('toggleSidebar');
        const sidebar = document.querySelector('.sidebar');
        
        if (toggleButton && sidebar) {
            toggleButton.addEventListener('click', function() {
                sidebar.classList.toggle('show');
            });
            
            // Cerrar sidebar al hacer clic fuera en móvil
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(e.target) && !toggleButton.contains(e.target)) {
                        sidebar.classList.remove('show');
                    }
                }
            });
        }
        
        // ========================================
        // Auto-cerrar alertas después de 5 segundos
        // ========================================
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(function(alert) {
                try {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                } catch(e) {
                    // Si hay error al cerrar, simplemente ocultar
                    alert.style.display = 'none';
                }
            });
        }, 5000);
        
        // ========================================
        // Confirmar acciones importantes
        // ========================================
        
        // Confirmar cierre de sesión
        const logoutLinks = document.querySelectorAll('a[href*="logout.php"]');
        logoutLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('¿Está seguro de cerrar sesión?')) {
                    e.preventDefault();
                    return false;
                }
            });
        });
        
        // Confirmar eliminaciones
        const deleteLinks = document.querySelectorAll('a[href*="eliminar"]');
        deleteLinks.forEach(link => {
            if (!link.hasAttribute('onclick')) {
                link.addEventListener('click', function(e) {
                    if (!confirm('¿Está seguro de eliminar este elemento?')) {
                        e.preventDefault();
                        return false;
                    }
                });
            }
        });
        
        // ========================================
        // Highlight active menu item
        // ========================================
        const currentPage = window.location.pathname.split('/').pop();
        const menuItems = document.querySelectorAll('.menu-item');
        
        menuItems.forEach(item => {
            const href = item.getAttribute('href');
            if (href && href.includes(currentPage)) {
                item.classList.add('active');
            }
        });
        
        // ========================================
        // Sistema de notificaciones toast
        // ========================================
        function showNotification(message, type = 'info') {
            const toastContainer = document.getElementById('toastContainer') || createToastContainer();
            
            const toastId = 'toast-' + Date.now();
            const iconMap = {
                'success': 'bi-check-circle-fill',
                'danger': 'bi-x-circle-fill',
                'warning': 'bi-exclamation-triangle-fill',
                'info': 'bi-info-circle-fill'
            };
            
            const colorMap = {
                'success': '#00C853',
                'danger': '#dc3545',
                'warning': '#ffc107',
                'info': '#0066FF'
            };
            
            const toastHTML = `
                <div class="toast align-items-center border-0" id="${toastId}" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body" style="border-left: 4px solid ${colorMap[type]};">
                            <i class="bi ${iconMap[type]} me-2" style="color: ${colorMap[type]};"></i>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHTML);
            
            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
            toast.show();
            
            toastElement.addEventListener('hidden.bs.toast', function() {
                toastElement.remove();
            });
        }
        
        function createToastContainer() {
            const container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
            return container;
        }
        
        // Hacer disponible globalmente
        window.showNotification = showNotification;
        
        // ========================================
        // Tooltips de Bootstrap
        // ========================================
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        
        // ========================================
        // Prevenir doble submit en formularios
        // ========================================
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
                    
                    // Reactivar después de 5 segundos por seguridad
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = submitBtn.dataset.originalText || 'Enviar';
                    }, 5000);
                }
            });
        });
        
        // ========================================
        // Contador de caracteres en textareas
        // ========================================
        const textareasWithCounter = document.querySelectorAll('textarea[maxlength]');
        textareasWithCounter.forEach(textarea => {
            const maxLength = textarea.getAttribute('maxlength');
            const counter = document.createElement('small');
            counter.className = 'text-muted d-block mt-1';
            counter.textContent = `0 / ${maxLength} caracteres`;
            textarea.parentNode.appendChild(counter);
            
            textarea.addEventListener('input', function() {
                const length = this.value.length;
                counter.textContent = `${length} / ${maxLength} caracteres`;
                counter.style.color = length > maxLength * 0.9 ? '#dc3545' : '#6c757d';
            });
        });
        
        // ========================================
        // Previsualización de imágenes
        // ========================================
        const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
        imageInputs.forEach(input => {
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        let preview = input.parentNode.querySelector('.image-preview');
                        if (!preview) {
                            preview = document.createElement('div');
                            preview.className = 'image-preview mt-2';
                            input.parentNode.appendChild(preview);
                        }
                        preview.innerHTML = `
                            <img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                            <button type="button" class="btn btn-sm btn-danger mt-1" onclick="this.parentNode.remove(); this.closest('input[type=file]').value='';">
                                <i class="bi bi-trash"></i> Quitar
                            </button>
                        `;
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
        
        // ========================================
        // Validación en tiempo real
        // ========================================
        const emailInputs = document.querySelectorAll('input[type="email"]');
        emailInputs.forEach(input => {
            input.addEventListener('blur', function() {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (this.value && !emailRegex.test(this.value)) {
                    this.classList.add('is-invalid');
                    let feedback = this.nextElementSibling;
                    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                        feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback';
                        this.parentNode.appendChild(feedback);
                    }
                    feedback.textContent = 'Por favor, ingrese un email válido';
                } else {
                    this.classList.remove('is-invalid');
                }
            });
        });
        
        // ========================================
        // Búsqueda en tablas
        // ========================================
        const searchInputs = document.querySelectorAll('[data-table-search]');
        searchInputs.forEach(searchInput => {
            const tableId = searchInput.dataset.tableSearch;
            const table = document.querySelector(tableId);
            
            if (table) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const rows = table.querySelectorAll('tbody tr');
                    
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                });
            }
        });
        
        // ========================================
        // Copiar al portapapeles
        // ========================================
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showNotification('Copiado al portapapeles', 'success');
            }).catch(err => {
                showNotification('Error al copiar', 'danger');
            });
        }
        window.copyToClipboard = copyToClipboard;
        
        // ========================================
        // Información del sistema en consola
        // ========================================
        console.log('%c<?php echo SITE_NAME; ?>', 'color: #0066FF; font-size: 20px; font-weight: bold;');
        console.log('%cSistema de Gestión de Referencias Médicas', 'color: #666; font-size: 12px;');
        console.log('%c━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'color: #0066FF;');
        console.log('Versión: %c<?php echo SITE_VERSION; ?>', 'color: #00C853; font-weight: bold;');
        console.log('Usuario: %c<?php echo getUserName(); ?>', 'color: #0066FF; font-weight: bold;');
        console.log('Página: %c' + currentPage, 'color: #666;');
        console.log('Laboratorio: %c<?php echo LAB_NAME; ?>', 'color: #0066FF; font-weight: bold;');
        console.log('%c━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'color: #0066FF;');
        
        // ========================================
        // Detectar inactividad (30 minutos)
        // ========================================
        let inactivityTimer;
        const INACTIVITY_LIMIT = 30 * 60 * 1000; // 30 minutos
        
        function resetInactivityTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(() => {
                if (confirm('Su sesión está por expirar debido a inactividad. ¿Desea continuar?')) {
                    resetInactivityTimer();
                } else {
                    window.location.href = '<?php echo SITE_URL; ?>/admin/logout.php';
                }
            }, INACTIVITY_LIMIT);
        }
        
        // Eventos que resetean el timer
        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'].forEach(event => {
            document.addEventListener(event, resetInactivityTimer, true);
        });
        
        // Iniciar timer
        resetInactivityTimer();
        
        // ========================================
        // Verificar conexión a internet
        // ========================================
        window.addEventListener('online', () => {
            showNotification('Conexión restaurada', 'success');
        });
        
        window.addEventListener('offline', () => {
            showNotification('Sin conexión a internet', 'warning');
        });
        
        // ========================================
        // Inicialización completa
        // ========================================
        document.addEventListener('DOMContentLoaded', function() {
            console.log('%c✓ Sistema inicializado correctamente', 'color: #00C853; font-weight: bold;');
            
            // Verificar elementos críticos
            const criticalElements = {
                'Sidebar': document.querySelector('.sidebar'),
                'Topbar': document.querySelector('.topbar'),
                'Main Content': document.querySelector('.main-content')
            };
            
            Object.entries(criticalElements).forEach(([name, element]) => {
                if (!element) {
                    console.warn(`⚠️ Elemento crítico no encontrado: ${name}`);
                }
            });
        });
    </script>
</body>
</html>
