<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/pages/mascotas/edit.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-3 px-lg-5 py-4">

    <!-- Cards de mensajes -->
    <?php if(session('success')): ?>
        <?php echo $__env->make('cards.registro-exitoso', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <?php echo $__env->make('cards.error-registro', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>

    <!-- Header Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="edit-mascota-header text-center">
                <h1 class="display-5 fw-bold">
                    <i class="fas fa-edit me-3"></i>Editar Mascota
                </h1>
                <p class="lead text-muted">Actualiza la información de <strong class="text-turquesa"><?php echo e($mascota->Nombre_mascota); ?></strong></p>
            </div>
        </div>
    </div>

    <!-- Formulario -->
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <div class="card form-mascota-card shadow-lg border-0">
                <div class="card-header form-mascota-header">
                    <h3 class="mb-0">
                        <i class="fas fa-paw me-2"></i>Formulario de Edición
                    </h3>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('mascotas.update', $mascota)); ?>" method="POST" enctype="multipart/form-data" id="mascotaForm">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <!-- Sección 1: Información Básica -->
                        <div class="form-section">
                            <h4 class="section-title">
                                <i class="fas fa-info-circle me-2"></i>Información Básica
                            </h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="Nombre_mascota" class="form-label">
                                        Nombre de la Mascota <span class="required">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control form-control-custom"
                                           id="Nombre_mascota"
                                           name="Nombre_mascota"
                                           value="<?php echo e(old('Nombre_mascota', $mascota->Nombre_mascota)); ?>"
                                           placeholder="Ej: Max, Luna, Toby..."
                                           required>
                                </div>

                                <div class="col-md-6">
                                    <label for="Especie" class="form-label">
                                        Especie <span class="required">*</span>
                                    </label>
                                    <select class="form-select form-select-custom"
                                            id="Especie"
                                            name="Especie"
                                            required>
                                        <option value="">Selecciona una especie</option>
                                        <option value="Perro" <?php echo e(old('Especie', $mascota->Especie) == 'Perro' ? 'selected' : ''); ?>>🐕 Perro</option>
                                        <option value="Gato" <?php echo e(old('Especie', $mascota->Especie) == 'Gato' ? 'selected' : ''); ?>>🐈 Gato</option>
                                        <option value="Conejo" <?php echo e(old('Especie', $mascota->Especie) == 'Conejo' ? 'selected' : ''); ?>>🐇 Conejo</option>
                                        <option value="Otro" <?php echo e(old('Especie', $mascota->Especie) == 'Otro' ? 'selected' : ''); ?>>🐾 Otro</option>
                                    </select>
                                </div>

                                <!-- Campo para Razas (selección múltiple mejorada) -->
                                <div class="col-md-6">
                                    <label for="razas" class="form-label">
                                        Razas <span class="required">*</span>
                                    </label>
                                    <div class="multi-select-container">
                                        <select class="form-select form-select-custom multi-select"
                                                id="razas"
                                                name="razas[]"
                                                multiple
                                                size="3"
                                                required>
                                            <option value="">Selecciona una o más razas</option>
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
                                </div>

                                <div class="col-md-6">
                                    <label for="Edad_aprox" class="form-label">
                                        Edad Aproximada (años) <span class="required">*</span>
                                    </label>
                                    <input type="number"
                                           class="form-control form-control-custom"
                                           id="Edad_aprox"
                                           name="Edad_aprox"
                                           value="<?php echo e(old('Edad_aprox', $mascota->Edad_aprox)); ?>"
                                           min="0"
                                           max="30"
                                           step="0.5"
                                           placeholder="Ej: 2.5"
                                           required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Género <span class="required">*</span></label>
                                    <div class="radio-group">
                                        <div class="form-check">
                                            <input class="form-check-input"
                                                   type="radio"
                                                   name="Genero"
                                                   id="GeneroMacho"
                                                   value="Macho"
                                                   <?php echo e(old('Genero', $mascota->Genero) == 'Macho' ? 'checked' : ''); ?>

                                                   required>
                                            <label class="form-check-label" for="GeneroMacho">
                                                <i class="fas fa-mars me-1"></i>Macho
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input"
                                                   type="radio"
                                                   name="Genero"
                                                   id="GeneroHembra"
                                                   value="Hembra"
                                                   <?php echo e(old('Genero', $mascota->Genero) == 'Hembra' ? 'checked' : ''); ?>>
                                            <label class="form-check-label" for="GeneroHembra">
                                                <i class="fas fa-venus me-1"></i>Hembra
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="estado" class="form-label">
                                        Estado Actual <span class="required">*</span>
                                    </label>
                                    <select class="form-select form-select-custom"
                                            id="estado"
                                            name="estado"
                                            required>
                                        <option value="">Selecciona un estado</option>
                                        <option value="En adopcion" <?php echo e(old('estado', $mascota->estado) == 'En adopcion' ? 'selected' : ''); ?>>
                                            <i class="fas fa-heart me-1"></i>En adopción
                                        </option>
                                        <option value="Rescatada" <?php echo e(old('estado', $mascota->estado) == 'Rescatada' ? 'selected' : ''); ?>>
                                            <i class="fas fa-shield-alt me-1"></i>Rescatada
                                        </option>
                                        <option value="Adoptado" <?php echo e(old('estado', $mascota->estado) == 'Adoptado' ? 'selected' : ''); ?>>
                                            <i class="fas fa-home me-1"></i>Adoptado
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Sección 2: Ubicación y Descripción -->
                        <div class="form-section">
                            <h4 class="section-title">
                                <i class="fas fa-map-marker-alt me-2"></i>Ubicación y Descripción
                            </h4>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="Lugar_rescate" class="form-label">
                                        Lugar donde se encuentra <span class="required">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control form-control-custom"
                                           id="Lugar_rescate"
                                           name="Lugar_rescate"
                                           value="<?php echo e(old('Lugar_rescate', $mascota->Lugar_rescate)); ?>"
                                           placeholder="Ej: Parque Central, Calle Principal #123..."
                                           required>
                                </div>

                                <div class="col-12">
                                    <label for="Descripcion" class="form-label">
                                        Descripción <span class="required">*</span>
                                    </label>
                                    <textarea class="form-control form-control-custom"
                                              id="Descripcion"
                                              name="Descripcion"
                                              rows="5"
                                              placeholder="Describe a la mascota: carácter, comportamiento, condición de salud, necesidades especiales..."
                                              required><?php echo e(old('Descripcion', $mascota->Descripcion)); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Sección 3: Salud y Vacunas -->
                        <div class="form-section">
                            <h4 class="section-title">
                                <i class="fas fa-syringe me-2"></i>Salud y Vacunas
                            </h4>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="vacunas_aplicadas" class="form-label">
                                        Vacunas Aplicadas
                                    </label>
                                    <div class="multi-select-container">
                                        <select class="form-select form-select-custom multi-select"
                                                id="vacunas_aplicadas"
                                                name="vacunas_aplicadas[]"
                                                multiple
                                                size="3">
                                            <option value="">Selecciona las vacunas aplicadas</option>
                                            <?php $__currentLoopData = $vacunas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vacuna): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($vacuna->id); ?>"
                                                    <?php echo e(in_array($vacuna->id, old('vacunas_aplicadas', $mascota->tiposVacunas->pluck('id')->toArray())) ? 'selected' : ''); ?>>
                                                    <?php echo e($vacuna->nombre_vacuna); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                    <div class="form-help">
                                        <i class="fas fa-info-circle"></i> Mantén presionada la tecla Ctrl para seleccionar múltiples vacunas
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección 4: Fotografía -->
                        <div class="form-section">
                            <h4 class="section-title">
                                <i class="fas fa-camera me-2"></i>Fotografía
                            </h4>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="Foto" class="form-label">
                                        Foto Principal
                                    </label>

                                    <!-- Mostrar imagen actual -->
                                    <?php if($mascota->Foto): ?>
                                    <div class="current-image mb-3">
                                        <p class="text-muted small mb-2">Imagen actual:</p>
                                        <img src="<?php echo e(Storage::url($mascota->Foto)); ?>"
                                             alt="<?php echo e($mascota->Nombre_mascota); ?>"
                                             class="img-thumbnail current-img">
                                    </div>
                                    <?php endif; ?>

                                    <input type="file"
                                           class="form-control form-control-custom"
                                           id="Foto"
                                           name="Foto"
                                           accept="image/*">
                                    <div class="form-help">
                                        <i class="fas fa-info-circle"></i> Deja vacío para mantener la imagen actual • Formatos: JPG, PNG, GIF • Máx. 2MB
                                    </div>
                                    <?php $__errorArgs = ['Foto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="error-message"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <!-- Galería de Fotos -->
                                <div class="col-12">
                                    <label class="form-label">
                                        Galería de Fotos (Máximo 3 imágenes)
                                    </label>
                                    <div class="gallery-upload-container">
                                        <input type="file"
                                               class="form-control form-control-custom"
                                               id="galeria_fotos"
                                               name="galeria_fotos[]"
                                               multiple
                                               accept="image/*"
                                               max="3">
                                        <div class="form-help">
                                            <i class="fas fa-info-circle"></i> Puedes seleccionar hasta 3 imágenes adicionales para la galería
                                        </div>

                                        <!-- Mostrar galería actual -->
                                        <?php if($mascota->galeria_fotos && count($mascota->galeria_fotos) > 0): ?>
                                        <div class="current-gallery mt-3">
                                            <p class="text-muted small mb-2">Galería actual:</p>
                                            <div class="row g-2">
                                                <?php $__currentLoopData = $mascota->galeria_fotos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $foto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="col-4">
                                                    <div class="gallery-item position-relative">
                                                        <img src="<?php echo e(Storage::url($foto['ruta'])); ?>"
                                                             alt="Foto <?php echo e($index + 1); ?>"
                                                             class="img-thumbnail w-100">
                                                        <div class="gallery-overlay">
                                                            <small>Foto <?php echo e($index + 1); ?></small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección 5: Fechas y Fundación -->
                        <div class="form-section">
                            <h4 class="section-title">
                                <i class="fas fa-calendar-alt me-2"></i>Fechas y Organización
                            </h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="Fecha_ingreso" class="form-label">
                                        Fecha de Ingreso <span class="required">*</span>
                                    </label>
                                    <input type="date"
                                           class="form-control form-control-custom"
                                           id="Fecha_ingreso"
                                           name="Fecha_ingreso"
                                           value="<?php echo e(old('Fecha_ingreso', $mascota->Fecha_ingreso)); ?>"
                                           required>
                                </div>
                                <div class="col-md-6">
                                    <label for="Fecha_salida" class="form-label">
                                        Fecha de Salida (si aplica)
                                    </label>
                                    <input type="date"
                                           class="form-control form-control-custom"
                                           id="Fecha_salida"
                                           name="Fecha_salida"
                                           value="<?php echo e(old('Fecha_salida', $mascota->Fecha_salida)); ?>">
                                </div>
                                <div class="col-12">
                                    <label for="fundacion_id" class="form-label">
                                        Fundación (opcional)
                                    </label>
                                    <select class="form-select form-select-custom"
                                            id="fundacion_id"
                                            name="fundacion_id">
                                        <option value="">Sin fundación asignada</option>
                                        <?php $__currentLoopData = $fundaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fundacion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($fundacion->id); ?>"
                                                <?php echo e(old('fundacion_id', $mascota->fundacion_id) == $fundacion->id ? 'selected' : ''); ?>>
                                                <?php echo e($fundacion->Nombre_1); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="form-actions">
                            <button type="submit" class="btn btn-submit">
                                <i class="fas fa-save me-2"></i>Actualizar Mascota
                            </button>
                            <a href="<?php echo e(route('admin.mascotas.show', $mascota)); ?>" class="btn btn-cancel">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\users\edit.blade.php ENDPATH**/ ?>