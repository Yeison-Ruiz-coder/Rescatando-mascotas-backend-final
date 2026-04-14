 <!-- Usa el nuevo layout del home -->

<?php $__env->startSection('title', 'Rescatando Mascotas - Inicio'); ?>

<?php $__env->startSection('content'); ?>
    <!-- Hero Section -->
    <?php echo $__env->make('home.partials.hero', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Stats Section -->
    <?php echo $__env->make('home.partials.stats', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Mascotas Section -->
    <?php echo $__env->make('home.partials.mascotas', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


    <!-- Proceso Section -->
    <?php echo $__env->make('home.partials.proceso', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Testimonios Section -->
    <?php echo $__env->make('home.partials.testimonios', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- CTA Section -->
    <?php echo $__env->make('home.partials.cta', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php $__env->startPush('styles'); ?>
    
    <link rel="stylesheet" href="<?php echo e(asset('css/home/hero.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/home/mascotas.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/home/cta.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/home/stats.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/home/testimonios.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/home/responsive.css')); ?>">
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\home\index.blade.php ENDPATH**/ ?>