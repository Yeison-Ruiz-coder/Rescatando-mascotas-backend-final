<?php if(session('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <div class="alert-content">
        <i class="fas fa-exclamation-circle alert-icon"></i>
        <div class="alert-text">
            <h5 class="alert-title">¡Error en el Registro!</h5>
            <p class="alert-message"><?php echo e(session('error')); ?></p>
        </div>
    </div>
    <button type="button" class="alert-close" data-bs-dismiss="alert" aria-label="Close">
        <i class="fas fa-times"></i>
    </button>
</div>
<?php endif; ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-foreve\resources\views/partials/alerts/error.blade.php ENDPATH**/ ?>