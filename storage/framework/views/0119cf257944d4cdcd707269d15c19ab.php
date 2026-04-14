
<div class="card filtros-card">
    <div class="card-header filtros-header">
        <h5 class="mb-0">
            <i class="fas fa-filter me-2"></i> Filtros de Búsqueda
        </h5>
    </div>
    <div class="card-body filtros-body">
        <form action="<?php echo e(route('admin.adopciones.index')); ?>" method="GET">
            <div class="row g-3">
                <!-- Búsqueda por nombre de mascota o adoptante -->
                <div class="col-md-4">
                    <label for="busqueda" class="form-label">Buscar:</label>
                    <input type="text" class="form-control" id="busqueda" name="busqueda"
                           value="<?php echo e(request('busqueda')); ?>" placeholder="Mascota o adoptante...">
                </div>

                <!-- Filtro por estado -->
                <div class="col-md-3">
                    <label for="estado" class="form-label">Estado:</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="">Todos los estados</option>
                        <?php $__currentLoopData = $estados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $estado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($estado); ?>"
                                <?php echo e(request('estado') == $estado ? 'selected' : ''); ?>>
                                <?php if($estado == 'en_proceso'): ?> En Proceso
                                <?php elseif($estado == 'aprobada'): ?> Aprobada
                                <?php elseif($estado == 'completada'): ?> Completada
                                <?php elseif($estado == 'rechazada'): ?> Rechazada
                                <?php elseif($estado == 'cancelada'): ?> Cancelada
                                <?php else: ?> <?php echo e($estado); ?>

                                <?php endif; ?>
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <!-- Filtro por mascota -->
                <div class="col-md-3">
                    <label for="mascota_id" class="form-label">Mascota:</label>
                    <select class="form-select" id="mascota_id" name="mascota_id">
                        <option value="">Todas las mascotas</option>
                        <?php $__currentLoopData = $mascotas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mascota): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($mascota->id); ?>"
                                <?php echo e(request('mascota_id') == $mascota->id ? 'selected' : ''); ?>>
                                <?php echo e($mascota->nombre ?? $mascota->Nombre_mascota); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <!-- Filtro por usuario -->
                <div class="col-md-2">
                    <label for="usuario_id" class="form-label">Adoptante:</label>
                    <select class="form-select" id="usuario_id" name="usuario_id">
                        <option value="">Todos</option>
                        <?php $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usuario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($usuario->id); ?>"
                                <?php echo e(request('usuario_id') == $usuario->id ? 'selected' : ''); ?>>
                                <?php echo e($usuario->name ?? ($usuario->Nombre_1 ?? '')); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <!-- Filtro por fecha desde -->
                <div class="col-md-3">
                    <label for="fecha_desde" class="form-label">Desde:</label>
                    <input type="date" class="form-control" id="fecha_desde" name="fecha_desde"
                           value="<?php echo e(request('fecha_desde')); ?>">
                </div>

                <!-- Filtro por fecha hasta -->
                <div class="col-md-3">
                    <label for="fecha_hasta" class="form-label">Hasta:</label>
                    <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta"
                           value="<?php echo e(request('fecha_hasta')); ?>">
                </div>

                <!-- Botones -->
                <div class="col-md-12">
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i> Aplicar Filtros
                        </button>
                        <a href="<?php echo e(route('admin.adopciones.index')); ?>" class="btn btn-outline-primary">
                            <i class="fas fa-times me-2"></i> Limpiar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\adopciones\partials\index\filters.blade.php ENDPATH**/ ?>