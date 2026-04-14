<?php $__env->startSection('title', 'Crear Nueva Solicitud'); ?>

<?php $__env->startSection('content'); ?>
<section class="admin-panel">

    <!-- Header -->
    <?php echo $__env->make('admin.solicitud.partials.create.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Alertas -->
    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <i class="fa-solid fa-check-circle"></i> <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-error">
            <i class="fa-solid fa-exclamation-circle"></i> <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <!-- Formulario de Creación -->
    <?php echo $__env->make('admin.solicitud.partials.create.form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

</section>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/pages/solicitud/create.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación del formulario
    const form = document.querySelector('.solicitud-form');
    const contenido = document.getElementById('contenido');

    if (form) {
        form.addEventListener('submit', function(e) {
            if (contenido.value.trim().length < 10) {
                e.preventDefault();
                alert('El contenido debe tener al menos 10 caracteres.');
                contenido.focus();
            }
        });
    }

    // Auto-seleccionar fecha actual
    const fechaInput = document.getElementById('fecha_solicitud');
    if (fechaInput && !fechaInput.value) {
        const now = new Date();
        const localDateTime = now.toISOString().slice(0, 16);
        fechaInput.value = localDateTime;
    }
});
</script>
<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/pages/solicitud/create.js')); ?>"></script>
<?php $__env->stopPush(); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\solicitud\create.blade.php ENDPATH**/ ?>