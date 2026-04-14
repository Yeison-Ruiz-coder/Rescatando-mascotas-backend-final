<?php $__env->startSection('title', 'Adopciones'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/style.css')); ?>?v=<?php echo e(time()); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/pages/adopciones/public-index.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <h1 class="text-center">Listado de mascotas en adopción</h1>

    <?php if(isset($mascotas) && $mascotas->count() > 0): ?>
        <div class="row mt-4">
            <?php $__currentLoopData = $mascotas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mascota): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-4 mb-4">
                    <?php echo $__env->make('public.adopciones.partials.mascota-card', ['mascota' => $mascota], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php else: ?>
        <div class="sin-mascotas-container">
            <i class="fas fa-paw"></i>
            <h3>No hay mascotas en adopción</h3>
            <p>Pronto tendremos nuevos amigos buscando hogar</p>
            <a href="<?php echo e(route('public.mascotas.index')); ?>" class="btn-volver-mascotas">
                <i class="fas fa-arrow-left"></i>
                Ver todas las mascotas
            </a>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<style>
    /* TUS ESTILOS DIRECTOS AQUÍ - COPIA TODO EL CSS QUE TE DI */
    :root {
        --color-turquesa: #764ba2;
        --color-fucsia: #667eea;
        /* ... resto de variables ... */
    }

    .card-mascota-moderna {
        background: rgba(255,255,255,0.05);
        border-radius: 20px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .badge-moderno {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 15px;
        background: var(--color-turquesa);
        color: white;
        margin-right: 5px;
    }
    /* ... más estilos ... */
</style>

<!-- resto de tu contenido -->
<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\adopciones\index.blade.php ENDPATH**/ ?>