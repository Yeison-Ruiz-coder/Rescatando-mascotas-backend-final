
<div class="card shadow gestion-card mb-4">
    <div class="card-header fw-bold bg-turquesa text-white">
        <i class="fas fa-cog me-2"></i>Información de Gestión
    </div>
    <div class="card-body">
        <div class="mb-3">
            <strong>ID de Mascota:</strong>
            <span class="badge bg-secondary float-end">#<?php echo e($mascota->id); ?></span>
        </div>

        <div class="mb-3">
            <strong>Estado Actual:</strong>
            <?php switch($mascota->estado):
                case ('En adopcion'): ?>
                    <span class="badge bg-success float-end">En adopción</span>
                    <?php break; ?>
                <?php case ('Adoptado'): ?>
                    <span class="badge bg-info float-end">Adoptado</span>
                    <?php break; ?>
                <?php case ('Rescatada'): ?>
                    <span class="badge bg-warning float-end">Rescatada</span>
                    <?php break; ?>
                <?php case ('En acogida'): ?>
                    <span class="badge bg-secondary float-end">En acogida</span>
                    <?php break; ?>
                <?php default: ?>
                    <span class="badge bg-light float-end"><?php echo e($mascota->estado); ?></span>
            <?php endswitch; ?>
        </div>

        <div class="mb-3">
            <strong>Fecha de Creación:</strong>
            <span class="float-end"><?php echo e($mascota->created_at->format('d/m/Y H:i')); ?></span>
        </div>

        <div class="mb-3">
            <strong>Última Actualización:</strong>
            <span class="float-end"><?php echo e($mascota->updated_at->format('d/m/Y H:i')); ?></span>
        </div>

        <?php if($mascota->necesita_hogar_temporal): ?>
        <div class="mb-3">
            <span class="badge bg-warning text-dark w-100 p-2">
                <i class="fas fa-home me-2"></i>Necesita hogar temporal
            </span>
        </div>
        <?php endif; ?>

        <?php if($mascota->condiciones_especiales): ?>
        <div class="mb-3">
            <strong>Condiciones Especiales:</strong>
            <p class="text-muted mt-1"><?php echo e($mascota->condiciones_especiales); ?></p>
        </div>
        <?php endif; ?>

        <hr>

        <div class="d-flex gap-2 flex-wrap">
            <a href="<?php echo e(route('admin.mascotas.edit', $mascota)); ?>" class="btn btn-warning flex-fill">
                <i class="fas fa-edit me-2"></i>Editar
            </a>

            <form action="<?php echo e(route('admin.mascotas.destroy', $mascota)); ?>" method="POST" class="flex-fill">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn btn-danger w-100"
                    onclick="return confirm('¿Estás seguro de que deseas eliminar a <?php echo e($mascota->nombre_mascota); ?>? Esta acción no se puede deshacer.')">
                    <i class="fas fa-trash me-2"></i>Eliminar
                </button>
            </form>
        </div>

        <div class="mt-3">
            <a href="<?php echo e(route('admin.mascotas.index')); ?>" class="btn btn-secondary w-100">
                <i class="fas fa-arrow-left me-2"></i>Volver al Listado
            </a>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-foreve\resources\views/admin/mascotas/partials/show/management-info.blade.php ENDPATH**/ ?>