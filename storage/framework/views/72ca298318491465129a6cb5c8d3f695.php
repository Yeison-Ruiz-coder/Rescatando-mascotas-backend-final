<!-- Sección 3: Salud y Vacunas -->
<div class="form-section">
    <h4 class="section-title">
        <i class="fas fa-syringe me-2"></i>Salud y Vacunas
    </h4>
    <div class="row g-3">
        <!-- Checkboxes para opciones de salud -->
        <div class="col-12">
            <label class="form-label d-block">Opciones de Salud</label>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input"
                               type="checkbox"
                               id="necesita_hogar_temporal"
                               name="necesita_hogar_temporal"
                               value="1"
                               <?php echo e(old('necesita_hogar_temporal', $mascota->necesita_hogar_temporal) ? 'checked' : ''); ?>>
                        <label class="form-check-label" for="necesita_hogar_temporal">
                            <i class="fas fa-home me-1"></i>Necesita hogar temporal
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input"
                               type="checkbox"
                               id="apto_con_ninos"
                               name="apto_con_ninos"
                               value="1"
                               <?php echo e(old('apto_con_ninos', $mascota->apto_con_ninos) ? 'checked' : ''); ?>>
                        <label class="form-check-label" for="apto_con_ninos">
                            <i class="fas fa-child me-1"></i>Apto con niños
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input"
                               type="checkbox"
                               id="apto_con_otros_animales"
                               name="apto_con_otros_animales"
                               value="1"
                               <?php echo e(old('apto_con_otros_animales', $mascota->apto_con_otros_animales) ? 'checked' : ''); ?>>
                        <label class="form-check-label" for="apto_con_otros_animales">
                            <i class="fas fa-dog me-1"></i>Apto con otros animales
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vacunas -->
        <div class="col-12 mt-3">
            <label for="vacunas" class="form-label">
                Vacunas Aplicadas
            </label>
            <div class="multi-select-container">
                <select class="form-select form-select-custom multi-select <?php $__errorArgs = ['vacunas'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        id="vacunas"
                        name="vacunas[]"
                        multiple
                        size="4">
                    <?php $__currentLoopData = $vacunas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vacuna): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($vacuna->id); ?>"
                            <?php echo e(in_array($vacuna->id, old('vacunas', $mascota->vacunas->pluck('id')->toArray())) ? 'selected' : ''); ?>>
                            <?php echo e($vacuna->nombre_vacuna); ?>

                            <?php if($vacuna->frecuencia_dias): ?>
                                (Cada <?php echo e($vacuna->frecuencia_dias); ?> días)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="form-help">
                <i class="fas fa-info-circle"></i> Mantén presionada la tecla Ctrl para seleccionar múltiples vacunas
            </div>
            <?php $__errorArgs = ['vacunas'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-foreve\resources\views/admin/mascotas/partials/edit/form-health.blade.php ENDPATH**/ ?>