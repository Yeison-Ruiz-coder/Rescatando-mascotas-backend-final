<?php $__env->startSection('content'); ?>
<div class="container mt-5">
    <h2 class="mb-4">Detalle de la Donación</h2>
    <div class="card p-4 shadow-sm">
        <div class="row">
            <div class="col-md-6">
                <p><strong>ID de Transacción:</strong> #<?php echo e($donation->id ?? '12345'); ?></p>
                <p><strong>Monto Donado:</strong> $<?php echo e(number_format($donation->amount ?? 30000, 0, ',', '.')); ?></p>
                <p><strong>Frecuencia:</strong> <?php echo e($donation->frequency ?? 'Única'); ?></p>
                <p><strong>Fecha de Donación:</strong> <?php echo e($donation->created_at->format('d/m/Y') ?? '20/06/2025'); ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Correo del Donante:</strong> <?php echo e($donation->email ?? 'ejemplo@correo.com'); ?></p>
                <p><strong>Beneficiario:</strong> <?php echo e($donation->target_entity ?? 'Fundación Hogar Feliz'); ?></p>
                <p><strong>Método de Pago:</strong> <?php echo e($donation->payment_method ?? 'Nequi'); ?></p>
                <p><strong>Estado:</strong> <span class="badge bg-success p-2">Completada</span></p>
            </div>
        </div>
        <hr>
        <div class="d-flex justify-content-between">
            <a href="<?php echo e(route('donaciones.index')); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Volver al Historial
            </a>
            <a href="#" class="btn btn-info text-white">
                <i class="fas fa-receipt me-2"></i> Descargar Recibo
            </a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\donaciones\show.blade.php ENDPATH**/ ?>