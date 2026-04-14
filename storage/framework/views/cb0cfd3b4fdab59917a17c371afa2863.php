<?php $__env->startSection('content'); ?>
<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h1>Ver Apadrinamiento</h1>

            <div class="card">
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Usuario:</dt>
                        <dd class="col-sm-9"><?php echo e($apadrinamiento->usuario->name ?? 'N/A'); ?></dd>

                        <dt class="col-sm-3">Mascota:</dt>
                        <dd class="col-sm-9"><?php echo e($apadrinamiento->mascota->nombre ?? 'N/A'); ?></dd>

                        <dt class="col-sm-3">Monto Mensual:</dt>
                        <dd class="col-sm-9">$<?php echo e(number_format($apadrinamiento->monto_mensual, 2)); ?></dd>

                        <dt class="col-sm-3">Frecuencia:</dt>
                        <dd class="col-sm-9"><?php echo e(ucfirst($apadrinamiento->frecuencia)); ?></dd>

                        <dt class="col-sm-3">Fecha Inicio:</dt>
                        <dd class="col-sm-9"><?php echo e($apadrinamiento->fecha_inicio->format('d/m/Y')); ?></dd>

                        <dt class="col-sm-3">Fecha Fin:</dt>
                        <dd class="col-sm-9"><?php echo e($apadrinamiento->fecha_fin ? $apadrinamiento->fecha_fin->format('d/m/Y') : 'Sin fecha fin'); ?></dd>

                        <dt class="col-sm-3">Estado:</dt>
                        <dd class="col-sm-9">
                            <span class="badge bg-<?php echo e($apadrinamiento->estado == 'activo' ? 'success' : 'warning'); ?>">
                                <?php echo e(ucfirst($apadrinamiento->estado)); ?>

                            </span>
                        </dd>

                        <dt class="col-sm-3">Mensaje Apoyo:</dt>
                        <dd class="col-sm-9"><?php echo e($apadrinamiento->mensaje_apoyo ?? 'Sin mensaje'); ?></dd>
                    </dl>
                </div>
            </div>

            <div class="mt-3">
                <a href="<?php echo e(route('apadrinamientos.edit', $apadrinamiento)); ?>" class="btn btn-warning">Editar</a>
                <a href="<?php echo e(route('apadrinamientos.index')); ?>" class="btn btn-secondary">Volver</a>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\apadrinamientos\show.blade.php ENDPATH**/ ?>