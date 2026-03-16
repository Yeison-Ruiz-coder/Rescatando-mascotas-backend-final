<!-- Sección 2: Ubicación y Descripción -->
<div class="form-section">
    <h4 class="section-title">
        <i class="fas fa-map-marker-alt me-2"></i>Ubicación y Descripción
    </h4>
    <div class="row g-3">
        <div class="col-12">
            <label for="lugar_rescate" class="form-label">
                Lugar donde se encuentra <span class="required">*</span>
            </label>
            <input type="text"
                   class="form-control form-control-custom <?php $__errorArgs = ['lugar_rescate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   id="lugar_rescate"
                   name="lugar_rescate"
                   value="<?php echo e(old('lugar_rescate')); ?>"
                   placeholder="Ej: Parque Central, Calle Principal #123..."
                   required>
            <?php $__errorArgs = ['lugar_rescate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="invalid-feedback"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="col-12">
            <label for="descripcion" class="form-label">
                Descripción <span class="required">*</span>
            </label>
            <textarea class="form-control form-control-custom <?php $__errorArgs = ['descripcion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                      id="descripcion"
                      name="descripcion"
                      rows="5"
                      placeholder="Describe a la mascota: carácter, comportamiento, condición de salud, necesidades especiales..."
                      required><?php echo e(old('descripcion')); ?></textarea>
            <?php $__errorArgs = ['descripcion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="invalid-feedback"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <!-- NUEVO: Campo para condiciones especiales (enfermedades crónicas, discapacidades) -->
        <div class="col-12">
            <label for="condiciones_especiales" class="form-label">
                Condiciones Especiales
            </label>
            <textarea class="form-control form-control-custom <?php $__errorArgs = ['condiciones_especiales'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                      id="condiciones_especiales"
                      name="condiciones_especiales"
                      rows="3"
                      placeholder="Enfermedades crónicas, discapacidades, cuidados especiales..."><?php echo e(old('condiciones_especiales')); ?></textarea>
            <?php $__errorArgs = ['condiciones_especiales'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="invalid-feedback"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-foreve\resources\views/admin/mascotas/partials/create/form-location-description.blade.php ENDPATH**/ ?>