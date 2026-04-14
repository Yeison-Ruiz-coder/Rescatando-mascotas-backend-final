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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="text-white">Eventos para Mascotas</h1>
                    <a href="<?php echo e(route('admin.eventos.create')); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Crear Nuevo Evento
                    </a>
                </div>
            </div>
        </div>

        <?php if($eventos->count() > 0): ?>
            <div class="row">
                <?php $__currentLoopData = $eventos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card event-card h-100">
                        <?php if($evento->imagen_url): ?>
                            <img src="<?php echo e(asset('storage' . str_replace('/storage', '', $evento->imagen_url))); ?>"
                                 class="card-img-top"
                                 alt="<?php echo e($evento->Nombre_evento); ?>">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/300x200/4F46E5/FFFFFF?text=Evento+Mascotas"
                                 class="card-img-top"
                                 alt="Imagen por defecto">
                        <?php endif; ?>

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo e($evento->Nombre_evento); ?></h5>
                            <p class="card-text flex-grow-1"><?php echo e(Str::limit($evento->Descripcion, 100)); ?></p>

                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt"></i> <?php echo e($evento->Lugar_evento); ?>

                                    </small>
                                    <span class="event-date">
                                        <?php echo e(\Carbon\Carbon::parse($evento->Fecha_evento)->format('d M Y')); ?>

                                    </span>
                                </div>

                                <div class="d-flex gap-2">
                                    <a href="<?php echo e(route('eventos.show', $evento)); ?>" class="btn btn-outline-primary btn-sm flex-fill">
                                        <i class="fas fa-eye"></i> Ver Detalles
                                    </a>

                                    <!-- Botón Eliminar con confirmación -->
                                    <form action="<?php echo e(route('eventos.destroy', $evento)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                                onclick="return confirm('¿Estás seguro de que quieres eliminar este evento?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
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
                    <div class="alert alert-info text-center">
                        <h4>No hay eventos programados</h4>
                        <p>¡Crea el primer evento para mascotas!</p>
                        <a href="<?php echo e(route('admin.eventos.create')); ?>" class="btn btn-primary">
                            Crear Primer Evento
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php $__env->stopSection(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\eventos\index.blade.php ENDPATH**/ ?>