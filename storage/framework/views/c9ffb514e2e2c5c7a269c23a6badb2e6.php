<span class="badge
    <?php if($adopcion->estado == 'completada'): ?> bg-success
    <?php elseif($adopcion->estado == 'en_proceso'): ?> bg-warning text-dark
    <?php elseif($adopcion->estado == 'aprobada'): ?> bg-info
    <?php elseif($adopcion->estado == 'rechazada'): ?> bg-danger
    <?php else: ?> bg-secondary
    <?php endif; ?>" style="font-size: 0.9rem; padding: 0.5rem 1rem;">

    <?php if($adopcion->estado == 'en_proceso'): ?> En Proceso
    <?php elseif($adopcion->estado == 'aprobada'): ?> Aprobada
    <?php elseif($adopcion->estado == 'completada'): ?> Completada
    <?php elseif($adopcion->estado == 'rechazada'): ?> Rechazada
    <?php elseif($adopcion->estado == 'cancelada'): ?> Cancelada
    <?php else: ?> <?php echo e(ucfirst($adopcion->estado)); ?>

    <?php endif; ?>
</span>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\adopciones\partials\show\estado_badge.blade.php ENDPATH**/ ?>