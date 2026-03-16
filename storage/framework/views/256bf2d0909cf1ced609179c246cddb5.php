<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="filtros-modernos">
            <form action="<?php echo e(route('public.mascotas.index')); ?>" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label text-turquesa fw-bold">Especie</label>
                    <select name="especie" class="form-select">
                        <option value="">Todas las especies</option>
                        <?php $__currentLoopData = $especies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $especie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($especie); ?>" <?php echo e(request('especie') == $especie ? 'selected' : ''); ?>>
                                <?php echo e($especie); ?>s
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-turquesa fw-bold">Género</label>
                    <select name="genero" class="form-select">
                        <option value="">Todos los géneros</option>
                        <option value="Macho" <?php echo e(request('genero') == 'Macho' ? 'selected' : ''); ?>>Machos</option>
                        <option value="Hembra" <?php echo e(request('genero') == 'Hembra' ? 'selected' : ''); ?>>Hembras</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn-buscar-moderno">
                        <i class="fas fa-search me-2"></i> Buscar Mascotas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-foreve\resources\views/public/mascotas/partials/index/filters.blade.php ENDPATH**/ ?>