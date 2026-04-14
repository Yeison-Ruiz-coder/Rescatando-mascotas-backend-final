<?php $__env->startSection('title', '¡Solicitud Enviada con Éxito!'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/pages/adopciones/exito.css')); ?>?v=1.0">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <!-- CARD PRINCIPAL DE ÉXITO -->
            <div class="card card-exito animacion-entrada">
                <div class="card-body p-4 p-md-5 text-center">
                    
                    <!-- ICONO DE ÉXITO ANIMADO -->
                    <div class="exito-icono mb-4">
                        <div class="circulo-exito">
                            <i class="fas fa-check-circle fa-5x"></i>
                        </div>
                    </div>

                    <!-- TÍTULO -->
                    <h1 class="titulo-exito mb-3">
                        ¡Solicitud Enviada con Éxito!
                    </h1>

                    <!-- MENSAJE PRINCIPAL -->
                    <p class="mensaje-exito lead mb-4">
                        Hemos recibido tu solicitud para adoptar a 
                        <strong class="text-turquesa"><?php echo e($solicitud->solicitable->nombre_mascota ?? 'la mascota'); ?></strong>
                    </p>

                    <!-- TARJETA DE DETALLES DE LA SOLICITUD -->
                    <div class="detalle-solicitud-card mb-4 p-4">
                        <h5 class="detalle-titulo mb-3">
                            <i class="fas fa-clipboard-list me-2"></i>
                            Detalles de tu solicitud
                        </h5>
                        
                        <div class="detalle-grid">
                            <div class="detalle-item">
                                <span class="detalle-label">Número de solicitud:</span>
                                <span class="detalle-valor">#<?php echo e($solicitud->id); ?></span>
                            </div>
                            
                            <div class="detalle-item">
                                <span class="detalle-label">Fecha de envío:</span>
                                <span class="detalle-valor"><?php echo e($solicitud->created_at->format('d/m/Y H:i')); ?></span>
                            </div>
                            
                            <div class="detalle-item">
                                <span class="detalle-label">Mascota:</span>
                                <span class="detalle-valor"><?php echo e($solicitud->solicitable->nombre_mascota ?? 'N/A'); ?></span>
                            </div>
                            
                            <div class="detalle-item">
                                <span class="detalle-label">Estado actual:</span>
                                <span class="badge-estado badge-pendiente">
                                    <i class="fas fa-clock me-1"></i>
                                    Pendiente de revisión
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- INFORMACIÓN DE CONTACTO -->
                    <div class="info-contacto mb-4">
                        <div class="info-contacto-icono mb-2">
                            <i class="fas fa-envelope"></i>
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <h5 class="mb-2">Te contactaremos pronto</h5>
                        <p class="mb-1">
                            <strong>Email:</strong> <?php echo e($solicitud->email_solicitante); ?>

                        </p>
                        <p>
                            <strong>Teléfono:</strong> <?php echo e($solicitud->telefono_solicitante); ?>

                        </p>
                        <p class="text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            Revisaremos tu solicitud en los próximos días y te contactaremos.
                        </p>
                    </div>

                    <!-- PASOS A SEGUIR -->
                    <div class="proceso-pasos mb-4">
                        <h5 class="mb-3">
                            <i class="fas fa-tasks me-2"></i>
                            Próximos pasos
                        </h5>
                        
                        <div class="pasos-grid">
                            <div class="paso-item">
                                <div class="paso-numero">1</div>
                                <div class="paso-contenido">
                                    <strong>Revisión</strong>
                                    <small>La fundación revisará tu solicitud</small>
                                </div>
                            </div>
                            
                            <div class="paso-item">
                                <div class="paso-numero">2</div>
                                <div class="paso-contenido">
                                    <strong>Entrevista</strong>
                                    <small>Coordinaremos una entrevista</small>
                                </div>
                            </div>
                            
                            <div class="paso-item">
                                <div class="paso-numero">3</div>
                                <div class="paso-contenido">
                                    <strong>Vista</strong>
                                    <small>Conocerás a tu nueva mascota</small>
                                </div>
                            </div>
                            
                            <div class="paso-item">
                                <div class="paso-numero">4</div>
                                <div class="paso-contenido">
                                    <strong>¡Hogar!</strong>
                                    <small>Adopción completada</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TIEMPO ESTIMADO -->
                    <div class="alert alert-info tiempo-estimado mb-4">
                        <i class="fas fa-clock me-2"></i>
                        <strong>Tiempo estimado del proceso:</strong> 5-7 días hábiles
                    </div>

                    <!-- BOTONES DE ACCIÓN -->
                    <div class="acciones-exito mt-4">
                        <a href="<?php echo e(route('public.mascotas.index')); ?>" class="btn btn-ver-mascotas me-2 mb-2">
                            <i class="fas fa-paw me-2"></i>
                            Ver más mascotas
                        </a>
                        
                        <?php if(auth()->guard()->check()): ?>
                        <a href="<?php echo e(route('public.adopciones.mis-solicitudes')); ?>" class="btn btn-mis-solicitudes mb-2">
                            <i class="fas fa-clipboard-list me-2"></i>
                            Mis solicitudes
                        </a>
                        <?php endif; ?>
                        
                        
                    </div>

                    <!-- COMPARTIR -->
                    <div class="compartir-redes mt-4 pt-3">
                        <p class="small text-muted mb-2">Comparte esta alegría</p>
                        <div class="redes-sociales">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo e(urlencode(route('public.mascotas.show', $solicitud->solicitable_id))); ?>" 
                               target="_blank" 
                               class="btn-redes facebook"
                               title="Compartir en Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?text=¡Apoyemos%20la%20adopción%20de%20<?php echo e(urlencode($solicitud->solicitable->nombre_mascota ?? 'mascotas')); ?>%20en%20<?php echo e(urlencode(config('app.name'))); ?>&url=<?php echo e(urlencode(route('public.mascotas.show', $solicitud->solicitable_id))); ?>" 
                               target="_blank" 
                               class="btn-redes twitter"
                               title="Compartir en Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://wa.me/?text=¡Apoyemos%20la%20adopción%20de%20<?php echo e(urlencode($solicitud->solicitable->nombre_mascota ?? 'mascotas')); ?>%20en%20<?php echo e(urlencode(route('public.mascotas.show', $solicitud->solicitable_id))); ?>" 
                               target="_blank" 
                               class="btn-redes whatsapp"
                               title="Compartir en WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animación adicional para el icono
    const icono = document.querySelector('.circulo-exito i');
    if (icono) {
        setTimeout(() => {
            icono.classList.add('animacion-latido');
        }, 500);
    }
    
    // Confeti opcional (si quieres agregar efecto de celebración)
    if (typeof confetti !== 'undefined') {
        setTimeout(() => {
            confetti({
                particleCount: 100,
                spread: 70,
                origin: { y: 0.6 },
                colors: ['#764ba2', '#667eea', '#f55f93', '#FEA4C4']
            });
        }, 300);
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('public.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\adopciones\solicitud-exitosa.blade.php ENDPATH**/ ?>