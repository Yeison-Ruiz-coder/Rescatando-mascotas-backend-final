<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/pages/adopciones/exito.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-exito animacion-entrada text-center">
                <div class="card-body p-5">
                    <div class="exito-icono mb-4">
                        <i class="fas fa-check-circle fa-5x text-success"></i>
                    </div>

                    <h2 class="mb-3">¡Solicitud Enviada con Éxito!</h2>

                    <p class="lead mb-4">
                        Hemos recibido tu solicitud para adoptar a <strong><?php echo e($solicitud->solicitable->nombre_mascota); ?></strong>
                    </p>

                    <div class="info-solicitud mb-4 p-4 bg-light rounded">
                        <h5>Detalles de la solicitud</h5>
                        <p><strong>Número de solicitud:</strong> #<?php echo e($solicitud->id); ?></p>
                        <p><strong>Fecha:</strong> <?php echo e($solicitud->created_at->format('d/m/Y H:i')); ?></p>
                        <p><strong>Estado:</strong>
                            <span class="badge bg-warning text-dark">Pendiente de revisión</span>
                        </p>
                    </div>

                    <div class="proceso-siguiente mb-4">
                        <h5>¿Qué sigue ahora?</h5>
                        <ol class="text-start">
                            <li>La fundación revisará tu solicitud en los próximos días</li>
                            <li>Te contactarán al <strong><?php echo e($solicitud->email_solicitante); ?></strong> para coordinar una entrevista</li>
                            <li>Si todo va bien, coordinarán la visita de seguimiento</li>
                            <li>¡Podrás conocer a tu nuevo amigo en persona!</li>
                        </ol>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-clock me-2"></i>
                        <strong>Tiempo estimado:</strong> El proceso puede tomar de 5 a 7 días hábiles.
                    </div>

                    <div class="mt-4">
                        <a href="<?php echo e(route('public.mascotas.index')); ?>" class="btn btn-turquesa me-2">
                            <i class="fas fa-paw me-2"></i>Ver más mascotas
                        </a>
                        <a href="<?php echo e(route('public.home')); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-2"></i>Ir al inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\adopciones\partials\exito.blade.php ENDPATH**/ ?>