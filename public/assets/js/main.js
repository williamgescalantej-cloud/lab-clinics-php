/**
 * LABCLINICS - JavaScript Principal
 * Funcionalidades interactivas del sistema
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ====================================
    // AGREGAR/REMOVER HORARIOS DINÁMICOS
    // ====================================
    let contadorHorarios = 1;
    const btnAddHorario = document.getElementById('add-horario');
    const horariosContainer = document.getElementById('horarios-container');
    
    if (btnAddHorario && horariosContainer) {
        // Agregar horario
        btnAddHorario.addEventListener('click', function() {
            const nuevoHorario = `
                <div class="row mb-2 horario-item">
                    <div class="col-md-4">
                        <select class="form-select form-select-sm" name="horarios[${contadorHorarios}][dia]">
                            <option value="">Día...</option>
                            <option value="Lunes">Lunes</option>
                            <option value="Martes">Martes</option>
                            <option value="Miércoles">Miércoles</option>
                            <option value="Jueves">Jueves</option>
                            <option value="Viernes">Viernes</option>
                            <option value="Sábado">Sábado</option>
                            <option value="Domingo">Domingo</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="time" class="form-control form-control-sm" 
                               name="horarios[${contadorHorarios}][inicio]">
                    </div>
                    <div class="col-md-3">
                        <input type="time" class="form-control form-control-sm" 
                               name="horarios[${contadorHorarios}][fin]">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-sm btn-danger remove-horario">❌</button>
                    </div>
                </div>
            `;
            
            horariosContainer.insertAdjacentHTML('beforeend', nuevoHorario);
            contadorHorarios++;
        });
        
        // Remover horario (delegación de eventos)
        horariosContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-horario')) {
                const item = e.target.closest('.horario-item');
                if (horariosContainer.querySelectorAll('.horario-item').length > 1) {
                    item.remove();
                } else {
                    alert('Debe mantener al menos un horario');
                }
            }
        });
    }
    
    // ====================================
    // AGREGAR/REMOVER REDES SOCIALES
    // ====================================
    let contadorRedes = 1;
    const btnAddRed = document.getElementById('add-red');
    const redesContainer = document.getElementById('redes-container');
    
    if (btnAddRed && redesContainer) {
        // Agregar red social
        btnAddRed.addEventListener('click', function() {
            const totalRedes = redesContainer.querySelectorAll('.red-item').length;
            
            if (totalRedes >= 3) {
                alert('Máximo 3 redes sociales permitidas');
                return;
            }
            
            const nuevaRed = `
                <div class="row mb-2 red-item">
                    <div class="col-md-4">
                        <select class="form-select form-select-sm" name="redes[${contadorRedes}][plataforma]">
                            <option value="">Plataforma...</option>
                            <option value="Facebook">Facebook</option>
                            <option value="Instagram">Instagram</option>
                            <option value="WhatsApp">WhatsApp</option>
                            <option value="LinkedIn">LinkedIn</option>
                            <option value="TikTok">TikTok</option>
                            <option value="YouTube">YouTube</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="url" class="form-control form-control-sm" 
                               name="redes[${contadorRedes}][url]" placeholder="URL completa">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-sm btn-danger remove-red">❌</button>
                    </div>
                </div>
            `;
            
            redesContainer.insertAdjacentHTML('beforeend', nuevaRed);
            contadorRedes++;
        });
        
        // Remover red social (delegación de eventos)
        redesContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-red')) {
                const item = e.target.closest('.red-item');
                if (redesContainer.querySelectorAll('.red-item').length > 1) {
                    item.remove();
                } else {
                    alert('Debe mantener al menos una red social o dejar vacía');
                }
            }
        });
    }
    
    // ====================================
    // PREVIEW DE IMAGEN ANTES DE SUBIR
    // ====================================
    const inputFoto = document.getElementById('foto');
    if (inputFoto) {
        inputFoto.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validar tamaño (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('La imagen no debe superar los 5MB');
                    inputFoto.value = '';
                    return;
                }
                
                // Validar tipo
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Solo se permiten imágenes JPG, PNG o GIF');
                    inputFoto.value = '';
                    return;
                }
                
                console.log('✓ Imagen válida:', file.name);
            }
        });
    }
    
    // ====================================
    // CONFIRMACIÓN DE ELIMINACIÓN
    // ====================================
    const botonesEliminar = document.querySelectorAll('[data-confirm-delete]');
    botonesEliminar.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('¿Está seguro de eliminar este elemento?')) {
                e.preventDefault();
            }
        });
    });
    
    // ====================================
    // AUTO-CERRAR ALERTAS DESPUÉS DE 5 SEG
    // ====================================
    const alertas = document.querySelectorAll('.alert:not(.alert-permanent)');
    alertas.forEach(alerta => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alerta);
            bsAlert.close();
        }, 5000);
    });
    
    // ====================================
    // VALIDACIÓN DE FORMULARIOS HTML5
    // ====================================
    const formularios = document.querySelectorAll('.needs-validation');
    formularios.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    console.log('✓ LabClinics JS inicializado correctamente');
});
