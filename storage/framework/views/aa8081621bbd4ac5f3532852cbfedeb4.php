<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/pages/mascotas/create.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-3 px-lg-5 py-4">

    <!-- Alertas -->
    <?php echo $__env->make('partials.alerts.error', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('partials.alerts.success', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


    <!-- Header Section -->
    <?php echo $__env->make('admin.mascotas.partials.create.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Formulario -->
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <div class="card form-mascota-card shadow-lg border-0">
                <div class="card-header form-mascota-header">
                    <h3 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>Formulario de Registro
                    </h3>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('admin.mascotas.store')); ?>" method="POST" enctype="multipart/form-data" id="mascotaForm">
                        <?php echo csrf_field(); ?>

                        <!-- Sección 1: Información Básica -->
                        <?php echo $__env->make('admin.mascotas.partials.create.form-basic-info', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                        <!-- Sección 2: Ubicación y Descripción -->
                        <?php echo $__env->make('admin.mascotas.partials.create.form-location-description', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                        <!-- Sección 3: Salud y Vacunas -->
                        <?php echo $__env->make('admin.mascotas.partials.create.form-health', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                        <!-- Sección 4: Galería de Fotos -->
                        <?php echo $__env->make('admin.mascotas.partials.create.form-gallery', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                        <!-- Sección 5: Fechas y Fundación -->
                        <?php echo $__env->make('admin.mascotas.partials.create.form-dates-foundation', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                        <!-- Botones de acción -->
                        <?php echo $__env->make('admin.mascotas.partials.create.form-actions', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="<?php echo e(asset('js/pages/mascotas/create.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-foreve\resources\views/admin/mascotas/create.blade.php ENDPATH**/ ?>