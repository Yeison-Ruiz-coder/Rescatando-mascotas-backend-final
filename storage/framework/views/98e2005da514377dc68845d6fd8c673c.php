
<div class="card shadow mb-4">
    <div class="card-header fw-bold bg-turquesa text-white">
        <i class="fas fa-syringe me-2"></i>Historial de Vacunación
    </div>
    <div class="card-body">
        <?php if($mascota->vacunas && $mascota->vacunas->count() > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Vacuna</th>
                            <th>Frecuencia</th>
                            <th>Fecha Aplicación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $mascota->vacunas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vacuna): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <i class="fas fa-syringe text-info me-2"></i>
                                <?php echo e($vacuna->nombre_vacuna); ?>

                            </td>
                            <td>
                                <?php if($vacuna->frecuencia_dias): ?>
                                    Cada <?php echo e($vacuna->frecuencia_dias); ?> días
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($vacuna->pivot && $vacuna->pivot->fecha_aplicacion): ?>
                                    <?php echo e(\Carbon\Carbon::parse($vacuna->pivot->fecha_aplicacion)->format('d/m/Y')); ?>

                                <?php else: ?>
                                    <span class="text-muted">No registrada</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted mb-0">
                <i class="fas fa-info-circle me-2"></i>
                Aún no se ha registrado el historial de vacunas.
            </p>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-foreve\resources\views/admin/mascotas/partials/show/vaccines.blade.php ENDPATH**/ ?>