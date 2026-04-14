<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/pages/mascotas/index.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-3 px-lg-5 py-4">
    <!-- Header -->
    <?php echo $__env->make('admin.mascotas.partials.index.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Acciones Principales -->
    <?php echo $__env->make('admin.mascotas.partials.index.actions', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Panel de Filtros -->
    <?php echo $__env->make('components.modules.mascotas.filters', ['especies' => $especies ?? []], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Estadísticas Rápidas -->
    <?php if(!request()->anyFilled(['especie', 'estado', 'raza'])): ?>
        <?php echo $__env->make('admin.mascotas.partials.index.stats', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>

    <!-- Grid de Mascotas -->
    <div class="row">
        <?php $__empty_1 = true; $__currentLoopData = $mascotas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mascota): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                <?php echo $__env->make('components.modules.mascotas.card', ['mascota' => $mascota], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php echo $__env->make('admin.mascotas.partials.index.empty-state', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endif; ?>
    </div>

    <!-- Paginación -->
    <?php if($mascotas->hasPages()): ?>
        <?php echo $__env->make('admin.mascotas.partials.index.pagination', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\mascotas\index.blade.php ENDPATH**/ ?>