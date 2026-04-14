<?php $__env->startSection('title', 'Detalle de Solicitud #' . $solicitud->id); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-turquesa text-white">
                    <h4 class="mb-0">Detalle de Solicitud #<?php echo e($solicitud->id); ?></h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Información de la Mascota</h5>
                            <p><strong>Nombre:</strong> <?php echo e($solicitud->solicitable->nombre_mascota ?? 'N/A'); ?></p>
                            <p><strong>Especie:</strong> <?php echo e($solicitud->solicitable->especie ?? 'N/A'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5>Estado de la Solicitud</h5>
                            <?php
                                $estadoClass = [
                                    'pendiente' => 'warning',
                                    'en_revision' => 'info',
                                    'aprobada' => 'success',
                                    'rechazada' => 'danger',
                                    'completada' => 'secondary'
                                ][$solicitud->estado] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?php echo e($estadoClass); ?> p-2">
                                <?php echo e(ucfirst(str_replace('_', ' ', $solicitud->estado))); ?>

                            </span>
                            <p class="mt-2"><small>Creada: <?php echo e($solicitud->created_at->format('d/m/Y H:i')); ?></small></p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Tus Datos</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nombre:</strong> <?php echo e($solicitud->nombre_solicitante); ?> <?php echo e($solicitud->getDatoAdopcion('apellido_solicitante')); ?></p>
                            <p><strong>Email:</strong> <?php echo e($solicitud->email_solicitante); ?></p>
                            <p><strong>Teléfono:</strong> <?php echo e($solicitud->telefono_solicitante); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Documento:</strong> <?php echo e($solicitud->getDatoAdopcion('documento_identidad')); ?></p>
                            <p><strong>Dirección:</strong> <?php echo e($solicitud->getDatoAdopcion('direccion')); ?></p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Detalles de la Solicitud</h5>
                    <p><strong>Experiencia con mascotas:</strong> <?php echo e($solicitud->getDatoAdopcion('experiencia_mascotas')); ?></p>
                    <p><strong>Tipo de vivienda:</strong> <?php echo e($solicitud->getDatoAdopcion('tipo_vivienda')); ?></p>
                    <p><strong>Motivo:</strong></p>
                    <div class="p-3 bg-light rounded">
                        <?php echo e($solicitud->contenido); ?>

                    </div>

                    <hr>

                    <h5 class="mb-3">Compromisos</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <span class="badge bg-<?php echo e($solicitud->getDatoAdopcion('compromiso_cuidado') ? 'success' : 'danger'); ?>">
                                Cuidado responsable
                            </span>
                        </div>
                        <div class="col-md-4">
                            <span class="badge bg-<?php echo e($solicitud->getDatoAdopcion('compromiso_esterilizacion') ? 'success' : 'danger'); ?>">
                                Esterilización
                            </span>
                        </div>
                        <div class="col-md-4">
                            <span class="badge bg-<?php echo e($solicitud->getDatoAdopcion('compromiso_seguimiento') ? 'success' : 'danger'); ?>">
                                Seguimiento
                            </span>
                        </div>
                    </div>

                    <?php if($solicitud->estado === 'rechazada' && $solicitud->razon_rechazo): ?>
                        <hr>
                        <div class="alert alert-danger">
                            <h5>Razón del rechazo:</h5>
                            <p><?php echo e($solicitud->razon_rechazo); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if($solicitud->estado === 'aprobada'): ?>
                        <hr>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            ¡Tu solicitud ha sido aprobada! Pronto te contactarán para coordinar la adopción.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="<?php echo e(route('public.adopciones.mis-solicitudes')); ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Volver a mis solicitudes
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\adopciones\solicitud-detalle.blade.php ENDPATH**/ ?>