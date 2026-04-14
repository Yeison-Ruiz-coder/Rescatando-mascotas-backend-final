<div class="alert-card error mb-4">
    <div class="alert-header">
        <i class="fas fa-exclamation-circle text-danger me-2"></i>
        <h5 class="mb-0">Error al Registrar</h5>
        <button type="button" class="btn-close" onclick="closeAlert(this)"></button>
    </div>
    <div class="alert-body">
        <p class="mb-3"><?php echo e(session('error')); ?></p>
        <div class="alert-actions">
            <button type="button" class="btn btn-outline-danger" onclick="closeAlert(this)">
                <i class="fas fa-times me-1"></i>Entendido
            </button>
        </div>
    </div>
</div>

<script>
function closeAlert(element) {
    element.closest('.alert-card').style.display = 'none';
}

// Cerrar alerta automáticamente después de 8 segundos
setTimeout(() => {
    const alert = document.querySelector('.alert-card');
    if (alert) alert.style.display = 'none';
}, 8000);
</script><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\cards\error-registro.blade.php ENDPATH**/ ?>