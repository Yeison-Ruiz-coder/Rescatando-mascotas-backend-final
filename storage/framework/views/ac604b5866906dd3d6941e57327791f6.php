<?php $__env->startSection('title', 'Reportar - Rescatando Mascotas Forever'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="text-center mb-4">Reportar Situación</h1>

            <form action="<?php echo e(route('reportes.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Enviar Reporte</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\reportes\create.blade.php ENDPATH**/ ?>