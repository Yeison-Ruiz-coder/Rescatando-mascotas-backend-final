
<div class="card shadow mb-4">
    <div class="card-header fw-bold bg-turquesa text-white">
        <i class="fas fa-paw me-2"></i>Raza / Cruce
    </div>
    <div class="card-body">
        <?php $__empty_1 = true; $__currentLoopData = $mascota->razas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $raza): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <span class="badge bg-info text-white fs-6 me-2 mb-2 p-2"><?php echo e($raza->nombre_raza); ?></span>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-muted mb-0">
                <i class="fas fa-info-circle me-2"></i>
                No hay información de raza registrada.
            </p>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-foreve\resources\views/admin/mascotas/partials/show/breeds.blade.php ENDPATH**/ ?>