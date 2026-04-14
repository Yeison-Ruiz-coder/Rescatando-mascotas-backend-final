<div class="alert-card success mb-4">
    <div class="alert-header">
        <i class="fas fa-check-circle text-success me-2"></i>
        <h5 class="mb-0">¡Mascota Registrada con Éxito!</h5>
        <button type="button" class="btn-close" onclick="closeAlert(this)"></button>
    </div>
    <div class="alert-body">
        <p class="mb-3">La mascota ha sido registrada correctamente en el sistema.</p>
        <div class="alert-actions">
            <a href="<?php echo e(route('admin.mascotas.index')); ?>" class="btn btn-outline-primary me-2">
                <i class="fas fa-list me-1"></i>Volver al Listado
            </a>
            <button type="button" class="btn btn-primary" onclick="continueAdding()">
                <i class="fas fa-plus me-1"></i>Seguir Agregando
            </button>
        </div>
    </div>
</div>

<script>
function closeAlert(element) {
    element.closest('.alert-card').style.display = 'none';
}

function continueAdding() {
    // Oculta la alerta
    document.querySelector('.alert-card').style.display = 'none';
    // Resetear el formulario
    document.querySelector('form').reset();
}

// Cerrar alerta automáticamente después de 8 segundos
setTimeout(() => {
    const alert = document.querySelector('.alert-card');
    if (alert) alert.style.display = 'none';
}, 8000);
</script><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\cards\registro-exitoso.blade.php ENDPATH**/ ?>