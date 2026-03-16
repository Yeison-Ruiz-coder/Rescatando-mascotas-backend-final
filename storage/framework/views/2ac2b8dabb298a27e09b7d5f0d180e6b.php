<!-- Card de Mascota -->
<div class="card mascota-card shadow-sm h-100">
    <!-- Imagen con Badge de Estado -->
    <div class="mascota-img-container position-relative overflow-hidden">
        <img src="<?php echo e($mascota->foto_principal ? Storage::url($mascota->foto_principal) : asset('img/mascota-placeholder.jpg')); ?>"
             class="mascota-card-img"
             alt="<?php echo e($mascota->nombre_mascota); ?>"
             onerror="this.onerror=null; this.src='<?php echo e(asset('img/mascota-placeholder.jpg')); ?>'">

        <div class="mascota-badge
            <?php if($mascota->estado == 'En adopcion'): ?> badge-adopcion
            <?php elseif($mascota->estado == 'Adoptado'): ?> badge-adoptado
            <?php else: ?> badge-rescatada <?php endif; ?>">
            <i class="fas
                <?php if($mascota->estado == 'En adopcion'): ?> fa-heart
                <?php elseif($mascota->estado == 'Adoptado'): ?> fa-home
                <?php else: ?> fa-shield-alt <?php endif; ?> me-1">
            </i>
            <?php echo e($mascota->estado); ?>

        </div>
    </div>

    <!-- Contenido de la Card -->
    <div class="card-body d-flex flex-column">
        <!-- Nombre y Especie -->
        <div class="mb-3">
            <h5 class="mascota-nombre"><?php echo e($mascota->nombre_mascota); ?></h5>
            <div class="mascota-especie">
                <i class="fas fa-paw me-1"></i>
                <?php echo e($mascota->especie); ?>

                <?php if($mascota->raza): ?>
                <span class="text-muted">• <?php echo e($mascota->raza); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Información Detallada -->
        <div class="mascota-info mb-3">
            <div class="info-item">
                <i class="fas fa-birthday-cake text-fucsia"></i>
                <span><?php echo e($mascota->edad_aprox); ?> años</span>
            </div>
            <div class="info-item">
                <i class="fas fa-venus-mars text-fucsia"></i>
                <span><?php echo e($mascota->genero); ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-map-marker-alt text-fucsia"></i>
                <span><?php echo e(Str::limit($mascota->lugar_rescate, 40)); ?></span>
            </div>
        </div>

        <!-- Descripción -->
        <div class="mascota-descripcion mb-3 flex-grow-1">
            <p class="text-muted small">
                <?php echo e(Str::limit($mascota->descripcion, 120)); ?>

            </p>
        </div>

        <!-- Botones de Acción -->
        <div class="mascota-actions mt-auto">
            <div class="d-grid gap-2">
                <a href="<?php echo e(route('admin.mascotas.show', $mascota)); ?>"
                   class="btn btn-primary btn-sm">
                    <i class="fas fa-eye me-1"></i>Ver Detalles
                </a>
                <a href="<?php echo e(route('admin.mascotas.edit', $mascota)); ?>"
                   class="btn btn-outline-warning btn-sm">
                    <i class="fas fa-edit me-1"></i>Editar
                </a>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-foreve\resources\views/components/modules/mascotas/card.blade.php ENDPATH**/ ?>