<!-- Header Profesional con Hamburguesa Integrada -->
<header class="admin-header">
    <div class="header-container">
        <!-- Botón Hamburguesa -->
        <button class="hamburger-btn" id="hamburgerBtn">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Logo y Título (AHORA CON IMAGEN) -->
        <div class="header-brand">
            <!-- CAMBIO AQUÍ: Eliminamos el icono y ponemos la imagen -->
            <img src="<?php echo e(asset('img/logo-oscuro.png')); ?>" alt="Logo Fundación" class="header-logo">
            <img src="<?php echo e(asset('img/texto-logo-oscuro.png')); ?>" alt="Logo Fundación" class="header-logo-texto">

            <div class="brand-text">
            </div>
        </div>

        <!-- Botón de Perfil Simple -->

        <a href="<?php echo e(('admin.administradores.index')); ?>" class="profile-btn">

            <div class="profile-avatar">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="profile-text">
                <span class="profile-name"><?php echo e(auth()->user()->name ?? 'Admin'); ?></span>
                <span class="profile-role">Administrador</span>
            </div>
        </a>

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
</header>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\admin\layouts\header.blade.php ENDPATH**/ ?>