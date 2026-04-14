<?php $__env->startSection('content'); ?>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h2>Historial de Donaciones</h2>
            <p class="lead">Aquí verías un listado de las donaciones realizadas.</p>
            <a href="<?php echo e(route('admin.donaciones.index')); ?>" class="btn btn-primary btn-lg">
                Hacer una Nueva Donación
            </a>
        </div>
    </div>

    
    
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\donaciones\index.blade.php ENDPATH**/ ?>