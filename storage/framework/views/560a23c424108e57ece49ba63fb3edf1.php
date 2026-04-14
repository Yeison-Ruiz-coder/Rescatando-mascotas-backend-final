<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb breadcrumb-mascota">
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('public.mascotas.index')); ?>" class="text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i>Volver a Mascotas
            </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo e($mascota->nombre_mascota); ?></li>
    </ol>
</nav>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\mascotas\partials\show\breadcrumb.blade.php ENDPATH**/ ?>