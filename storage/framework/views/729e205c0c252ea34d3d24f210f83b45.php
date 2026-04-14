<?php $__env->startSection('title', 'Gestión de Solicitudes - Admin'); ?>


<?php $__env->startSection('content'); ?>
<section class="admin-panel">

    <!-- Header -->
    <?php echo $__env->make('admin.solicitud.partials.index.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Alertas -->
    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <i class="fa-solid fa-check-circle"></i> <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-error">
            <i class="fa-solid fa-exclamation-circle"></i> <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <!-- Acciones -->
    <?php echo $__env->make('admin.solicitud.partials.index.actions', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Tabla -->
    <?php echo $__env->make('admin.solicitud.partials.index.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Paginación -->
    <?php echo $__env->make('admin.solicitud.partials.index.pagination', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

</section>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/pages/solicitud/index.js')); ?>"></script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/pages/solicitud/index.css')); ?>">
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\solicitud\index.blade.php ENDPATH**/ ?>