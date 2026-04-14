
<div class="card-details">

    <!-- Estado Actual con botón de cambio rápido -->
    <div class="detail-group status-group">
        <label>Estado Actual:</label>
        <div class="status-with-actions">
            <?php
                $estado_class = strtolower(str_replace('_', '-', $solicitud->estado ?? 'pendiente'));
                $estado_texto = str_replace('_', ' ', ucfirst($solicitud->estado ?? 'pendiente'));
            ?>
            <span class="status-badge large <?php echo e($estado_class); ?>">
                <?php echo e($estado_texto); ?>

            </span>

            <?php if($solicitud->isPendiente() || $solicitud->isEnRevision()): ?>
            <button type="button" class="btn-action quick-status-btn" onclick="openStatusModal()">
                <i class="fa-solid fa-pen"></i> Cambiar Estado
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Información Básica -->
    <div class="details-grid">
        <div class="detail-group">
            <label><i class="fa-solid fa-tag"></i> Tipo de Solicitud:</label>
            <p>
                <span class="tipo-badge <?php echo e($solicitud->tipo_solicitud); ?>">
                    <?php echo e(ucfirst($solicitud->tipo_solicitud)); ?>

                </span>
            </p>
        </div>

        <div class="detail-group">
            <label><i class="fa-solid fa-calendar"></i> Fecha de Solicitud:</label>
            <p><?php echo e($solicitud->fecha_solicitud->format('d/m/Y H:i')); ?></p>
        </div>

        <div class="detail-group">
            <label><i class="fa-solid fa-user"></i> Solicitante:</label>
            <?php if($solicitud->usuario): ?>
                <p>
                    <strong><?php echo e($solicitud->usuario->name ?? $solicitud->usuario->nombre_completo); ?></strong>
                    <br>
                    <small class="text-muted">(Usuario registrado)</small>
                </p>
            <?php else: ?>
                <p>
                    <strong><?php echo e($solicitud->nombre_solicitante); ?></strong>
                    <?php if($solicitud->esAdopcion() && $apellido = $solicitud->getDatoAdopcion('apellido_solicitante')): ?>
                        <strong> <?php echo e($apellido); ?></strong>
                    <?php endif; ?>
                    <br>
                    <small><?php echo e($solicitud->email_solicitante); ?> | <?php echo e($solicitud->telefono_solicitante); ?></small>
                </p>
            <?php endif; ?>
        </div>

        <div class="detail-group">
            <label><i class="fa-solid fa-clock"></i> Creada el:</label>
            <p><?php echo e($solicitud->created_at->format('d/m/Y H:i')); ?></p>
        </div>
    </div>

    <!-- Información del Item Solicitado -->
    <?php if($solicitud->solicitable): ?>
    <div class="detail-group full-width">
        <label><i class="fa-solid fa-paw"></i> Item Solicitado:</label>
        <div class="solicitable-info">
            <?php if($solicitud->solicitable_type == 'App\Models\Mascota'): ?>
                <p>
                    <strong>Mascota:</strong>
                    <a href="<?php echo e(route('admin.mascotas.show', $solicitud->solicitable_id)); ?>">
                        <?php echo e($solicitud->solicitable->nombre ?? 'Mascota #' . $solicitud->solicitable_id); ?>

                    </a>
                </p>
            <?php else: ?>
                <p><strong>Tipo:</strong> <?php echo e(class_basename($solicitud->solicitable_type)); ?></p>
                <p><strong>ID:</strong> #<?php echo e($solicitud->solicitable_id); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Datos Específicos de Adopción (si aplica) -->
    <?php if($solicitud->esAdopcion() && $solicitud->datos_adicionales): ?>
    <div class="detail-group full-width">
        <label><i class="fa-solid fa-file-signature"></i> Detalles de Adopción:</label>
        <div class="adopcion-details-grid">
            <?php if($direccion = $solicitud->getDatoAdopcion('direccion')): ?>
            <div class="detail-item">
                <span class="detail-label">Dirección:</span>
                <span class="detail-value"><?php echo e($direccion); ?></span>
            </div>
            <?php endif; ?>

            <?php if($experiencia = $solicitud->getDatoAdopcion('experiencia_mascotas')): ?>
            <div class="detail-item">
                <span class="detail-label">Experiencia con mascotas:</span>
                <span class="detail-value"><?php echo e($experiencia); ?></span>
            </div>
            <?php endif; ?>

            <?php if($vivienda = $solicitud->getDatoAdopcion('tipo_vivienda')): ?>
            <div class="detail-item">
                <span class="detail-label">Tipo de vivienda:</span>
                <span class="detail-value"><?php echo e($vivienda); ?></span>
            </div>
            <?php endif; ?>

            <?php if($motivo = $solicitud->getDatoAdopcion('motivo_adopcion')): ?>
            <div class="detail-item">
                <span class="detail-label">Motivo de adopción:</span>
                <span class="detail-value"><?php echo e($motivo); ?></span>
            </div>
            <?php endif; ?>

            <!-- Compromisos -->
            <div class="detail-item compromisos">
                <span class="detail-label">Compromisos:</span>
                <div class="compromisos-list">
                    <?php if($solicitud->getDatoAdopcion('compromiso_cuidado')): ?>
                    <span class="compromiso-badge cumplido">
                        <i class="fa-solid fa-check"></i> Cuidado responsable
                    </span>
                    <?php endif; ?>
                    <?php if($solicitud->getDatoAdopcion('compromiso_esterilizacion')): ?>
                    <span class="compromiso-badge cumplido">
                        <i class="fa-solid fa-check"></i> Esterilización
                    </span>
                    <?php endif; ?>
                    <?php if($solicitud->getDatoAdopcion('compromiso_seguimiento')): ?>
                    <span class="compromiso-badge cumplido">
                        <i class="fa-solid fa-check"></i> Seguimiento
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Contenido / Justificación -->
    <div class="detail-group full-width">
        <label><i class="fa-solid fa-file-lines"></i> Contenido / Justificación:</label>
        <div class="content-box">
            <?php echo e($solicitud->contenido ?: 'Sin contenido adicional'); ?>

        </div>
    </div>

    <!-- Notas Internas (solo visible para admin) -->
    <?php if($solicitud->notas_internas): ?>
    <div class="detail-group full-width">
        <label><i class="fa-solid fa-lock"></i> Notas Internas:</label>
        <div class="content-box internal-notes">
            <?php echo e($solicitud->notas_internas); ?>

        </div>
    </div>
    <?php endif; ?>

    <!-- Información de Revisión -->
    <?php if($solicitud->revisado_por || $solicitud->razon_rechazo): ?>
    <div class="detail-group full-width revision-info">
        <label><i class="fa-solid fa-clipboard-check"></i> Información de Revisión:</label>
        <?php if($solicitud->revisor): ?>
        <p><strong>Revisado por:</strong> <?php echo e($solicitud->revisor->name); ?></p>
        <?php endif; ?>
        <?php if($solicitud->fecha_revision): ?>
        <p><strong>Fecha de revisión:</strong> <?php echo e($solicitud->fecha_revision->format('d/m/Y H:i')); ?></p>
        <?php endif; ?>
        <?php if($solicitud->razon_rechazo): ?>
        <div class="razon-rechazo">
            <strong>Razón de rechazo:</strong>
            <p><?php echo e($solicitud->razon_rechazo); ?></p>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<!-- Modal para cambiar estado (incluirlo al final) -->
<?php echo $__env->make('admin.solicitud.partials.show.status-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\solicitud\partials\show\details.blade.php ENDPATH**/ ?>