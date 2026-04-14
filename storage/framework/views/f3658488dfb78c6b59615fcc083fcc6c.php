<?php $__env->startSection('title', 'Rescate #<?php echo e($rescate->id); ?> - Rescatando Mascotas Forever'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <h1>Rescate #<?php echo e($rescate->id); ?></h1>
    <div class="row">
        <div class="col-md-8">
            
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\rescates\show.blade.php ENDPATH**/ ?>