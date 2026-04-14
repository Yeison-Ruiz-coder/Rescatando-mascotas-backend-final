<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Historial de Seguimientos</h5>
    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalSeguimiento">
        <i class="fas fa-plus me-1"></i>Nuevo Seguimiento
    </button>
</div>

<?php if($adopcion->seguimientos->count() > 0): ?>
    <div class="timeline">
        <?php $__currentLoopData = $adopcion->seguimientos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $seguimiento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="timeline-item">
                <div class="timeline-badge
                    <?php if($seguimiento->estado_mascota == 'excelente'): ?> bg-success
                    <?php elseif($seguimiento->estado_mascota == 'bueno'): ?> bg-info
                    <?php elseif($seguimiento->estado_mascota == 'regular'): ?> bg-warning
                    <?php else: ?> bg-danger
                    <?php endif; ?>">
                    <i class="fas fa-paw"></i>
                </div>
                <div class="timeline-content card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h6 class="card-subtitle mb-2 text-muted">
                                <?php echo e($seguimiento->fecha_seguimiento->format('d/m/Y')); ?>

                            </h6>
                            <span class="badge
                                <?php if($seguimiento->estado_mascota == 'excelente'): ?> bg-success
                                <?php elseif($seguimiento->estado_mascota == 'bueno'): ?> bg-info
                                <?php elseif($seguimiento->estado_mascota == 'regular'): ?> bg-warning
                                <?php else: ?> bg-danger
                                <?php endif; ?>">
                                <?php echo e(ucfirst($seguimiento->estado_mascota)); ?>

                            </span>
                        </div>
                        <p class="card-text"><?php echo e($seguimiento->observaciones); ?></p>
                        <small class="text-muted">
                            Registrado por: <?php echo e($seguimiento->realizadoPor->name ?? 'Sistema'); ?>

                        </small>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php else: ?>
    <div class="text-center py-5">
        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
        <h6 class="text-muted">No hay seguimientos registrados</h6>
        <p class="text-muted small">Agrega el primer seguimiento usando el botón superior</p>
    </div>
<?php endif; ?>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline:before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}
.timeline-item {
    position: relative;
    margin-bottom: 20px;
}
.timeline-badge {
    position: absolute;
    left: -30px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    z-index: 1;
}
.timeline-content {
    margin-left: 20px;
}
</style>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\adopciones\partials\show\seguimientos.blade.php ENDPATH**/ ?>