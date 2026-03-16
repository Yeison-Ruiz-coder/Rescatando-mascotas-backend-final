<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <!-- Botones de acción -->
    <?php echo $__env->make('admin.mascotas.partials.show.actions', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <div class="row">
        <!-- Columna izquierda: Galería de fotos -->
        <div class="col-lg-5 mb-4">
            <?php echo $__env->make('admin.mascotas.partials.show.gallery', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php echo $__env->make('admin.mascotas.partials.show.management-info', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>

        <!-- Columna derecha: Información detallada -->
        <div class="col-lg-7">
            <?php echo $__env->make('admin.mascotas.partials.show.description', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php echo $__env->make('admin.mascotas.partials.show.technical-details', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php echo $__env->make('admin.mascotas.partials.show.breeds', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php echo $__env->make('admin.mascotas.partials.show.vaccines', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/pages/mascotas/show.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="<?php echo e(asset('js/pages/mascotas/show.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-foreve\resources\views/admin/mascotas/show.blade.php ENDPATH**/ ?>