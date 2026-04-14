<?php $__env->startSection('content'); ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <h2 class="mb-4">Gestionar Donación Mensual</h2>
            <p class="lead">Modifica la información de tu donación periódica (ID: <?php echo e($donation->id ?? 'N/A'); ?>)</p>

            
            
            <?php echo $__env->make('donaciones.partials.form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\donaciones\edit.blade.php ENDPATH**/ ?>