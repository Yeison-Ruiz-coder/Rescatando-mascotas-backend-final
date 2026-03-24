<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Panel Admin'); ?> - Mascotas</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tus estilos personalizados -->
    <link rel="stylesheet" href="<?php echo e(asset('css/components/footer.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/style.css')); ?>">
    <link href="<?php echo e(asset('css/components/admin.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/components/public-navbar.css')); ?>" rel="stylesheet">
    

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>

<body>
    <!-- Header (el actual pero mejorado) -->
    <?php echo $__env->make('public.layouts.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Menú Hamburguesa -->
    <?php echo $__env->make('public.layouts.navigation', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Overlay para cuando el menú está abierto -->
    <div class="menu-overlay" id="menuOverlay"></div>

    <!-- Contenido Principal -->
    <main class="main-content" id="mainContent">
        <div class="container-fluid py-4">
            <!-- Título de la página -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3"><?php echo $__env->yieldContent('page-title'); ?></h1>
                <?php echo $__env->yieldContent('page-actions'); ?>
            </div>

            <!-- Mensajes de sesión -->
            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Contenido de la página -->
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </main>

    <?php echo $__env->make('public.layouts.partials.footer.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="<?php echo e(asset('js/public-navbar.js')); ?>"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend\resources\views/public/layouts/app.blade.php ENDPATH**/ ?>