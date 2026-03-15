<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos de Mascotas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo e(asset('css/pages/eventos/index.css')); ?>" rel="stylesheet">
    <!-- Agregar Font Awesome para los iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    
    <?php $__env->startSection('content'); ?>
    <div class="container mt-4">
        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <div class="text-center mb-4">
                    <h1 class="text-turquesa">Eventos para Mascotas</h1>
                    <p class="lead text-muted">Descubre los próximos eventos y actividades para mascotas</p>
                </div>
            </div>
        </div>

        <?php if($eventos->count() > 0): ?>
            <div class="row">
                <?php $__currentLoopData = $eventos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card event-card h-100">
                        <?php if($evento->imagen_url): ?>
                            <img src="<?php echo e(asset($evento->imagen_url)); ?>"
                                 class="card-img-top"
                                 alt="<?php echo e($evento->Nombre_evento); ?>"
                                 style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/300x200/1B8E95/FFFFFF?text=Evento+Mascotas"
                                 class="card-img-top event-image"
                                 alt="Imagen por defecto"
                                 style="height: 200px; object-fit: cover;">
                        <?php endif; ?>

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-turquesa"><?php echo e($evento->Nombre_evento); ?></h5>
                            <p class="card-text flex-grow-1 text-muted">
                                <?php echo e(Str::limit($evento->Descripcion, 100)); ?>

                            </p>

                            <div class="mt-auto">
                                <div class="event-info mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-map-marker-alt text-fucsia me-2"></i>
                                        <small class="text-dark"><?php echo e($evento->Lugar_evento); ?></small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar-alt text-fucsia me-2"></i>
                                        <small class="text-dark">
                                            <?php echo e(\Carbon\Carbon::parse($evento->created_at)->format('d M Y')); ?>

                                        </small>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <a href="<?php echo e(route('public.eventos.show', $evento)); ?>"
                                       class="btn btn-turquesa btn-sm">
                                        <i class="fas fa-eye me-1"></i> Ver Detalles del Evento
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info text-center py-5">
                        <i class="fas fa-calendar-times fa-3x text-turquesa mb-3"></i>
                        <h4 class="text-turquesa">No hay eventos programados</h4>
                        <p class="text-muted">Próximamente tendremos eventos para mascotas. ¡Mantente atento!</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php $__env->stopSection(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php echo $__env->make('public.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-foreve\resources\views/public/eventos/index.blade.php ENDPATH**/ ?>