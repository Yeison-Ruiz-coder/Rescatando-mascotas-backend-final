<div class="modal fade modal-mascota" id="modalAdopcion" tabindex="-1" aria-labelledby="modalAdopcionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-white" id="modalAdopcionLabel">
                    <i class="fas fa-heart me-2"></i>Solicitar Adopción de <?php echo e($mascota->nombre_mascota); ?>

                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert bg-light-info border-0 mb-4">
                    <i class="fas fa-info-circle text-turquesa me-2"></i>
                    Para solicitar la adopción, por favor contacta directamente a la fundación.
                </div>

                <?php if($mascota->fundacion): ?>
                <div class="card border-turquesa mb-4">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0 text-turquesa fw-bold">Información de Contacto</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong class="text-turquesa d-block mb-1">Fundación:</strong>
                                <span class="text-muted"><?php echo e($mascota->fundacion->Nombre_1); ?></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong class="text-turquesa d-block mb-1">Teléfono:</strong>
                                <span class="text-muted"><?php echo e($mascota->fundacion->Telefono ?? 'No especificado'); ?></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong class="text-turquesa d-block mb-1">Email:</strong>
                                <span class="text-muted"><?php echo e($mascota->fundacion->Email ?? 'No especificado'); ?></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong class="text-turquesa d-block mb-1">Dirección:</strong>
                                <span class="text-muted"><?php echo e($mascota->fundacion->Direccion ?? 'No especificada'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="p-3 bg-gris-claro rounded">
                    <p class="text-muted mb-2 small">
                        <i class="fas fa-clock text-fucsia me-2"></i>
                        <strong>Proceso de adopción:</strong>
                    </p>
                    <ul class="text-muted small mb-0 ps-3">
                        <li>Entrevista personal</li>
                        <li>Visita domiciliaria</li>
                        <li>Seguimiento post-adopción</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-turquesa" data-bs-dismiss="modal">Cerrar</button>
                <?php if($mascota->fundacion): ?>
                <a href="tel:<?php echo e($mascota->fundacion->Telefono); ?>" class="btn btn-fucsia">
                    <i class="fas fa-phone me-2"></i>Llamar Ahora
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\mascotas\partials\show\adoption-modal.blade.php ENDPATH**/ ?>