<?php if(session('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <div class="alert-content">
        <i class="fas fa-check-circle alert-icon"></i>
        <div class="alert-text">
            <h5 class="alert-title">¡Mascota Registrada con Éxito!</h5>
            <p class="alert-message">La mascota ha sido registrada correctamente en el sistema.</p>
            <?php if(is_string(session('success')) && session('success') != ''): ?>
                <small class="alert-details"><?php echo e(session('success')); ?></small>
            <?php endif; ?>
        </div>
    </div>
    <button type="button" class="alert-close" data-bs-dismiss="alert" aria-label="Close">
        <i class="fas fa-times"></i>
    </button>
</div>
<?php endif; ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-foreve\resources\views/partials/alerts/success.blade.php ENDPATH**/ ?>