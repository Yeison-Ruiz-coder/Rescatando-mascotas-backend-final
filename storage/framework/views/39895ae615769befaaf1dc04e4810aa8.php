<!-- Sección 5: Fechas y Fundación -->
<div class="form-section">
    <h4 class="section-title">
        <i class="fas fa-calendar-alt me-2"></i>Fechas y Organización
    </h4>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="fecha_ingreso" class="form-label">
                Fecha de Ingreso <span class="required">*</span>
            </label>
            <input type="date"
                   class="form-control form-control-custom <?php $__errorArgs = ['fecha_ingreso'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   id="fecha_ingreso"
                   name="fecha_ingreso"
                   value="<?php echo e(old('fecha_ingreso', $mascota->fecha_ingreso ? $mascota->fecha_ingreso->format('Y-m-d') : date('Y-m-d'))); ?>"
                   required>
            <?php $__errorArgs = ['fecha_ingreso'];
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

        <div class="col-md-6">
            <label for="fecha_salida" class="form-label">
                Fecha de Salida (si aplica)
            </label>
            <input type="date"
                   class="form-control form-control-custom <?php $__errorArgs = ['fecha_salida'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   id="fecha_salida"
                   name="fecha_salida"
                   value="<?php echo e(old('fecha_salida', $mascota->fecha_salida ? $mascota->fecha_salida->format('Y-m-d') : '')); ?>">
            <?php $__errorArgs = ['fecha_salida'];
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
            <label for="fundacion_id" class="form-label">
                Fundación <span class="required">*</span>
            </label>
            <select class="form-select form-select-custom <?php $__errorArgs = ['fundacion_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    id="fundacion_id"
                    name="fundacion_id"
                    required>
                <option value="">Selecciona una fundación</option>
                <?php $__currentLoopData = $fundaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fundacion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($fundacion->id); ?>"
                        <?php echo e(old('fundacion_id', $mascota->fundacion_id) == $fundacion->id ? 'selected' : ''); ?>>
                        <?php echo e($fundacion->Nombre_1); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['fundacion_id'];
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
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-foreve\resources\views/admin/mascotas/partials/edit/form-dates-foundation.blade.php ENDPATH**/ ?>