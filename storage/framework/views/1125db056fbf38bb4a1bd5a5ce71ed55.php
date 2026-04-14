<div class="info-mascota-solicitud animacion-entrada">
    <div class="row align-items-center">
        <div class="col-md-3 text-center mb-3 mb-md-0">
            <?php if($mascota->foto_principal): ?>
            <img src="<?php echo e(Storage::url($mascota->foto_principal)); ?>"
                 class="foto-mascota-solicitud"
                 alt="<?php echo e($mascota->nombre_mascota); ?>">
            <?php else: ?>
            <div class="placeholder-foto-mascota">
                <i class="fas fa-paw"></i>
            </div>
            <?php endif; ?>
        </div>
        <div class="col-md-9">
            <h4 class="nombre-mascota-solicitud"><?php echo e($mascota->nombre_mascota); ?></h4>
            <div class="mb-3">
                <span class="badge badge-especie me-1"><?php echo e($mascota->especie ?? 'No especificada'); ?></span>
                <span class="badge badge-genero me-1"><?php echo e($mascota->genero ?? 'No especificado'); ?></span>
                <span class="badge badge-edad"><?php echo e($mascota->edad_aprox ?? '?'); ?> años</span>

                <!-- Mostrar razas si existen -->
                <?php if($mascota->razas && $mascota->razas->count() > 0): ?>
                    <span class="badge bg-secondary me-1">
                        <?php $__currentLoopData = $mascota->razas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $raza): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php echo e($raza->nombre_raza); ?><?php if(!$loop->last): ?>, <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </span>
                <?php endif; ?>
            </div>
            <p class="text-muted mb-0">
                <i class="fas fa-quote-left me-1 text-turquesa"></i>
                <?php echo e(Str::limit($mascota->descripcion ?? 'Sin descripción disponible', 200)); ?>

            </p>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\adopciones\partials\mascota_info.blade.php ENDPATH**/ ?>