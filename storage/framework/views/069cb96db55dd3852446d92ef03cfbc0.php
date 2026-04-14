<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/pages/adopciones/index.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container fade-in py-4 mb-5">
    <div class="row">
        <div class="col-12">

            <!-- Header -->
            <?php echo $__env->make('admin.adopciones.partials.index.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <!-- Filtros -->
            <?php echo $__env->make('admin.adopciones.partials.index.filters', [
                'estados' => $estados ?? [],
                'mascotas' => $mascotas ?? [],
                'usuarios' => $usuarios ?? []
            ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <!-- Alertas -->
            <?php echo $__env->make('admin.adopciones.partials.index.alerts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <!-- Contador -->
            <?php echo $__env->make('admin.adopciones.partials.index.counter', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <!-- Tabla o Estado Vacío -->
            <?php if($adopciones->count() > 0): ?>
                <?php echo $__env->make('admin.adopciones.partials.index.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php echo $__env->make('admin.adopciones.partials.index.pagination', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php else: ?>
                <?php echo $__env->make('admin.adopciones.partials.index.empty_state', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php endif; ?>

        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\adopciones\index.blade.php ENDPATH**/ ?>