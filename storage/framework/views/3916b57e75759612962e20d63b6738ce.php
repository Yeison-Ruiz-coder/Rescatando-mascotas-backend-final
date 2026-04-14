
<div class="table-container">
    <table class="solicitudes-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tipo</th>
                <th>Solicitante</th>
                <th>Item</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $solicitudes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $solicitud): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>#<?php echo e($solicitud->id); ?></td>
                    <td>
                        <span class="tipo-badge tipo-<?php echo e($solicitud->tipo_solicitud); ?>">
                            <?php echo e(ucfirst($solicitud->tipo_solicitud)); ?>

                        </span>
                    </td>
                    <td>
                        <?php if($solicitud->usuario): ?>
                            <?php echo e($solicitud->usuario->name ?? $solicitud->usuario->nombre_completo); ?>

                        <?php else: ?>
                            <?php echo e($solicitud->nombre_solicitante); ?>

                            <?php if($solicitud->esAdopcion() && $apellido = $solicitud->getDatoAdopcion('apellido_solicitante')): ?>
                                <?php echo e($apellido); ?>

                            <?php endif; ?>
                            <br>
                            <small class="text-muted"><?php echo e($solicitud->email_solicitante); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($solicitud->solicitable): ?>
                            <?php if($solicitud->solicitable_type == 'App\Models\Mascota'): ?>
                                <span class="item-info">
                                    <i class="fa-solid fa-paw"></i>
                                    <?php echo e($solicitud->solicitable->nombre ?? 'Mascota'); ?>

                                </span>
                            <?php else: ?>
                                <span class="item-info">
                                    <?php echo e(class_basename($solicitud->solicitable_type)); ?>

                                </span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($solicitud->fecha_solicitud->format('d/m/Y')); ?></td>
                    <td>
                        <?php
                            $estado_class = strtolower(str_replace('_', '-', $solicitud->estado ?? 'pendiente'));
                            $estado_texto = str_replace('_', ' ', ucfirst($solicitud->estado ?? 'pendiente'));
                        ?>
                        <span class="status-badge <?php echo e($estado_class); ?>">
                            <?php echo e($estado_texto); ?>

                        </span>
                    </td>
                    <td class="actions">
                        <a href="<?php echo e(route('admin.solicitudes.show', $solicitud)); ?>" class="btn-action view-btn" title="Ver">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        <a href="<?php echo e(route('admin.solicitudes.edit', $solicitud)); ?>" class="btn-action edit-btn" title="Editar">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <form action="<?php echo e(route('admin.solicitudes.destroy', $solicitud)); ?>" method="POST" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn-action delete-btn" title="Eliminar"
                                onclick="return confirm('¿Estás seguro de eliminar esta solicitud?')">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" class="no-data">
                        <i class="fa-solid fa-clipboard-list fa-3x"></i>
                        <p>No hay solicitudes registradas</p>
                        <a href="<?php echo e(route('admin.solicitudes.create')); ?>" class="btn-action primary-btn">
                            Crear Primera Solicitud
                        </a>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\solicitud\partials\index\table.blade.php ENDPATH**/ ?>