<div class="mb-4">
    <h5 class="text-turquesa mb-3 fw-bold">
        <i class="fas fa-heartbeat me-2"></i>Salud y Cuidados
    </h5>

    <!-- Vacunas -->
    <div class="estado-vacunas d-flex align-items-center mb-3 p-3">
        <i class="fas fa-syringe text-turquesa me-3 fs-5"></i>
        <div>
            <small class="text-muted d-block">Vacunas</small>
            <strong class="text-turquesa">
                <?php if($mascota->vacunas && $mascota->vacunas->count() > 0): ?>
                    <?php echo e($mascota->vacunas->count()); ?> vacunas registradas
                <?php else: ?>
                    En proceso
                <?php endif; ?>
            </strong>
        </div>
    </div>

    <!-- Lugar de rescate -->
    <div class="estado-salud d-flex align-items-center p-3">
        <i class="fas fa-map-marker-alt text-turquesa me-3 fs-5"></i>
        <div>
            <small class="text-muted d-block">Rescatado en</small>
            <strong class="text-turquesa"><?php echo e($mascota->lugar_rescate ?? 'No especificado'); ?></strong>
        </div>
    </div>

    <!-- Condiciones especiales (si aplica) -->
    <?php if($mascota->condiciones_especiales): ?>
    <div class="estado-salud d-flex align-items-center p-3 mt-3 bg-light-info">
        <i class="fas fa-exclamation-triangle text-warning me-3 fs-5"></i>
        <div>
            <small class="text-muted d-block">Cuidados especiales</small>
            <strong class="text-turquesa"><?php echo e($mascota->condiciones_especiales); ?></strong>
        </div>
    </div>
    <?php endif; ?>

    <!-- Apto con niños y otros animales -->
    <div class="row mt-3">
        <?php if($mascota->apto_con_ninos): ?>
        <div class="col-6">
            <span class="badge bg-success p-2 w-100">
                <i class="fas fa-child me-1"></i>Apto con niños
            </span>
        </div>
        <?php endif; ?>
        <?php if($mascota->apto_con_otros_animales): ?>
        <div class="col-6">
            <span class="badge bg-success p-2 w-100">
                <i class="fas fa-dog me-1"></i>Apto con otros animales
            </span>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\mascotas\partials\show\health-info.blade.php ENDPATH**/ ?>