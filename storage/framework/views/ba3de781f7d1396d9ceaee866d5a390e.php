<!-- Estadísticas Rápidas -->
<div class="row mb-4">
    <div class="col-12">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="stat-card bg-success text-white">
                    <div class="stat-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="stat-info">
                        <h4><?php echo e($mascotas->where('estado', 'En adopcion')->count()); ?></h4>
                        <p>En Adopción</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-info text-white">
                    <div class="stat-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="stat-info">
                        <h4><?php echo e($mascotas->where('estado', 'Adoptado')->count()); ?></h4>
                        <p>Adoptados</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-warning text-dark">
                    <div class="stat-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h4><?php echo e($mascotas->where('estado', 'Rescatada')->count()); ?></h4>
                        <p>Rescatadas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-secondary text-white">
                    <div class="stat-icon">
                        <i class="fas fa-paw"></i>
                    </div>
                    <div class="stat-info">
                        <h4><?php echo e($mascotas->total()); ?></h4>
                        <p>Total en Sistema</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\mascotas\partials\index\stats.blade.php ENDPATH**/ ?>