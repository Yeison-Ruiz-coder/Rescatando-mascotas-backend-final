<div class="col-xl-4 col-lg-6 mb-4">
    <div class="card-mascota-moderna">
        <!-- Imagen con overlay -->
        <div class="card-imagen-container">
            <?php if($mascota->foto_principal): ?>
            <img src="<?php echo e(Storage::url($mascota->foto_principal)); ?>"
                 alt="<?php echo e($mascota->nombre_mascota); ?>">
            <?php else: ?>
            <div class="w-100 h-100 bg-gris-claro d-flex align-items-center justify-content-center">
                <i class="fas fa-paw fa-4x text-turquesa opacity-50"></i>
            </div>
            <?php endif; ?>
            <div class="overlay-mascota"></div>

            <!-- Badges flotantes -->
            <div class="badges-container">
                <span class="badge-moderno badge-especie-moderno">
                    <?php echo e($mascota->especie); ?>

                </span>
                <span class="badge-moderno badge-genero-moderno">
                    <?php echo e($mascota->genero); ?>

                </span>
                <span class="badge-moderno badge-edad-moderno">
                    <?php echo e($mascota->edad_aprox); ?> años
                </span>
            </div>
        </div>

        <!-- Contenido -->
        <div class="card-body-moderno">
            <h3 class="nombre-mascota"><?php echo e($mascota->nombre_mascota); ?></h3>

            <p class="descripcion-mascota">
                <?php echo e(Str::limit($mascota->descripcion, 150)); ?>

            </p>

            <!-- Información de fundación -->
            <?php if($mascota->fundacion): ?>
            <div class="info-fundacion">
                <i class="fas fa-home"></i>
                <span>Rescatado por: <?php echo e($mascota->fundacion->Nombre_1); ?></span>
            </div>
            <?php endif; ?>

            <!-- Botón -->
            <a href="<?php echo e(route('public.mascotas.show', $mascota->id)); ?>"
               class="btn-conocer-mas">
               <i class="fas fa-heart me-2"></i>Conocer a <?php echo e($mascota->nombre_mascota); ?>

            </a>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-foreve\resources\views/public/mascotas/partials/index/mascota-card.blade.php ENDPATH**/ ?>