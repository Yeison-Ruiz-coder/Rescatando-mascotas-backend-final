<?php $__env->startSection('title', 'Rescates - Rescatando Mascotas Forever'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <h1 class="text-center mb-4">Nuestros Rescates</h1>

    <div class="row">
        <?php $__currentLoopData = $rescates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rescate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\rescates\index.blade.php ENDPATH**/ ?>