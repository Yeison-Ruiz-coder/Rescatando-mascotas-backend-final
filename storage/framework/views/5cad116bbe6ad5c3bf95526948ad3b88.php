<div class="seccion-formulario animacion-entrada">
    <h5>
        <i class="fas fa-user-circle"></i>Tus Datos Personales
    </h5>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="nombre" class="form-label-solicitud">
                <i class="fas fa-signature"></i>Nombre
            </label>
            <input type="text" class="form-control-solicitud <?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   id="nombre" name="nombre"
                   value="<?php echo e(old('nombre')); ?>"
                   placeholder="Tu nombre" required>
            <?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="mensaje-error">
                    <i class="fas fa-exclamation-circle"></i><?php echo e($message); ?>

                </div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="col-md-6 mb-3">
            <label for="apellido" class="form-label-solicitud">
                <i class="fas fa-signature"></i>Apellido
            </label>
            <input type="text" class="form-control-solicitud <?php $__errorArgs = ['apellido'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   id="apellido" name="apellido"
                   value="<?php echo e(old('apellido')); ?>"
                   placeholder="Tus apellidos" required>
            <?php $__errorArgs = ['apellido'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="mensaje-error">
                    <i class="fas fa-exclamation-circle"></i><?php echo e($message); ?>

                </div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="email" class="form-label-solicitud">
                <i class="fas fa-envelope"></i>Email
            </label>
            <input type="email" class="form-control-solicitud <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   id="email" name="email"
                   value="<?php echo e(old('email')); ?>"
                   placeholder="tu@email.com" required>
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="mensaje-error">
                    <i class="fas fa-exclamation-circle"></i><?php echo e($message); ?>

                </div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="col-md-6 mb-3">
            <label for="telefono" class="form-label-solicitud">
                <i class="fas fa-phone"></i>Teléfono
            </label>
            <input type="tel" class="form-control-solicitud <?php $__errorArgs = ['telefono'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   id="telefono" name="telefono"
                   value="<?php echo e(old('telefono')); ?>"
                   placeholder="+57 300 123 4567" required>
            <?php $__errorArgs = ['telefono'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="mensaje-error">
                    <i class="fas fa-exclamation-circle"></i><?php echo e($message); ?>

                </div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="documento_identidad" class="form-label-solicitud">
                <i class="fas fa-id-card"></i>Documento de Identidad
            </label>
            <input type="text" class="form-control-solicitud <?php $__errorArgs = ['documento_identidad'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   id="documento_identidad" name="documento_identidad"
                   value="<?php echo e(old('documento_identidad')); ?>"
                   placeholder="Número de documento" required>
            <?php $__errorArgs = ['documento_identidad'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="mensaje-error">
                    <i class="fas fa-exclamation-circle"></i><?php echo e($message); ?>

                </div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="col-md-6 mb-3">
            <!-- Espacio vacío para mantener el grid -->
        </div>
    </div>

    <div class="mb-3">
        <label for="direccion" class="form-label-solicitud">
            <i class="fas fa-map-marker-alt"></i>Dirección Completa
        </label>
        <textarea class="form-control-solicitud <?php $__errorArgs = ['direccion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                  id="direccion" name="direccion"
                  rows="3"
                  placeholder="Ingresa tu dirección completa (ciudad, barrio, calle, número)"
                  required><?php echo e(old('direccion')); ?></textarea>
        <?php $__errorArgs = ['direccion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="mensaje-error">
                <i class="fas fa-exclamation-circle"></i><?php echo e($message); ?>

            </div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\adopciones\partials\form_datos_personales.blade.php ENDPATH**/ ?>