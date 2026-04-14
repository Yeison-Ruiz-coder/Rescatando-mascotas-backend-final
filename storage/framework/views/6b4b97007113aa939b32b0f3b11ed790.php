<div class="card card-compromisos animacion-entrada">
    <div class="card-body">
        <h6 class="card-title">
            <i class="fas fa-handshake"></i>Compromisos de Adopción
        </h6>

        <div class="form-check-compromiso">
            <input class="form-check-input" type="checkbox" id="compromiso_cuidado" name="compromiso_cuidado" required
                   <?php echo e(old('compromiso_cuidado') ? 'checked' : ''); ?>>
            <label class="form-check-label" for="compromiso_cuidado">
                <strong>Cuidado responsable:</strong> Me comprometo a proporcionar todos los cuidados necesarios incluyendo alimentación adecuada, atención veterinaria regular, ejercicio y mucho amor.
            </label>
        </div>

        <div class="form-check-compromiso">
            <input class="form-check-input" type="checkbox" id="compromiso_esterilizacion" name="compromiso_esterilizacion" required
                   <?php echo e(old('compromiso_esterilizacion') ? 'checked' : ''); ?>>
            <label class="form-check-label" for="compromiso_esterilizacion">
                <strong>Esterilización:</strong> Acepto esterilizar a la mascota si no lo está, de acuerdo con las políticas de la fundación para controlar la población animal.
            </label>
        </div>

        <div class="form-check-compromiso">
            <input class="form-check-input" type="checkbox" id="compromiso_seguimiento" name="compromiso_seguimiento" required
                   <?php echo e(old('compromiso_seguimiento') ? 'checked' : ''); ?>>
            <label class="form-check-label" for="compromiso_seguimiento">
                <strong>Seguimiento:</strong> Permito visitas de seguimiento por parte de la fundación para verificar el bienestar de la mascota y proporcionaré actualizaciones periódicas.
            </label>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\adopciones\partials\compromisos.blade.php ENDPATH**/ ?>