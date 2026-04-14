<!-- Mascotas Section -->
<section id="mascotas" class="adopta-section">
    <div class="container">
        <h2 class="section-title">Mascotas Disponibles para Adoptar</h2>
        <p class="section-subtitle">Conoce a nuestros amigos que buscan un hogar lleno de amor y cuidado.</p>

        <div class="row g-4">
            <?php if(isset($mascotasRecientes) && $mascotasRecientes->count() > 0): ?>
                <?php $__currentLoopData = $mascotasRecientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mascota): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-xl-4 col-lg-6 mb-4 animate-fade-in-up">
                    <div class="card-mascota-moderna">
                        <!-- Imagen con overlay -->
                        <div class="card-imagen-container">
                            <?php if(isset($mascota->foto_principal) && $mascota->foto_principal): ?>
                                <img src="<?php echo e(asset('storage/' . $mascota->foto_principal)); ?>"
                                     alt="<?php echo e($mascota->nombre_mascota); ?>">
                            <?php elseif(isset($mascota->Foto) && $mascota->Foto): ?>
                                
                                <img src="<?php echo e(asset('storage/' . $mascota->Foto)); ?>"
                                     alt="<?php echo e($mascota->nombre_mascota ?? $mascota->Nombre_mascota ?? 'Mascota'); ?>">
                            <?php else: ?>
                                <div class="w-100 h-100 bg-gris-claro d-flex align-items-center justify-content-center">
                                    <i class="fas fa-paw fa-4x text-turquesa opacity-50"></i>
                                </div>
                            <?php endif; ?>
                            <div class="overlay-mascota"></div>

                            <!-- Badges flotantes -->
                            <div class="badges-container">
                                <span class="badge-moderno badge-especie-moderno">
                                    <?php echo e($mascota->especie ?? $mascota->Especie ?? 'Mascota'); ?>

                                </span>
                                <span class="badge-moderno badge-genero-moderno">
                                    <?php echo e($mascota->genero ?? $mascota->Genero ?? 'N/A'); ?>

                                </span>
                                <span class="badge-moderno badge-edad-moderno">
                                    <?php echo e($mascota->edad_aprox ?? $mascota->Edad_aprox ?? '?'); ?> años
                                </span>
                            </div>
                        </div>

                        <!-- Contenido -->
                        <div class="card-body-moderno">
                            <h3 class="nombre-mascota"><?php echo e($mascota->nombre_mascota ?? $mascota->Nombre_mascota ?? 'Mascota'); ?></h3>

                            <p class="descripcion-mascota">
                                <?php echo e(Str::limit($mascota->descripcion ?? $mascota->Descripcion ?? 'Sin descripción disponible', 150)); ?>

                            </p>

                            <!-- Información de fundación -->
                            <?php if(isset($mascota->fundacion)): ?>
                                <div class="info-fundacion">
                                    <i class="fas fa-home"></i>
                                    <span>Rescatado por: <?php echo e($mascota->fundacion->Nombre_1 ?? 'Fundación'); ?></span>
                                </div>
                            <?php endif; ?>

                            <!-- Botones -->
                            <div class="d-flex gap-2">
                                <a href="<?php echo e(route('public.mascotas.show', $mascota->id)); ?>"
                                   class="btn-conocer-mas flex-fill">
                                   <i class="fas fa-heart me-2"></i>Conocer más
                                </a>
                                <a href="<?php echo e(route('public.adopciones.solicitar', $mascota->id)); ?>"
                                   class="btn-adoptar-home">
                                   <i class="fas fa-home me-2"></i>Adoptar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php else: ?>
                <!-- Mensaje cuando no hay mascotas -->
                <div class="col-12 text-center">
                    <p class="lead">Pronto tendremos mascotas disponibles para adopción.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-5">
            <a href="<?php echo e(route('public.mascotas.index')); ?>" class="btn-primary-custom">
                Ver Todas las Mascotas
            </a>
        </div>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\home\partials\mascotas.blade.php ENDPATH**/ ?>