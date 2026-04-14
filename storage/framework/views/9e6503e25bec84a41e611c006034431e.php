<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-paw me-2 text-primary"></i>
            Adopción #<?php echo e($adopcion->id); ?>

        </h2>
        <p class="text-muted mb-0">
            <?php echo e($adopcion->mascota->nombre ?? 'Mascota'); ?> -
            <?php echo e($adopcion->adoptante->name ?? 'Adoptante'); ?>

        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo e(route('admin.adopciones.edit', $adopcion->id)); ?>" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>Editar
        </a>
        <a href="<?php echo e(route('admin.adopciones.index')); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\adopciones\partials\show\header.blade.php ENDPATH**/ ?>