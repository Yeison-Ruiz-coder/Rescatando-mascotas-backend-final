<?php if($mascotas->hasPages()): ?>
<div class="row mt-5">
    <div class="col-12 paginacion-moderna">
        <?php echo e($mascotas->links()); ?>

    </div>
</div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-foreve\resources\views/public/mascotas/partials/index/pagination.blade.php ENDPATH**/ ?>