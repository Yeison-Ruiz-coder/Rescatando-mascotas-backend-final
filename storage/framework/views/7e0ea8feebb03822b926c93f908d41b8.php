<?php $__env->startSection('title', 'Mis Solicitudes de Adopción'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <h1 class="mb-4">Mis Solicitudes de Adopción</h1>

    <?php if($solicitudes->count() > 0): ?>
        <div class="row">
            <?php $__currentLoopData = $solicitudes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $solicitud): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-6 mb-4">
                    <div class="card solicitud-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <?php if($solicitud->solicitable && $solicitud->solicitable->foto_principal): ?>
                                    <img src="<?php echo e(Storage::url($solicitud->solicitable->foto_principal)); ?>"
                                         class="solicitud-mascota-img me-3"
                                         alt="<?php echo e($solicitud->solicitable->nombre_mascota); ?>">
                                <?php else: ?>
                                    <div class="solicitud-mascota-placeholder me-3">
                                        <i class="fas fa-paw"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h5 class="mb-1"><?php echo e($solicitud->solicitable->nombre_mascota ?? 'Mascota'); ?></h5>
                                    <p class="text-muted mb-0">Solicitud #<?php echo e($solicitud->id); ?></p>
                                </div>
                            </div>

                            <div class="mb-3">
                                <span class="badge bg-<?php echo e($solicitud->estado == 'pendiente' ? 'warning' : ($solicitud->estado == 'aprobada' ? 'success' : ($solicitud->estado == 'rechazada' ? 'danger' : 'info'))); ?>">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $solicitud->estado))); ?>

                                </span>
                                <span class="badge bg-secondary ms-2">
                                    <?php echo e($solicitud->created_at->format('d/m/Y')); ?>

                                </span>
                            </div>

                            <p class="card-text"><?php echo e(Str::limit($solicitud->contenido, 100)); ?></p>

                            <a href="<?php echo e(route('public.adopciones.solicitud-detalle', $solicitud->id)); ?>"
                               class="btn btn-turquesa btn-sm">
                                Ver detalles
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="d-flex justify-content-center mt-4">
            <?php echo e($solicitudes->links()); ?>

        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
            <h3 class="text-muted">No tienes solicitudes</h3>
            <p class="text-muted">Aún no has solicitado ninguna adopción</p>
            <a href="<?php echo e(route('public.adopciones.index')); ?>" class="btn btn-turquesa mt-3">
                <i class="fas fa-paw me-2"></i>Ver mascotas disponibles
            </a>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\adopciones\mis-solicitudes.blade.php ENDPATH**/ ?>