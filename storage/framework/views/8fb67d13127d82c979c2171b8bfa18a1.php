<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($evento->Nombre_evento); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    

    <?php $__env->startSection('content'); ?>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-body">
                        <?php if($evento->imagen_url): ?>
                            <img src="<?php echo e(asset($evento->imagen_url)); ?>"
                                 class="img-fluid rounded mb-4 w-100"
                                 alt="<?php echo e($evento->Nombre_evento); ?>"
                                 style="max-height: 400px; object-fit: cover;">
                        <?php endif; ?>

                        <h1 class="card-title text-primary"><?php echo e($evento->Nombre_evento); ?></h1>

                        <div class="row mb-4 bg-light rounded p-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-2">
                                    <strong class="me-2"><i class="fas fa-map-marker-alt text-primary"></i> Lugar:</strong>
                                    <span><?php echo e($evento->Lugar_evento); ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-2">
                                    <strong class="me-2"><i class="fas fa-calendar-alt text-primary"></i> Fecha:</strong>
                                    <span><?php echo e(\Carbon\Carbon::parse($evento->Fecha_evento)->format('d M Y, h:i A')); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5 class="text-primary">Descripción del Evento:</h5>
                            <p class="card-text lead"><?php echo e($evento->Descripcion); ?></p>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="<?php echo e(route('eventos.public.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Volver a Eventos
                            </a>
                            <small class="text-muted">
                                Publicado el: <?php echo e($evento->created_at->format('d M Y')); ?>

                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php $__env->stopSection(); ?>
</body>
</html>

<?php echo $__env->make('public.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\eventos\show.blade.php ENDPATH**/ ?>