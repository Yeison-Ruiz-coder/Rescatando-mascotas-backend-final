<?php if($mascota->fundacion): ?>
<div class="mb-4">
    <h5 class="text-turquesa mb-3 fw-bold">
        <i class="fas fa-home me-2"></i>Fundación Responsable
    </h5>
    <div class="card border-turquesa hover-lift">
        <div class="card-header bg-turquesa text-white py-2">
            <h6 class="mb-0"><?php echo e($mascota->fundacion->Nombre_1); ?></h6>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-center mb-2">
                <i class="fas fa-map-marker-alt text-fucsia me-3"></i>
                <span class="text-muted"><?php echo e($mascota->fundacion->Direccion ?? 'No especificada'); ?></span>
            </div>
            <div class="d-flex align-items-center mb-2">
                <i class="fas fa-phone text-fucsia me-3"></i>
                <span class="text-muted"><?php echo e($mascota->fundacion->Telefono ?? 'No especificado'); ?></span>
            </div>
            <div class="d-flex align-items-center">
                <i class="fas fa-envelope text-fucsia me-3"></i>
                <span class="text-muted"><?php echo e($mascota->fundacion->Email ?? 'No especificado'); ?></span>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\mascotas\partials\show\foundation-info.blade.php ENDPATH**/ ?>