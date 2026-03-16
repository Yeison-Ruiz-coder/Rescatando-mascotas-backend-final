
<div class="card shadow mb-4">
    <div class="card-header fw-bold bg-turquesa text-white">
        <i class="fas fa-info-circle me-2"></i>Ficha Técnica
    </div>
    <ul class="list-group list-group-flush">
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <strong>Especie:</strong>
            <span class="badge bg-info text-white p-2"><?php echo e($mascota->especie ?? 'No especificada'); ?></span>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <strong>Género:</strong>
            <span class="badge bg-secondary p-2"><?php echo e($mascota->genero ?? 'No especificado'); ?></span>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <strong>Edad:</strong>
            <span><?php echo e($mascota->edad_aprox ?? 'No especificada'); ?> años</span>
        </li>
        <li class="list-group-item">
            <strong>Lugar de Rescate:</strong>
            <span class="float-end"><?php echo e($mascota->lugar_rescate ?? 'No especificado'); ?></span>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <strong>Fecha de Ingreso:</strong>
            <span><?php echo e($mascota->fecha_ingreso ? \Carbon\Carbon::parse($mascota->fecha_ingreso)->format('d/m/Y') : 'No registrada'); ?></span>
        </li>
        <?php if($mascota->fecha_salida): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <strong>Fecha de Salida:</strong>
            <span><?php echo e(\Carbon\Carbon::parse($mascota->fecha_salida)->format('d/m/Y')); ?></span>
        </li>
        <?php endif; ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <strong>Responsable (Fundación):</strong>
            <span><?php echo e($mascota->fundacion ? $mascota->fundacion->Nombre_1 : 'No asignada'); ?></span>
        </li>
    </ul>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-foreve\resources\views/admin/mascotas/partials/show/technical-details.blade.php ENDPATH**/ ?>