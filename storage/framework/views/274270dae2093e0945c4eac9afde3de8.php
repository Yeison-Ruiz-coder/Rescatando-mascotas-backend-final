<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo e(url('/')); ?>">
            <i class="fas fa-paw text-primary me-2"></i>
            Rescatando Mascotas
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(url('/')); ?>#mascotas">Mascotas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(url('/')); ?>#proceso">Proceso</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('public.mascotas.index')); ?>">Adoptar</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('public.rescates.create')); ?>">Rescatar</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Más
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo e(route('public.donaciones.create')); ?>">Donaciones</a></li>
                        <li><a class="dropdown-item" href="#">Voluntariado</a></li>
                        <li><a class="dropdown-item" href="#">Servicios</a></li>
                    </ul>
                </li>
            </ul>

            <div class="d-flex">
                <?php if(auth()->guard()->check()): ?>
                    <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-outline-primary me-2">
                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                    </a>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="fas fa-sign-out-alt me-1"></i>Salir
                        </button>
                    </form>
                <?php else: ?>
                    <a href="<?php echo e(route('login')); ?>" class="btn btn-outline-primary me-2">
                        <i class="fas fa-sign-in-alt me-1"></i>Ingresar
                    </a>
                    <a href="<?php echo e(route('register')); ?>" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i>Registrarse
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\home\partials\navbar.blade.php ENDPATH**/ ?>