<?php $__env->startSection('content'); ?>
<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <h1>Apadrinamientos</h1>
            <a href="<?php echo e(route('admin.apadrinamientos.create')); ?>" class="btn btn-primary mb-3">
                Crear Apadrinamiento
            </a>

            <?php if($apadrinamientos->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Mascota</th>
                                <th>Monto Mensual</th>
                                <th>Frecuencia</th>
                                <th>Estado</th>
                                <th>Fecha Inicio</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $apadrinamientos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $apadrinamiento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($apadrinamiento->id); ?></td>
                                    <td><?php echo e($apadrinamiento->usuario->name ?? 'N/A'); ?></td>
                                    <td><?php echo e($apadrinamiento->mascota->nombre ?? 'N/A'); ?></td>
                                    <td>$<?php echo e(number_format($apadrinamiento->monto_mensual, 2)); ?></td>
                                    <td><?php echo e(ucfirst($apadrinamiento->frecuencia)); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo e($apadrinamiento->estado == 'activo' ? 'success' : 'warning'); ?>">
                                            <?php echo e(ucfirst($apadrinamiento->estado)); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e($apadrinamiento->fecha_inicio->format('d/m/Y')); ?></td>
                                    <td>
                                        <a href="<?php echo e(route('apadrinamientos.show', $apadrinamiento)); ?>" class="btn btn-sm btn-info">Ver</a>
                                        <a href="<?php echo e(route('apadrinamientos.edit', $apadrinamiento)); ?>" class="btn btn-sm btn-warning">Editar</a>
                                        <form action="<?php echo e(route('apadrinamientos.destroy', $apadrinamiento)); ?>" method="POST" style="display:inline;">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro?')">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="d-flex justify-content-center">
                    <?php echo e($apadrinamientos->links()); ?>

                </div>
            <?php else: ?>
                <div class="alert alert-info" role="alert">
                    No hay apadrinamientos registrados.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\apadrinamientos\index.blade.php ENDPATH**/ ?>