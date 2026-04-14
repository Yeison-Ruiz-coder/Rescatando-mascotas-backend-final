<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($evento->Nombre_evento); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo e(asset('css/pages/eventos/show.css')); ?>" rel="stylesheet">
    <!-- Agregar Font Awesome para los iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    
    <?php $__env->startSection('content'); ?>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-body">
                        <?php if($evento->imagen_url): ?>
                            <img src="<?php echo e(asset('storage' . str_replace('/storage', '', $evento->imagen_url))); ?>"
                                 class="event-image mb-4"
                                 alt="<?php echo e($evento->Nombre_evento); ?>">
                        <?php endif; ?>

                        <h1 class="card-title"><?php echo e($evento->Nombre_evento); ?></h1>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-2">
                                    <strong class="me-2"><i class="fas fa-map-marker-alt"></i> Lugar:</strong>
                                    <span class="text-muted"><?php echo e($evento->Lugar_evento); ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-2">
                                    <strong class="me-2"><i class="fas fa-calendar-alt"></i> Fecha:</strong>
                                    <span class="text-muted"><?php echo e(\Carbon\Carbon::parse($evento->Fecha_evento)->format('d M Y, h:i A')); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5>Descripción del Evento:</h5>
                            <p class="card-text"><?php echo e($evento->Descripcion); ?></p>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="<?php echo e(route('admin.eventos.index')); ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Volver a Eventos
                                </a>
                            </div>

                            <div class="d-flex gap-2">
                                <!-- Botón Editar -->
                                <a href="<?php echo e(route('eventos.edit', $evento)); ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-edit"></i> Editar
                                </a>

                                <!-- Botón Eliminar -->
                                <form action="<?php echo e(route('eventos.destroy', $evento)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-outline-danger"
                                            onclick="return confirm('¿Estás seguro de que quieres eliminar este evento?')">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="mt-3 text-end">
                            <small class="text-muted">
                                Creado el: <?php echo e($evento->created_at->format('d M Y')); ?>

                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php $__env->stopSection(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\eventos\show.blade.php ENDPATH**/ ?>