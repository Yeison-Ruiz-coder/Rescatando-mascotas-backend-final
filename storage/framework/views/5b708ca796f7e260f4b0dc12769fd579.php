
<?php if($solicitudes->hasPages()): ?>
<div class="pagination-container">
    <div class="pagination-info">
        Mostrando <?php echo e($solicitudes->firstItem() ?? 0); ?> - <?php echo e($solicitudes->lastItem() ?? 0); ?> de <?php echo e($solicitudes->total()); ?> solicitudes
    </div>
    <div class="pagination-links">
        <?php echo e($solicitudes->links()); ?>

    </div>
</div>
<?php endif; ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\solicitud\partials\index\pagination.blade.php ENDPATH**/ ?>