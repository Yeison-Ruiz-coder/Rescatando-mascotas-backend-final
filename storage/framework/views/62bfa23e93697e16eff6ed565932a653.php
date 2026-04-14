
<div class="edit-form-container">
    <form action="<?php echo e(route('admin.solicitudes.update', $solicitud)); ?>" method="POST" class="solicitud-form">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <!-- CAMPO OCULTO PARA user_id -->
        <input type="hidden" name="user_id" value="<?php echo e($solicitud->user_id); ?>">

        <!-- INFORMACIÓN DE SOLICITANTE (Solo lectura) -->
        <div class="info-card">
            <h4><i class="fa-solid fa-user"></i> Información del Solicitante</h4>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Nombre:</strong>
                    <span>
                        <?php if($solicitud->usuario): ?>
                            <?php echo e($solicitud->usuario->name ?? $solicitud->usuario->nombre_completo); ?>

                        <?php else: ?>
                            <?php echo e($solicitud->nombre_solicitante); ?>

                            <?php if($solicitud->esAdopcion() && $apellido = $solicitud->getDatoAdopcion('apellido_solicitante')): ?>
                                <?php echo e($apellido); ?>

                            <?php endif; ?>
                        <?php endif; ?>
                    </span>
                </div>

                <div class="info-item">
                    <strong>Email:</strong>
                    <span><?php echo e($solicitud->email_solicitante ?? ($solicitud->usuario->email ?? 'No especificado')); ?></span>
                </div>

                <div class="info-item">
                    <strong>Teléfono:</strong>
                    <span><?php echo e($solicitud->telefono_solicitante ?? 'No especificado'); ?></span>
                </div>

                <?php if($solicitud->solicitable && $solicitud->solicitable_type == 'App\Models\Mascota'): ?>
                <div class="info-item">
                    <strong>Mascota solicitada:</strong>
                    <span><?php echo e($solicitud->solicitable->nombre ?? 'Mascota #' . $solicitud->solicitable_id); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-grid">
            <!-- Tipo de Solicitud -->
            <div class="form-group">
                <label for="tipo_solicitud">
                    <i class="fa-solid fa-tag"></i> Tipo de Solicitud:
                </label>
                <select id="tipo_solicitud" name="tipo_solicitud" required class="form-control <?php $__errorArgs = ['tipo_solicitud'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <option value="">Selecciona un tipo</option>
                    <option value="adopcion" <?php echo e(old('tipo_solicitud', $solicitud->tipo_solicitud) == 'adopcion' ? 'selected' : ''); ?>>Adopción</option>
                    <option value="rescate" <?php echo e(old('tipo_solicitud', $solicitud->tipo_solicitud) == 'rescate' ? 'selected' : ''); ?>>Rescate</option>
                    <option value="apadrinamiento" <?php echo e(old('tipo_solicitud', $solicitud->tipo_solicitud) == 'apadrinamiento' ? 'selected' : ''); ?>>Apadrinamiento</option>
                    <option value="donacion" <?php echo e(old('tipo_solicitud', $solicitud->tipo_solicitud) == 'donacion' ? 'selected' : ''); ?>>Donación</option>
                    <option value="otro" <?php echo e(old('tipo_solicitud', $solicitud->tipo_solicitud) == 'otro' ? 'selected' : ''); ?>>Otro</option>
                </select>
                <?php $__errorArgs = ['tipo_solicitud'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="error-message"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Estado -->
            <div class="form-group">
                <label for="estado">
                    <i class="fa-solid fa-circle-check"></i> Estado:
                </label>
                <select id="estado" name="estado" required class="form-control <?php $__errorArgs = ['estado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <option value="pendiente" <?php echo e(old('estado', $solicitud->estado) == 'pendiente' ? 'selected' : ''); ?>>Pendiente</option>
                    <option value="en_revision" <?php echo e(old('estado', $solicitud->estado) == 'en_revision' ? 'selected' : ''); ?>>En Revisión</option>
                    <option value="aprobada" <?php echo e(old('estado', $solicitud->estado) == 'aprobada' ? 'selected' : ''); ?>>Aprobada</option>
                    <option value="rechazada" <?php echo e(old('estado', $solicitud->estado) == 'rechazada' ? 'selected' : ''); ?>>Rechazada</option>
                    <option value="completada" <?php echo e(old('estado', $solicitud->estado) == 'completada' ? 'selected' : ''); ?>>Completada</option>
                </select>
                <?php $__errorArgs = ['estado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="error-message"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Fecha de Solicitud -->
            <div class="form-group">
                <label for="fecha_solicitud">
                    <i class="fa-solid fa-calendar"></i> Fecha de Solicitud:
                </label>
                <input type="datetime-local" id="fecha_solicitud" name="fecha_solicitud"
                       value="<?php echo e(old('fecha_solicitud', $solicitud->fecha_solicitud->format('Y-m-d\TH:i'))); ?>"
                       required class="form-control <?php $__errorArgs = ['fecha_solicitud'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                <?php $__errorArgs = ['fecha_solicitud'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="error-message"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Notas Internas (solo admin) -->
            <div class="form-group full-width">
                <label for="notas_internas">
                    <i class="fa-solid fa-lock"></i> Notas Internas (solo visible para administradores):
                </label>
                <textarea id="notas_internas" name="notas_internas" rows="4"
                          class="form-control <?php $__errorArgs = ['notas_internas'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                          placeholder="Notas internas, observaciones, seguimiento..."><?php echo e(old('notas_internas', $solicitud->notas_internas)); ?></textarea>
                <?php $__errorArgs = ['notas_internas'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="error-message"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>

        <!-- Contenido / Justificación -->
        <div class="form-group full-width">
            <label for="contenido">
                <i class="fa-solid fa-file-lines"></i> Contenido / Justificación:
            </label>
            <textarea id="contenido" name="contenido" rows="10" required
                      class="form-control <?php $__errorArgs = ['contenido'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                      placeholder="Describe los detalles de la solicitud..."><?php echo e(old('contenido', $solicitud->contenido)); ?></textarea>
            <?php $__errorArgs = ['contenido'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span class="error-message"><?php echo e($message); ?></span>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <div class="help-text">
                <i class="fa-solid fa-info-circle"></i> Aquí puedes modificar o añadir notas sobre la justificación del solicitante.
            </div>
        </div>

        <!-- Razón de Rechazo (visible solo si está rechazada) -->
        <?php if($solicitud->isRechazada()): ?>
        <div class="form-group full-width">
            <label for="razon_rechazo">
                <i class="fa-solid fa-ban"></i> Razón de Rechazo:
            </label>
            <textarea id="razon_rechazo" name="razon_rechazo" rows="3"
                      class="form-control" readonly><?php echo e($solicitud->razon_rechazo); ?></textarea>
        </div>
        <?php endif; ?>

        <!-- Botones de Acción -->
        <div class="form-actions">
            <button type="submit" class="btn-submit">
                <i class="fa-solid fa-save"></i> Guardar Cambios
            </button>
            <a href="<?php echo e(route('admin.solicitudes.show', $solicitud)); ?>" class="btn-cancel">
                <i class="fa-solid fa-times"></i> Cancelar
            </a>
            <a href="<?php echo e(route('admin.solicitudes.index')); ?>" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i> Volver al Listado
            </a>
        </div>
    </form>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\solicitud\partials\edit\form.blade.php ENDPATH**/ ?>