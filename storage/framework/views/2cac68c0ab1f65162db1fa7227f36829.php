<!-- Estado Vacío -->
<div class="col-12">
    <div class="empty-state text-center py-5">
        <div class="empty-icon mb-4">
            <i class="fas fa-paw fa-4x text-muted"></i>
        </div>
        <h3 class="text-muted mb-3">No se encontraron mascotas</h3>
        <p class="text-muted mb-4">Intenta ajustar los filtros de búsqueda o crear una nueva mascota.</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="<?php echo e(route('admin.mascotas.index')); ?>" class="btn btn-primary">
                <i class="fas fa-redo me-2"></i>Ver todas las mascotas
            </a>
            <a href="<?php echo e(route('admin.mascotas.create')); ?>" class="btn btn-success">
                <i class="fas fa-plus me-2"></i>Crear Nueva Mascota
            </a>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\mascotas\partials\index\empty-state.blade.php ENDPATH**/ ?>