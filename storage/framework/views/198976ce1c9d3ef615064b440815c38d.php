
<div class="card shadow-sm tabla-adopciones">
    <div class="card-body p-0">
        <div class="estado-vacio">
            <i class="fas fa-paw fa-4x"></i>
            <h4>No se encontraron adopciones</h4>
            <p class="text-muted mb-4">
                <?php if(request()->anyFilled(['estado', 'mascota_id', 'usuario_id', 'busqueda', 'fecha_desde', 'fecha_hasta'])): ?>
                    Intenta con otros criterios de búsqueda o limpia los filtros.
                <?php else: ?>
                    Comienza registrando una nueva adopción en el sistema.
                <?php endif; ?>
            </p>
            <a href="<?php echo e(route('admin.adopciones.create')); ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-plus me-2"></i> Crear Primera Adopción
            </a>
        </div>
    </div>
</div><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\adopciones\partials\index\empty_state.blade.php ENDPATH**/ ?>