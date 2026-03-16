<!-- Sección 4: Galería de Fotos -->
<div class="form-section">
    <h4 class="section-title">
        <i class="fas fa-camera me-2"></i>Galería de Fotos
    </h4>
    <div class="row g-3">
        <!-- Foto Principal -->
        <div class="col-md-6">
            <label for="foto_principal" class="form-label">
                Foto Principal <span class="required">*</span>
            </label>
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
                   accept="image/jpeg,image/png,image/jpg,image/gif"
                   required>
            <div class="form-help">
                <i class="fas fa-info-circle"></i> Esta será la foto destacada • Formatos: JPG, PNG, GIF • Máx. 2MB
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

            <!-- Preview foto principal -->
            <div id="preview_principal_container" class="mt-2" style="display: none;">
                <img id="preview_principal" src="#" alt="Vista previa" class="img-thumbnail" style="max-height: 150px;">
            </div>
        </div>

        <!-- Galería de Fotos -->
        <div class="col-md-6">
            <label for="galeria_fotos" class="form-label">
                Galería de Fotos
            </label>
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

            <!-- Preview de galería -->
            <div id="preview_galeria_container" class="mt-2" style="display: none;">
                <h6 class="preview-title">Vista previa:</h6>
                <div class="gallery-preview" id="galleryPreview"></div>
            </div>
        </div>
    </div>
</div>

<script>
// Script para previsualizar foto principal
document.getElementById('foto_principal').addEventListener('change', function(e) {
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

// Script para previsualizar galería
document.getElementById('galeria_fotos').addEventListener('change', function(e) {
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
                    previewItem.className = 'gallery-preview-item';
                    previewItem.innerHTML = `
                        <img src="${e.target.result}" alt="Preview ${index + 1}" class="img-thumbnail" style="height: 80px; width: 80px; object-fit: cover; margin: 5px;">
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
</script>

<style>
.gallery-preview {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}
.gallery-preview-item {
    position: relative;
    display: inline-block;
}
</style>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-foreve\resources\views/admin/mascotas/partials/create/form-gallery.blade.php ENDPATH**/ ?>