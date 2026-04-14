<!-- Badges informativos -->
<div class="mb-4">
    <span class="badge badge-especie me-2 mb-2">
        <i class="fas fa-paw me-1"></i><?php echo e($mascota->especie ?? 'No especificada'); ?>

    </span>
    <span class="badge badge-genero me-2 mb-2">
        <i class="fas fa-venus-mars me-1"></i><?php echo e($mascota->genero ?? 'No especificado'); ?>

    </span>
    <span class="badge bg-secondary me-2 mb-2 px-3 py-2">
        <i class="fas fa-birthday-cake me-1"></i><?php echo e($mascota->edad_aprox ?? '?'); ?> años
    </span>
</div>

<!-- Información básica -->
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="info-circle d-flex align-items-center">
            <i class="fas fa-paw icono me-3"></i>
            <div>
                <small class="text-muted d-block">Especie</small>
                <strong class="text-turquesa"><?php echo e($mascota->especie ?? 'No especificada'); ?></strong>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="info-circle d-flex align-items-center">
            <i class="fas fa-venus-mars icono me-3"></i>
            <div>
                <small class="text-muted d-block">Género</small>
                <strong class="text-turquesa"><?php echo e($mascota->genero ?? 'No especificado'); ?></strong>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="info-circle d-flex align-items-center">
            <i class="fas fa-birthday-cake icono me-3"></i>
            <div>
                <small class="text-muted d-block">Edad</small>
                <strong class="text-turquesa"><?php echo e($mascota->edad_aprox ?? '?'); ?> años</strong>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="info-circle d-flex align-items-center">
            <i class="fas fa-dna icono me-3"></i>
            <div>
                <small class="text-muted d-block">Raza</small>
                <strong class="text-turquesa">
                    <?php if($mascota->razas && $mascota->razas->count() > 0): ?>
                        <?php $__currentLoopData = $mascota->razas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $raza): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php echo e($raza->nombre_raza); ?><?php if(!$loop->last): ?>, <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        No especificada
                    <?php endif; ?>
                </strong>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\mascotas\partials\show\basic-info.blade.php ENDPATH**/ ?>