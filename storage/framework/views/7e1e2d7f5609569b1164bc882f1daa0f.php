<!-- Imagen principal -->
<div class="card card-mascota mb-4">
    <div class="card-body p-0">
        <?php if($mascota->foto_principal): ?>
        <img src="<?php echo e(Storage::url($mascota->foto_principal)); ?>"
             class="img-fluid imagen-principal w-100"
             alt="<?php echo e($mascota->nombre_mascota); ?>"
             id="imagenPrincipal"
             style="max-height: 400px; object-fit: cover;">
        <?php else: ?>
        <div class="d-flex align-items-center justify-content-center bg-gris-claro"
             style="height: 400px;">
            <i class="fas fa-paw fa-5x text-turquesa opacity-50"></i>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Galería de fotos -->
<?php if($mascota->galeria_fotos && count($mascota->galeria_fotos) > 0): ?>
<div class="card card-mascota mb-4">
    <div class="card-header card-header-turquesa">
        <h6 class="mb-0"><i class="fas fa-images me-2"></i>Más Fotos</h6>
    </div>
    <div class="card-body">
        <div class="row g-2">
            <?php $__currentLoopData = $mascota->galeria_fotos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $foto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-4">
                <img src="<?php echo e(Storage::url($foto)); ?>"
                     class="gallery-thumb img-thumbnail"
                     alt="Foto <?php echo e($index + 1); ?>"
                     style="height: 100px; width: 100%; object-fit: cover; cursor: pointer;"
                     onclick="cambiarImagenPrincipal('<?php echo e(Storage::url($foto)); ?>')">
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function cambiarImagenPrincipal(nuevaSrc) {
    const imgPrincipal = document.getElementById('imagenPrincipal');
    if (imgPrincipal) {
        imgPrincipal.src = nuevaSrc;
    }
}
</script>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\mascotas\partials\show\gallery.blade.php ENDPATH**/ ?>