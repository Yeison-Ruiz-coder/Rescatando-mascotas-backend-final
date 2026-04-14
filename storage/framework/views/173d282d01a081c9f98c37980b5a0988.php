<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/pages/mascotas/public-show.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4 fade-in">

    <!-- Breadcrumb -->
    <?php echo $__env->make('public.mascotas.partials.show.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <div class="row">
        <!-- Columna izquierda - Imágenes -->
        <div class="col-lg-6 mb-4">
            <!-- Galería de imágenes -->
            <?php echo $__env->make('public.mascotas.partials.show.gallery', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <!-- Botón de adopción -->
            <?php echo $__env->make('public.mascotas.partials.show.action-buttons', ['position' => 'sidebar'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>

        <!-- Columna derecha - Información -->
        <div class="col-lg-6">
            <div class="card card-mascota">
                <div class="card-header card-header-turquesa">
                    <h3 class="mb-0 fw-bold"><?php echo e($mascota->nombre_mascota); ?></h3>
                </div>
                <div class="card-body p-4">
                    <!-- Información básica -->
                    <?php echo $__env->make('public.mascotas.partials.show.basic-info', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                    <!-- Descripción -->
                    <?php echo $__env->make('public.mascotas.partials.show.description', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                    <!-- Información de salud -->
                    <?php echo $__env->make('public.mascotas.partials.show.health-info', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                    <!-- Información de la fundación -->
                    <?php echo $__env->make('public.mascotas.partials.show.foundation-info', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                    <!-- Botones de acción -->
                    <?php echo $__env->make('public.mascotas.partials.show.action-buttons', ['position' => 'main'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Botón flotante para móvil -->
<?php echo $__env->make('public.mascotas.partials.show.floating-button', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<!-- Modal de adopción -->
<?php echo $__env->make('public.mascotas.partials.show.adoption-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/pages/mascotas/public-show.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\mascotas\show.blade.php ENDPATH**/ ?>