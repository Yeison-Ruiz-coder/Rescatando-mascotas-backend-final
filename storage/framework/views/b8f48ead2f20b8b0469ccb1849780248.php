
<div class="create-form-container">
    <form action="<?php echo e(route('admin.solicitudes.store')); ?>" method="POST" class="solicitud-form">
        <?php echo csrf_field(); ?>

        <div class="form-grid">
            <!-- Información del Solicitante -->
            <div class="form-group">
                <label for="user_id">
                    <i class="fa-solid fa-user"></i> Usuario Registrado (opcional):
                </label>
                <select id="user_id" name="user_id" class="form-control <?php $__errorArgs = ['user_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <option value="">Selecciona un usuario (o completa datos manual)</option>
                    <?php $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usuario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($usuario->id); ?>" <?php echo e(old('user_id') == $usuario->id ? 'selected' : ''); ?>>
                            <?php echo e($usuario->email); ?> - <?php echo e($usuario->name ?? $usuario->nombre_completo); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['user_id'];
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

            <!-- Separador: Datos manuales (se ocultan si hay user_id) -->
            <div id="datosManuales" class="form-group full-width">
                <h4>Datos del Solicitante (si no es usuario registrado)</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre_solicitante">Nombre:</label>
                        <input type="text" id="nombre_solicitante" name="nombre_solicitante"
                               value="<?php echo e(old('nombre_solicitante')); ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="email_solicitante">Email:</label>
                        <input type="email" id="email_solicitante" name="email_solicitante"
                               value="<?php echo e(old('email_solicitante')); ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="telefono_solicitante">Teléfono:</label>
                        <input type="text" id="telefono_solicitante" name="telefono_solicitante"
                               value="<?php echo e(old('telefono_solicitante')); ?>" class="form-control">
                    </div>
                </div>
            </div>

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
                    <option value="adopcion" <?php echo e(old('tipo_solicitud') == 'adopcion' ? 'selected' : ''); ?>>Adopción</option>
                    <option value="rescate" <?php echo e(old('tipo_solicitud') == 'rescate' ? 'selected' : ''); ?>>Rescate</option>
                    <option value="apadrinamiento" <?php echo e(old('tipo_solicitud') == 'apadrinamiento' ? 'selected' : ''); ?>>Apadrinamiento</option>
                    <option value="donacion" <?php echo e(old('tipo_solicitud') == 'donacion' ? 'selected' : ''); ?>>Donación</option>
                    <option value="otro" <?php echo e(old('tipo_solicitud') == 'otro' ? 'selected' : ''); ?>>Otro</option>
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

            <!-- Item Solicitado (Mascota) -->
            <div class="form-group">
                <label for="solicitable_id">
                    <i class="fa-solid fa-paw"></i> Mascota:
                </label>
                <select id="solicitable_id" name="solicitable_id" required class="form-control <?php $__errorArgs = ['solicitable_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <option value="">Selecciona una mascota</option>
                    <?php $__currentLoopData = $mascotas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mascota): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($mascota->id); ?>" <?php echo e(old('solicitable_id') == $mascota->id ? 'selected' : ''); ?>>
                            <?php echo e($mascota->nombre); ?> (<?php echo e($mascota->especie); ?>)
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <input type="hidden" name="solicitable_type" value="App\Models\Mascota">
                <?php $__errorArgs = ['solicitable_id'];
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

        <!-- Campos específicos para adopción (se muestran solo si se selecciona adopción) -->
        <div id="camposAdopcion" style="display: none;">
            <h4><i class="fa-solid fa-file-signature"></i> Detalles de Adopción</h4>
            <div class="form-grid">
                <div class="form-group">
                    <label for="datos_adopcion_apellido">Apellido:</label>
                    <input type="text" id="datos_adopcion_apellido" name="datos_adopcion[apellido_solicitante]"
                           value="<?php echo e(old('datos_adopcion.apellido_solicitante')); ?>" class="form-control">
                </div>

                <div class="form-group">
                    <label for="datos_adopcion_direccion">Dirección:</label>
                    <input type="text" id="datos_adopcion_direccion" name="datos_adopcion[direccion]"
                           value="<?php echo e(old('datos_adopcion.direccion')); ?>" class="form-control">
                </div>

                <div class="form-group full-width">
                    <label for="datos_adopcion_experiencia">Experiencia con mascotas:</label>
                    <textarea id="datos_adopcion_experiencia" name="datos_adopcion[experiencia_mascotas]"
                              rows="3" class="form-control"><?php echo e(old('datos_adopcion.experiencia_mascotas')); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="datos_adopcion_tipo_vivienda">Tipo de vivienda:</label>
                    <select id="datos_adopcion_tipo_vivienda" name="datos_adopcion[tipo_vivienda]" class="form-control">
                        <option value="">Selecciona...</option>
                        <option value="casa" <?php echo e(old('datos_adopcion.tipo_vivienda') == 'casa' ? 'selected' : ''); ?>>Casa</option>
                        <option value="apartamento" <?php echo e(old('datos_adopcion.tipo_vivienda') == 'apartamento' ? 'selected' : ''); ?>>Apartamento</option>
                        <option value="finca" <?php echo e(old('datos_adopcion.tipo_vivienda') == 'finca' ? 'selected' : ''); ?>>Finca</option>
                        <option value="otro" <?php echo e(old('datos_adopcion.tipo_vivienda') == 'otro' ? 'selected' : ''); ?>>Otro</option>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label for="datos_adopcion_motivo">Motivo de adopción:</label>
                    <textarea id="datos_adopcion_motivo" name="datos_adopcion[motivo_adopcion]"
                              rows="3" class="form-control"><?php echo e(old('datos_adopcion.motivo_adopcion')); ?></textarea>
                </div>

                <div class="form-group full-width">
                    <label>Compromisos:</label>
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="datos_adopcion[compromiso_cuidado]" value="1"
                                   <?php echo e(old('datos_adopcion.compromiso_cuidado') ? 'checked' : ''); ?>>
                            Compromiso de cuidado responsable
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="datos_adopcion[compromiso_esterilizacion]" value="1"
                                   <?php echo e(old('datos_adopcion.compromiso_esterilizacion') ? 'checked' : ''); ?>>
                            Compromiso de esterilización
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="datos_adopcion[compromiso_seguimiento]" value="1"
                                   <?php echo e(old('datos_adopcion.compromiso_seguimiento') ? 'checked' : ''); ?>>
                            Acepta seguimiento post-adopción
                        </label>
                    </div>
                </div>
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
                      placeholder="Describe los detalles de la solicitud, justificación, observaciones..."><?php echo e(old('contenido')); ?></textarea>
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
                <i class="fa-solid fa-info-circle"></i> Describe detalladamente la solicitud. Mínimo 10 caracteres.
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="form-actions">
            <button type="submit" class="btn-submit">
                <i class="fa-solid fa-plus"></i> Crear Solicitud
            </button>
            <a href="<?php echo e(route('admin.solicitudes.index')); ?>" class="btn-cancel">
                <i class="fa-solid fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mostrar/ocultar datos manuales según selección de usuario
    const userSelect = document.getElementById('user_id');
    const datosManuales = document.getElementById('datosManuales');

    function toggleDatosManuales() {
        if (userSelect.value) {
            datosManuales.style.display = 'none';
            // Limpiar campos manuales si se seleccionó usuario
            document.getElementById('nombre_solicitante').value = '';
            document.getElementById('email_solicitante').value = '';
            document.getElementById('telefono_solicitante').value = '';
        } else {
            datosManuales.style.display = 'block';
        }
    }

    userSelect.addEventListener('change', toggleDatosManuales);
    toggleDatosManuales();

    // Mostrar/ocultar campos de adopción según tipo
    const tipoSelect = document.getElementById('tipo_solicitud');
    const camposAdopcion = document.getElementById('camposAdopcion');

    function toggleCamposAdopcion() {
        if (tipoSelect.value === 'adopcion') {
            camposAdopcion.style.display = 'block';
        } else {
            camposAdopcion.style.display = 'none';
        }
    }

    tipoSelect.addEventListener('change', toggleCamposAdopcion);
    toggleCamposAdopcion();
});
</script>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\solicitud\partials\create\form.blade.php ENDPATH**/ ?>