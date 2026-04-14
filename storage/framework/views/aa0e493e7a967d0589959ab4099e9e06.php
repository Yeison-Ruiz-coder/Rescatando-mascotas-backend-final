<div class="botones-accion-solicitud">
    <div class="d-flex justify-content-between align-items-center">
        <a href="<?php echo e(route('public.mascotas.show', $mascota->id)); ?>" class="btn btn-cancelar-solicitud">
            <i class="fas fa-arrow-left me-2"></i>Cancelar
        </a>
        <button type="submit" class="btn btn-enviar-solicitud" id="btnEnviarSolicitud">
            <i class="fas fa-paper-plane me-2"></i>Enviar Solicitud
        </button>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\adopciones\partials\acciones.blade.php ENDPATH**/ ?>