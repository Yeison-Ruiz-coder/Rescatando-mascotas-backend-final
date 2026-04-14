<?php if($position === 'sidebar'): ?>
<!-- Versión para sidebar -->
<div class="card card-mascota">
    <div class="card-body text-center py-4">
        <h5 class="card-title text-fucsia mb-3">
            <i class="fas fa-heart me-2"></i>¿Te enamoraste de <?php echo e($mascota->nombre_mascota); ?>?
        </h5>
        <p class="card-text text-muted mb-4">Da el primer paso para darle un hogar lleno de amor</p>
        <a href="<?php echo e(route('public.adopciones.solicitar', $mascota->id)); ?>"
           class="btn btn-fucsia btn-lg px-4 py-2 mb-2">
            <i class="fas fa-heart me-2"></i>Solicitar Adopción
        </a>
        <p class="text-muted small">
            <i class="fas fa-shield-alt me-1"></i>Proceso 100% gratuito y responsable
        </p>
    </div>
</div>
<?php elseif($position === 'main'): ?>
<!-- Versión para área principal -->
<div class="d-grid gap-3 mt-4">
    <button class="btn btn-fucsia btn-lg py-3 fw-bold"
            data-bs-toggle="modal"
            data-bs-target="#modalAdopcion">
        <i class="fas fa-heart me-2"></i>¡Quiero Adoptar!
    </button>
    <a href="<?php echo e(route('public.mascotas.index')); ?>"
       class="btn btn-outline-turquesa btn-lg py-3">
        <i class="fas fa-paw me-2"></i>Ver Otras Mascotas
    </a>
</div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\mascotas\partials\show\action-buttons.blade.php ENDPATH**/ ?>