<!-- Sección 4: Fotografía -->
<div class="form-section">
    <h4 class="section-title">
        <i class="fas fa-camera me-2"></i>Fotografía
    </h4>
    <div class="row g-3">
        <!-- Foto Principal -->
        <div class="col-md-6">
            <label for="foto_principal" class="form-label">
                Foto Principal
            </label>

            <!-- Mostrar imagen actual si existe -->
            <?php if($mascota->foto_principal): ?>
            <div class="current-image mb-3">
                <p class="text-muted small mb-2">Imagen actual:</p>
                <div class="position-relative d-inline-block">
                    <img src="<?php echo e(Storage::url($mascota->foto_principal)); ?>"
                         alt="<?php echo e($mascota->nombre_mascota); ?>"
                         class="img-thumbnail"
                         style="max-height: 150px;">
                    <button type="button"
                            class="btn btn-sm btn-danger position-absolute top-0 end-0"
                            onclick="eliminarFotoPrincipal(<?php echo e($mascota->id); ?>)"
                            title="Eliminar foto principal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <input type="file"
                   class="form-control form-control-custom <?php $__errorArgs = ['foto_principal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   id="foto_principal"
                   name="foto_principal"
                   accept="image/jpeg,image/png,image/jpg,image/gif">
            <div class="form-help">
                <i class="fas fa-info-circle"></i> Deja vacío para mantener la imagen actual • Formatos: JPG, PNG, GIF • Máx. 2MB
            </div>
            <?php $__errorArgs = ['foto_principal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="invalid-feedback"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            <!-- Preview nueva foto principal -->
            <div id="preview_principal_container" class="mt-2" style="display: none;">
                <p class="text-muted small mb-2">Nueva imagen:</p>
                <img id="preview_principal" src="#" alt="Vista previa" class="img-thumbnail" style="max-height: 150px;">
            </div>
        </div>

        <!-- Galería de Fotos -->
        <div class="col-md-6">
            <label class="form-label">
                Galería de Fotos
            </label>

            <!-- Mostrar galería actual si existe -->
            <?php if($mascota->galeria_fotos && count($mascota->galeria_fotos) > 0): ?>
            <div class="current-gallery mb-3">
                <p class="text-muted small mb-2">Galería actual:</p>
                <div class="d-flex flex-wrap gap-2">
                    <?php $__currentLoopData = $mascota->galeria_fotos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $foto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="position-relative" style="width: 80px; height: 80px;">
                        <img src="<?php echo e(Storage::url($foto)); ?>"
                             alt="Foto <?php echo e($index + 1); ?>"
                             class="img-thumbnail w-100 h-100"
                             style="object-fit: cover;">
                        <button type="button"
                                class="btn btn-sm btn-danger position-absolute top-0 end-0 p-0 px-1"
                                onclick="eliminarFotoGaleria(<?php echo e($mascota->id); ?>, '<?php echo e($foto); ?>', this)"
                                title="Eliminar foto">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>

            <input type="file"
                   class="form-control form-control-custom <?php $__errorArgs = ['galeria_fotos'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> <?php $__errorArgs = ['galeria_fotos.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   id="galeria_fotos"
                   name="galeria_fotos[]"
                   multiple
                   accept="image/jpeg,image/png,image/jpg,image/gif">
            <div class="form-help">
                <i class="fas fa-info-circle"></i> Puedes seleccionar múltiples fotos adicionales • Máx. 2MB cada una
            </div>
            <?php $__errorArgs = ['galeria_fotos'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="invalid-feedback"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <?php $__errorArgs = ['galeria_fotos.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="invalid-feedback"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            <!-- Preview de nuevas fotos de galería -->
            <div id="preview_galeria_container" class="mt-2" style="display: none;">
                <p class="text-muted small mb-2">Nuevas imágenes:</p>
                <div class="gallery-preview d-flex flex-wrap gap-2" id="galleryPreview"></div>
            </div>
        </div>
    </div>
</div>

<script>
// Previsualizar foto principal
document.getElementById('foto_principal')?.addEventListener('change', function(e) {
    const previewContainer = document.getElementById('preview_principal_container');
    const previewImg = document.getElementById('preview_principal');

    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewContainer.style.display = 'block';
        }
        reader.readAsDataURL(this.files[0]);
    } else {
        previewContainer.style.display = 'none';
        previewImg.src = '#';
    }
});

// Previsualizar galería
document.getElementById('galeria_fotos')?.addEventListener('change', function(e) {
    const previewContainer = document.getElementById('preview_galeria_container');
    const galleryPreview = document.getElementById('galleryPreview');

    galleryPreview.innerHTML = '';

    if (this.files && this.files.length > 0) {
        previewContainer.style.display = 'block';

        Array.from(this.files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'position-relative';
                    previewItem.style.width = '80px';
                    previewItem.style.height = '80px';
                    previewItem.innerHTML = `
                        <img src="${e.target.result}" alt="Preview ${index + 1}"
                             class="img-thumbnail w-100 h-100"
                             style="object-fit: cover;">
                        <span class="badge bg-primary position-absolute top-0 start-0">${index + 1}</span>
                    `;
                    galleryPreview.appendChild(previewItem);
                };
                reader.readAsDataURL(file);
            }
        });
    } else {
        previewContainer.style.display = 'none';
    }
});

// Funciones para eliminar fotos (requieren rutas AJAX)
function eliminarFotoPrincipal(mascotaId) {
    if (confirm('¿Eliminar foto principal?')) {
        // Aquí iría la llamada AJAX para eliminar
        fetch(`/admin/mascotas/${mascotaId}/foto-principal`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            if (response.ok) {
                location.reload();
            }
        });
    }
}

function eliminarFotoGaleria(mascotaId, fotoPath, button) {
    if (confirm('¿Eliminar esta foto de la galería?')) {
        fetch(`/admin/mascotas/${mascotaId}/foto-galeria`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ foto: fotoPath })
        }).then(response => {
            if (response.ok) {
                button.closest('.position-relative').remove();
            }
        });
    }
}
</script>

<style>
.gallery-preview {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}
</style>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-foreve\resources\views/admin/mascotas/partials/edit/form-gallery.blade.php ENDPATH**/ ?>