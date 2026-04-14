
<div id="statusModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Cambiar Estado de Solicitud #<?php echo e($solicitud->id); ?></h3>
            <button type="button" class="close-modal" onclick="closeStatusModal()">&times;</button>
        </div>

        <form action="<?php echo e(route('admin.solicitudes.update-status', $solicitud->id)); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PATCH'); ?>

            <div class="modal-body">
                <div class="form-group">
                    <label for="modal_estado">Nuevo Estado:</label>
                    <select name="estado" id="modal_estado" class="form-control" required>
                        <option value="pendiente" <?php echo e($solicitud->estado == 'pendiente' ? 'selected' : ''); ?>>Pendiente</option>
                        <option value="en_revision" <?php echo e($solicitud->estado == 'en_revision' ? 'selected' : ''); ?>>En Revisión</option>
                        <option value="aprobada" <?php echo e($solicitud->estado == 'aprobada' ? 'selected' : ''); ?>>Aprobada</option>
                        <option value="rechazada" <?php echo e($solicitud->estado == 'rechazada' ? 'selected' : ''); ?>>Rechazada</option>
                        <option value="completada" <?php echo e($solicitud->estado == 'completada' ? 'selected' : ''); ?>>Completada</option>
                    </select>
                </div>

                <div id="razonRechazoGroup" class="form-group" style="display: none;">
                    <label for="modal_razon_rechazo">Razón de Rechazo:</label>
                    <textarea name="razon_rechazo" id="modal_razon_rechazo" rows="4"
                              class="form-control" placeholder="Indica el motivo del rechazo..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeStatusModal()">Cancelar</button>
                <button type="submit" class="btn-submit">Actualizar Estado</button>
            </div>
        </form>
    </div>
</div>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 8px;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-body {
    padding: 1rem;
}

.modal-footer {
    padding: 1rem;
    border-top: 1px solid #dee2e6;
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}

.close-modal {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
}
</style>

<script>
function openStatusModal() {
    document.getElementById('statusModal').style.display = 'flex';
}

function closeStatusModal() {
    document.getElementById('statusModal').style.display = 'none';
}

// Mostrar/ocultar campo de razón de rechazo
document.getElementById('modal_estado')?.addEventListener('change', function() {
    const razonGroup = document.getElementById('razonRechazoGroup');
    if (this.value === 'rechazada') {
        razonGroup.style.display = 'block';
    } else {
        razonGroup.style.display = 'none';
    }
});
</script>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\solicitud\partials\show\status-modal.blade.php ENDPATH**/ ?>