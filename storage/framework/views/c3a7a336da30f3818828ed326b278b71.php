<!-- Acciones Principales -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <a href="<?php echo e(route('admin.mascotas.create')); ?>" class="btn btn-success btn-lg">
                <i class="fas fa-plus-circle me-2"></i>Nueva Mascota
            </a>
            
            <div class="d-flex gap-2 flex-wrap">
                <span class="badge bg-turquesa fs-6 p-2">
                    <i class="fas fa-paw me-1"></i>
                    Total: <?php echo e($mascotas->total()); ?> mascotas
                </span>
                <?php if(request()->anyFilled(['especie', 'estado', 'raza'])): ?>
                <a href="<?php echo e(route('admin.mascotas.index')); ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times me-1"></i>Limpiar Filtros
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\mascotas\partials\index\actions.blade.php ENDPATH**/ ?>