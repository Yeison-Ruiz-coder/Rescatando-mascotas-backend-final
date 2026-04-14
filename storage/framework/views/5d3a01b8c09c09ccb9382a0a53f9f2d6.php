
<div class="seccion-info">
    <div class="d-flex align-items-center mb-3">
        <div class="icono-seccion icono-adopcion">
            <i class="fas fa-file-contract fa-lg"></i>
        </div>
        <h5 class="mb-0">Información de la Adopción</h5>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="info-item">
                <strong>Fecha de Adopción:</strong>
                <span class="valor"><?php echo e($adopcion->Fecha_adopcion->format('d/m/Y')); ?></span>
            </div>
            <div class="info-item">
                <strong>Lugar de Adopción:</strong>
                <span class="valor"><?php echo e($adopcion->Lugar_adopcion); ?></span>
            </div>
            <div class="info-item">
                <strong>Estado del proceso:</strong>
                <span class="valor">
                    <span class="badge badge-estado-detalle 
                        <?php if($adopcion->estado == 'Aprobado'): ?> bg-success
                        <?php elseif($adopcion->estado == 'En proceso'): ?> bg-warning
                        <?php else: ?> bg-danger
                        <?php endif; ?>">
                        <?php echo e($adopcion->estado); ?>

                    </span>
                </span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-item">
                <strong>Fecha de Registro:</strong>
                <span class="valor"><?php echo e($adopcion->created_at->format('d/m/Y H:i')); ?></span>
            </div>
            <div class="info-item">
                <strong>Última Actualización:</strong>
                <span class="valor"><?php echo e($adopcion->updated_at->format('d/m/Y H:i')); ?></span>
            </div>
        </div>
    </div>

    <!-- Información Adicional -->
    <?php if($adopcion->fundacion): ?>
    <div class="info-adicional mt-3">
        <div class="info-item">
            <strong>Fundación:</strong>
            <span class="valor"><?php echo e($adopcion->fundacion->Nombre_1); ?></span>
        </div>
        <div class="info-item">
            <strong>Dirección de la Fundación:</strong>
            <span class="valor"><?php echo e($adopcion->fundacion->Direccion); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <?php if($adopcion->administrador): ?>
    <div class="info-adicional mt-3">
        <div class="info-item">
            <strong>Administrador Responsable:</strong>
            <span class="valor"><?php echo e($adopcion->administrador->Nombre_1); ?> <?php echo e($adopcion->administrador->Apellido_1); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <?php if($adopcion->razon_rechazo): ?>
    <div class="alerta-rechazo mt-3">
        <div class="info-item">
            <strong>Razón de Rechazo:</strong>
            <span class="valor"><?php echo e($adopcion->razon_rechazo); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <?php if($adopcion->fecha_cierre): ?>
    <div class="info-adicional mt-3">
        <div class="info-item">
            <strong>Fecha de Cierre:</strong>
            <span class="valor"><?php echo e($adopcion->fecha_cierre->format('d/m/Y')); ?></span>
        </div>
    </div>
    <?php endif; ?>
</div><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\adopciones\partials\show\adopcion_info.blade.php ENDPATH**/ ?>