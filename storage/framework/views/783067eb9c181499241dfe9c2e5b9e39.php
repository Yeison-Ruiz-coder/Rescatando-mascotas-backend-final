
<div class="card shadow-sm tabla-adopciones">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="80">ID</th>
                        <th>Mascota</th>
                        <th>Adoptante</th>
                        <th width="120">Fecha Adopción</th>
                        <th>Estado</th>
                        <th width="150" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $adopciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $adopcion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="fw-bold text-turquesa">#<?php echo e($adopcion->id); ?></td>
                        <td>
                            <?php if($adopcion->mascota): ?>
                                <i class="fas fa-paw me-2 text-fucsia"></i>
                                <?php echo e($adopcion->mascota->nombre ?? $adopcion->mascota->Nombre_mascota); ?>

                            <?php else: ?>
                                <span class="text-muted">
                                    <i class="fas fa-question-circle me-2"></i>Mascota no encontrada
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($adopcion->adoptante): ?>
                                <i class="fas fa-user me-2 text-turquesa"></i>
                                <?php echo e($adopcion->adoptante->name ?? ($adopcion->adoptante->Nombre_1 ?? '')); ?>

                                <?php echo e($adopcion->adoptante->Apellido_1 ?? ''); ?>

                            <?php else: ?>
                                <span class="text-muted">
                                    <i class="fas fa-question-circle me-2"></i>Usuario no encontrado
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <i class="fas fa-calendar me-2 text-muted"></i>
                            <?php echo e($adopcion->fecha_adopcion ? $adopcion->fecha_adopcion->format('d/m/Y') : 'No definida'); ?>

                        </td>
                        <td>
                            <span class="badge badge-estado
                                <?php if($adopcion->estado == 'completada'): ?> bg-success
                                <?php elseif($adopcion->estado == 'en_proceso'): ?> bg-warning text-dark
                                <?php elseif($adopcion->estado == 'aprobada'): ?> bg-info
                                <?php elseif($adopcion->estado == 'rechazada'): ?> bg-danger
                                <?php else: ?> bg-secondary
                                <?php endif; ?>">
                                <?php if($adopcion->estado == 'en_proceso'): ?> En Proceso
                                <?php elseif($adopcion->estado == 'aprobada'): ?> Aprobada
                                <?php elseif($adopcion->estado == 'completada'): ?> Completada
                                <?php elseif($adopcion->estado == 'rechazada'): ?> Rechazada
                                <?php elseif($adopcion->estado == 'cancelada'): ?> Cancelada
                                <?php else: ?> <?php echo e($adopcion->estado); ?>

                                <?php endif; ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm btn-group-acciones">
                                <a href="<?php echo e(route('admin.adopciones.show', $adopcion->id)); ?>"
                                   class="btn btn-outline-info" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo e(route('admin.adopciones.edit', $adopcion->id)); ?>"
                                   class="btn btn-outline-primary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="<?php echo e(route('admin.adopciones.destroy', $adopcion->id)); ?>"
                                      method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-outline-danger"
                                            title="Eliminar"
                                            onclick="return confirm('¿Estás seguro de eliminar esta adopción?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\adopciones\partials\index\table.blade.php ENDPATH**/ ?>