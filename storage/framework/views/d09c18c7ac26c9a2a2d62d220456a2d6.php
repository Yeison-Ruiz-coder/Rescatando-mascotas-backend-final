<?php $__env->startSection('title', 'Detalles de Solicitud #' . $solicitud->id); ?>

<?php $__env->startSection('content'); ?>
<section class="admin-panel">

    <!-- Header -->
    <?php echo $__env->make('admin.solicitud.partials.show.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

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

    <!-- Detalles de la Solicitud -->
    <?php echo $__env->make('admin.solicitud.partials.show.details', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Status y Modal -->
    <?php echo $__env->make('admin.solicitud.partials.show.status-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Acciones -->
    <?php echo $__env->make('admin.solicitud.partials.show.actions', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

</section>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/pages/solicitud/show.css')); ?>">
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\solicitud\show.blade.php ENDPATH**/ ?>