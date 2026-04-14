<?php $__env->startSection('title', 'Donaciones - Rescatando Mascotas Forever'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="text-center mb-4">Realizar Donación</h1>

            <form action="<?php echo e(route('public.donaciones.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Donar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\donaciones\create.blade.php ENDPATH**/ ?>