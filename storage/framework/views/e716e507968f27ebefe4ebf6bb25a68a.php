<?php $__env->startSection('content'); ?>

<div class="container mt-5">
    <div class="row">

        <div class="col-md-6 header-text">
            <h1 class="text-start">Dona y cambia</h1>
            <h1 class="text-start mb-4">una vida</h1>
            <p>Cada aporte ayuda a alimentar, cuidar y proteger</p>
        </div>

        <div class="col-md-6 d-flex justify-content-center align-items-center">

        </div>
    </div>

    <div class="row justify-content-center mt-4">
        <div class="col-md-7">
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\donaciones\create.blade.php ENDPATH**/ ?>