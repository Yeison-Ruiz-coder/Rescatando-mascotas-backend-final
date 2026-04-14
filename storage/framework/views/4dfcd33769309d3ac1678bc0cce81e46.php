<div class="modal fade" id="modalSeguimiento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('admin.adopciones.seguimientos.store', $adopcion->id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-clipboard-list me-2"></i>Nuevo Seguimiento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="fecha_seguimiento" class="form-label">Fecha del Seguimiento *</label>
                        <input type="date" class="form-control" id="fecha_seguimiento"
                               name="fecha_seguimiento" value="<?php echo e(date('Y-m-d')); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="estado_mascota" class="form-label">Estado de la Mascota *</label>
                        <select class="form-select" id="estado_mascota" name="estado_mascota" required>
                            <option value="excelente">Excelente</option>
                            <option value="bueno">Bueno</option>
                            <option value="regular">Regular</option>
                            <option value="preocupante">Preocupante</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones *</label>
                        <textarea class="form-control" id="observaciones" name="observaciones"
                                  rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Guardar Seguimiento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\adopciones\partials\show\modal_seguimiento.blade.php ENDPATH**/ ?>