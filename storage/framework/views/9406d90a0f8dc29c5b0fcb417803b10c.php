
<nav class="public-navbar">
    <div class="public-navbar-container">
        
        <button class="public-hamburger-btn" id="publicHamburgerBtn">
            <span></span>
            <span></span>
            <span></span>
        </button>

        
        <a href="<?php echo e(url('/')); ?>" class="public-navbar-brand">
            <img src="<?php echo e(asset('img/logo-oscuro.png')); ?>" alt="Logo Fundación" class="header-logo">
            <img src="<?php echo e(asset('img/texto-logo-oscuro.png')); ?>" alt="Logo Fundación" class="header-logo-texto">
        </a>

        
        <a href="<?php echo e(url('/rescates/crear')); ?>" class="public-urgent-btn">
            <i class="fas fa-paw"></i>
            <span>Reportar Rescate</span>
        </a>

        
        <?php if(auth()->guard()->check()): ?>
        <div class="public-profile-btn dropdown" data-bs-toggle="dropdown">
            <div class="public-profile-avatar">
                <?php if(Auth::user()->avatar): ?>
                    <img src="<?php echo e(Auth::user()->avatar); ?>" alt="Avatar">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
            </div>
            <div class="public-profile-info">
                <span class="public-profile-name"><?php echo e(Auth::user()->name); ?></span>
                <span class="public-profile-role"><?php echo e(Auth::user()->role ?? 'Usuario'); ?></span>
            </div>
        </div>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?php echo e(url('/perfil')); ?>"><i class="fas fa-user me-2"></i>Mi Perfil</a></li>
            <li><a class="dropdown-item" href="<?php echo e(url('/mis-rescates')); ?>"><i class="fas fa-paw me-2"></i>Mis Rescates</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?php echo e(url('/logout')); ?>" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fas fa-sign-out-alt me-2"></i>Cerrar sesión</a></li>
        </ul>
        <?php else: ?>
        <a href="<?php echo e(url('/login')); ?>" class="public-profile-btn">
            <div class="public-profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="public-profile-info">
                <span class="public-profile-name">Iniciar sesión</span>
                <span class="public-profile-role">Registrarse</span>
            </div>
        </a>
        <?php endif; ?>

        
        <div class="language-selector">
            <a href="<?php echo e(route('locale.switch', 'es')); ?>"
               class="language-link <?php echo e(app()->getLocale() == 'es' ? 'active' : ''); ?>">
                ES
            </a>
            <span class="language-divider">|</span>
            <a href="<?php echo e(route('locale.switch', 'en')); ?>"
               class="language-link <?php echo e(app()->getLocale() == 'en' ? 'active' : ''); ?>">
                EN
            </a>
        </div>
    </div>
</nav>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend\resources\views/public/layouts/header.blade.php ENDPATH**/ ?>