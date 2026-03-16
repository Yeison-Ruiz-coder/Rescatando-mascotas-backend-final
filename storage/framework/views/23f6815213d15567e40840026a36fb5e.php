<!-- Sección 1: Información Básica -->
<div class="form-section">
    <h4 class="section-title">
        <i class="fas fa-info-circle me-2"></i>Información Básica
    </h4>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="nombre_mascota" class="form-label">
                Nombre de la Mascota <span class="required">*</span>
            </label>
            <input type="text"
                   class="form-control form-control-custom <?php $__errorArgs = ['nombre_mascota'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   id="nombre_mascota"
                   name="nombre_mascota"
                   value="<?php echo e(old('nombre_mascota', $mascota->nombre_mascota)); ?>"
                   placeholder="Ej: Max, Luna, Toby..."
                   required>
            <?php $__errorArgs = ['nombre_mascota'];
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
            <label for="especie" class="form-label">
                Especie <span class="required">*</span>
            </label>
            <select class="form-select form-select-custom <?php $__errorArgs = ['especie'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    id="especie"
                    name="especie"
                    required>
                <option value="">Selecciona una especie</option>
                <option value="Perro" <?php echo e(old('especie', $mascota->especie) == 'Perro' ? 'selected' : ''); ?>>Perro</option>
                <option value="Gato" <?php echo e(old('especie', $mascota->especie) == 'Gato' ? 'selected' : ''); ?>>Gato</option>
                <option value="Conejo" <?php echo e(old('especie', $mascota->especie) == 'Conejo' ? 'selected' : ''); ?>>Conejo</option>
                <option value="Otro" <?php echo e(old('especie', $mascota->especie) == 'Otro' ? 'selected' : ''); ?>>Otro</option>
            </select>
            <?php $__errorArgs = ['especie'];
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

        <!-- Campo para Razas (selección múltiple) -->
        <div class="col-md-6">
            <label for="razas" class="form-label">
                Razas <span class="required">*</span>
            </label>
            <div class="multi-select-container">
                <select class="form-select form-select-custom multi-select <?php $__errorArgs = ['razas'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        id="razas"
                        name="razas[]"
                        multiple
                        size="3"
                        required>
                    <?php $__currentLoopData = $razas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $raza): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($raza->id); ?>"
                            <?php echo e(in_array($raza->id, old('razas', $mascota->razas->pluck('id')->toArray())) ? 'selected' : ''); ?>>
                            <?php echo e($raza->nombre_raza); ?> (<?php echo e($raza->especie); ?>)
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="form-help">
                <i class="fas fa-info-circle"></i> Mantén presionada la tecla Ctrl para seleccionar múltiples razas
            </div>
            <?php $__errorArgs = ['razas'];
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

        <div class="col-md-6">
            <label for="edad_aprox" class="form-label">
                Edad Aproximada (años) <span class="required">*</span>
            </label>
            <input type="number"
                   class="form-control form-control-custom <?php $__errorArgs = ['edad_aprox'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   id="edad_aprox"
                   name="edad_aprox"
                   value="<?php echo e(old('edad_aprox', $mascota->edad_aprox)); ?>"
                   min="0"
                   max="30"
                   step="0.5"
                   placeholder="Ej: 2.5"
                   required>
            <?php $__errorArgs = ['edad_aprox'];
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
            <label class="form-label">Género <span class="required">*</span></label>
            <div class="radio-group">
                <div class="form-check">
                    <input class="form-check-input <?php $__errorArgs = ['genero'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           type="radio"
                           name="genero"
                           id="genero_macho"
                           value="Macho"
                           <?php echo e(old('genero', $mascota->genero) == 'Macho' ? 'checked' : ''); ?>

                           required>
                    <label class="form-check-label" for="genero_macho">
                        <i class="fas fa-mars me-1"></i>Macho
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input <?php $__errorArgs = ['genero'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           type="radio"
                           name="genero"
                           id="genero_hembra"
                           value="Hembra"
                           <?php echo e(old('genero', $mascota->genero) == 'Hembra' ? 'checked' : ''); ?>>
                    <label class="form-check-label" for="genero_hembra">
                        <i class="fas fa-venus me-1"></i>Hembra
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input <?php $__errorArgs = ['genero'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           type="radio"
                           name="genero"
                           id="genero_desconocido"
                           value="Desconocido"
                           <?php echo e(old('genero', $mascota->genero) == 'Desconocido' ? 'checked' : ''); ?>>
                    <label class="form-check-label" for="genero_desconocido">
                        <i class="fas fa-question me-1"></i>Desconocido
                    </label>
                </div>
            </div>
            <?php $__errorArgs = ['genero'];
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

        <div class="col-md-6">
            <label for="estado" class="form-label">
                Estado Actual <span class="required">*</span>
            </label>
            <select class="form-select form-select-custom <?php $__errorArgs = ['estado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    id="estado"
                    name="estado"
                    required>
                <option value="">Selecciona un estado</option>
                <option value="En adopcion" <?php echo e(old('estado', $mascota->estado) == 'En adopcion' ? 'selected' : ''); ?>>
                    En adopción
                </option>
                <option value="Rescatada" <?php echo e(old('estado', $mascota->estado) == 'Rescatada' ? 'selected' : ''); ?>>
                    Rescatada
                </option>
                <option value="En acogida" <?php echo e(old('estado', $mascota->estado) == 'En acogida' ? 'selected' : ''); ?>>
                    En acogida
                </option>
                <option value="Adoptado" <?php echo e(old('estado', $mascota->estado) == 'Adoptado' ? 'selected' : ''); ?>>
                    Adoptado
                </option>
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
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-foreve\resources\views/admin/mascotas/partials/edit/form-basic-info.blade.php ENDPATH**/ ?>