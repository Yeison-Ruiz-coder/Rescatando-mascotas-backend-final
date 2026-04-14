<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Detalles de la Adopción #<?php echo e($adopcion->id); ?></h5>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Información de la mascota -->
            <div class="col-md-6">
                <h6 class="border-bottom pb-2 mb-3">Información de la Mascota</h6>
                <table class="table table-sm">
                    <tr>
                        <th width="150">Nombre:</th>
                        <td>
                            <a href="<?php echo e(route('admin.mascotas.show', $adopcion->mascota->id)); ?>" class="text-primary">
                                <?php echo e($adopcion->mascota->nombre ?? 'N/A'); ?>

                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th>Especie:</th>
                        <td><?php echo e($adopcion->mascota->especie ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <th>Raza:</th>
                        <td><?php echo e($adopcion->mascota->raza ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <th>Edad:</th>
                        <td><?php echo e($adopcion->mascota->edad ?? 'N/A'); ?> años</td>
                    </tr>
                </table>
            </div>

            <!-- Información del adoptante -->
            <div class="col-md-6">
                <h6 class="border-bottom pb-2 mb-3">Información del Adoptante</h6>
                <table class="table table-sm">
                    <tr>
                        <th width="150">Nombre:</th>
                        <td>
                            <a href="<?php echo e(route('admin.usuarios.show', $adopcion->adoptante->id)); ?>" class="text-primary">
                                <?php echo e($adopcion->adoptante->name ?? 'N/A'); ?>

                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?php echo e($adopcion->adoptante->email ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <th>Teléfono:</th>
                        <td><?php echo e($adopcion->adoptante->telefono ?? 'N/A'); ?></td>
                    </tr>
                </table>
            </div>

            <!-- Información de la adopción -->
            <div class="col-12 mt-3">
                <h6 class="border-bottom pb-2 mb-3">Detalles de la Adopción</h6>
                <div class="row">
                    <div class="col-md-3">
                        <strong>Fecha Adopción:</strong><br>
                        <?php echo e($adopcion->fecha_adopcion ? $adopcion->fecha_adopcion->format('d/m/Y') : 'No definida'); ?>

                    </div>
                    <div class="col-md-3">
                        <strong>Estado:</strong><br>
                        <?php echo $__env->make('admin.adopciones.partials.show.estado_badge', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Fecha Cierre:</strong><br>
                        <?php echo e($adopcion->fecha_cierre ? $adopcion->fecha_cierre->format('d/m/Y') : 'Pendiente'); ?>

                    </div>
                    <div class="col-md-3">
                        <strong>Fundación:</strong><br>
                        <?php echo e($adopcion->fundacion->nombre ?? 'N/A'); ?>

                    </div>
                </div>

                <?php if($adopcion->razon_rechazo): ?>
                <div class="alert alert-danger mt-3">
                    <strong>Razón de Rechazo/Cancelación:</strong><br>
                    <?php echo e($adopcion->razon_rechazo); ?>

                </div>
                <?php endif; ?>
            </div>

            <!-- Información del administrador -->
            <div class="col-12 mt-3 text-muted small">
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Creado por:</strong> <?php echo e($adopcion->administrador->name ?? 'Sistema'); ?>

                        (<?php echo e($adopcion->created_at ? $adopcion->created_at->format('d/m/Y H:i') : ''); ?>)
                    </div>
                    <div class="col-md-6">
                        <strong>Última actualización:</strong> <?php echo e($adopcion->updated_at ? $adopcion->updated_at->format('d/m/Y H:i') : ''); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\adopciones\show.blade.php ENDPATH**/ ?>