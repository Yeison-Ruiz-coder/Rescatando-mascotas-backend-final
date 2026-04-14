
<div class="action-buttons-group">
    <a href="<?php echo e(route('admin.solicitudes.index')); ?>" class="btn-action back-btn">
        <i class="fa-solid fa-arrow-left"></i> Volver al Listado
    </a>

    <?php if($solicitud->isPendiente() || $solicitud->isEnRevision()): ?>
    <button type="button" class="btn-action review-btn" onclick="openStatusModal()">
        <i class="fa-solid fa-check-circle"></i> Revisar Solicitud
    </button>
    <?php endif; ?>

    <a href="<?php echo e(route('admin.solicitudes.edit', $solicitud)); ?>" class="btn-action edit-btn">
        <i class="fa-solid fa-pen-to-square"></i> Editar Solicitud
    </a>

    <?php if($solicitud->isRechazada() && $solicitud->razon_rechazo): ?>
    <button type="button" class="btn-action info-btn" onclick="showRejectionReason()">
        <i class="fa-solid fa-info-circle"></i> Ver Razón
    </button>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\solicitud\partials\show\actions.blade.php ENDPATH**/ ?>