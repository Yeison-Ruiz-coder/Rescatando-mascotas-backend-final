<form action="<?php echo e(route('admin.adopciones.update', $adopcion->id)); ?>" method="POST">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>

    <div class="row">
        <!-- Mascota -->
        <div class="col-md-6 mb-3">
            <label for="mascota_id" class="form-label">Mascota <span class="text-danger">*</span></label>
            <select name="mascota_id" id="mascota_id" class="form-select <?php $__errorArgs = ['mascota_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                <option value="">Seleccione una mascota</option>
                <?php $__currentLoopData = $mascotas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mascota): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($mascota->id); ?>" <?php echo e(old('mascota_id', $adopcion->mascota_id) == $mascota->id ? 'selected' : ''); ?>>
                        <?php echo e($mascota->nombre); ?> (<?php echo e($mascota->especie); ?>)
                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['mascota_id'];
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

        <!-- Usuario (Adoptante) -->
        <div class="col-md-6 mb-3">
            <label for="user_id" class="form-label">Adoptante <span class="text-danger">*</span></label>
            <select name="user_id" id="user_id" class="form-select <?php $__errorArgs = ['user_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                <option value="">Seleccione un adoptante</option>
                <?php $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usuario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($usuario->id); ?>" <?php echo e(old('user_id', $adopcion->user_id) == $usuario->id ? 'selected' : ''); ?>>
                        <?php echo e($usuario->name); ?> - <?php echo e($usuario->email); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['user_id'];
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

        <!-- Fundación -->
        <div class="col-md-6 mb-3">
            <label for="fundacion_id" class="form-label">Fundación</label>
            <select name="fundacion_id" id="fundacion_id" class="form-select <?php $__errorArgs = ['fundacion_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                <option value="">Seleccione una fundación</option>
                <?php $__currentLoopData = $fundaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fundacion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($fundacion->id); ?>" <?php echo e(old('fundacion_id', $adopcion->fundacion_id) == $fundacion->id ? 'selected' : ''); ?>>
                        <?php echo e($fundacion->nombre); ?>

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

        <!-- Solicitud -->
        <div class="col-md-6 mb-3">
            <label for="solicitud_id" class="form-label">Solicitud Asociada</label>
            <select name="solicitud_id" id="solicitud_id" class="form-select <?php $__errorArgs = ['solicitud_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                <option value="">Ninguna</option>
                <?php $__currentLoopData = $solicitudes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $solicitud): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($solicitud->id); ?>" <?php echo e(old('solicitud_id', $adopcion->solicitud_id) == $solicitud->id ? 'selected' : ''); ?>>
                        Solicitud #<?php echo e($solicitud->id); ?> - <?php echo e($solicitud->user->name ?? 'N/A'); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['solicitud_id'];
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

        <!-- Fecha de Adopción -->
        <div class="col-md-4 mb-3">
            <label for="fecha_adopcion" class="form-label">Fecha de Adopción</label>
            <input type="date" name="fecha_adopcion" id="fecha_adopcion"
                   class="form-control <?php $__errorArgs = ['fecha_adopcion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   value="<?php echo e(old('fecha_adopcion', $adopcion->fecha_adopcion ? $adopcion->fecha_adopcion->format('Y-m-d') : date('Y-m-d'))); ?>">
            <?php $__errorArgs = ['fecha_adopcion'];
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

        <!-- Estado -->
        <div class="col-md-4 mb-3">
            <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
            <select name="estado" id="estado" class="form-select <?php $__errorArgs = ['estado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                <option value="">Seleccione un estado</option>
                <?php $__currentLoopData = $estados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value); ?>" <?php echo e(old('estado', $adopcion->estado) == $value ? 'selected' : ''); ?>>
                        <?php echo e($label); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['estado'];
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

        <!-- Fecha de Cierre -->
        <div class="col-md-4 mb-3">
            <label for="fecha_cierre" class="form-label">Fecha de Cierre</label>
            <input type="date" name="fecha_cierre" id="fecha_cierre"
                   class="form-control <?php $__errorArgs = ['fecha_cierre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   value="<?php echo e(old('fecha_cierre', $adopcion->fecha_cierre ? $adopcion->fecha_cierre->format('Y-m-d') : '')); ?>">
            <?php $__errorArgs = ['fecha_cierre'];
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

        <!-- Razón de Rechazo -->
        <div class="col-12 mb-3 razon-rechazo-container" style="<?php echo e(in_array($adopcion->estado, ['rechazada', 'cancelada']) ? 'display:block;' : 'display:none;'); ?>">
            <label for="razon_rechazo" class="form-label">Razón de Rechazo/Cancelación</label>
            <textarea name="razon_rechazo" id="razon_rechazo" rows="3"
                      class="form-control <?php $__errorArgs = ['razon_rechazo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('razon_rechazo', $adopcion->razon_rechazo)); ?></textarea>
            <?php $__errorArgs = ['razon_rechazo'];
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

    <!-- Botones -->
    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="<?php echo e(route('admin.adopciones.show', $adopcion->id)); ?>" class="btn btn-info">
            <i class="fas fa-eye me-2"></i>Ver
        </a>
        <a href="<?php echo e(route('admin.adopciones.index')); ?>" class="btn btn-secondary">
            <i class="fas fa-times me-2"></i>Cancelar
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Actualizar
        </button>
    </div>
</form>

<?php $__env->startPush('scripts'); ?>
<script>
document.getElementById('estado').addEventListener('change', function() {
    const razonContainer = document.querySelector('.razon-rechazo-container');
    if (this.value === 'rechazada' || this.value === 'cancelada') {
        razonContainer.style.display = 'block';
    } else {
        razonContainer.style.display = 'none';
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\adopciones\partials\edit\form.blade.php ENDPATH**/ ?>