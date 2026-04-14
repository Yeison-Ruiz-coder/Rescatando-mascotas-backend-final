

<!-- Sidebar Público Simplificado -->
<aside class="public-sidebar" id="publicSidebar">

    
    <div class="public-sidebar-header">
        <div class="public-sidebar-user">
            <div class="public-sidebar-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="public-sidebar-user-info">
                <h5><?php echo e(auth()->check() ? auth()->user()->name : 'Invitado'); ?></h5>
                <span class="public-sidebar-user-role">Bienvenido</span>
            </div>
        </div>
        <button class="public-sidebar-close" id="publicSidebarClose">
            <i class="fas fa-times"></i>
        </button>
    </div>

    
    <nav class="public-sidebar-nav">

        
        <div class="public-sidebar-section">
            
            <a href="<?php echo e(url('/rescates/reportar')); ?>"
                class="public-sidebar-item public-rescate-item <?php echo e(request()->is('rescates/reportar') ? 'active' : ''); ?>">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Reportar Emergencia</span>
                <span class="public-sidebar-badge">URGENTE</span>
            </a>

            
            <a href="<?php echo e(url('/rescates/activos')); ?>"
                class="public-sidebar-item <?php echo e(request()->is('rescates/activos') ? 'active' : ''); ?>">
                <i class="fas fa-map-marker-alt"></i>
                <span>Rescates activos</span>
            </a>
        </div>

        
        <div class="public-sidebar-section">
            <div class="public-section-title">
                <i class="fas fa-dog me-1"></i> ADOPCIÓN
            </div>

            
            <a href="<?php echo e(route('public.adopciones.index')); ?>"
                class="public-sidebar-item <?php echo e(request()->routeIs('public.adopciones.index') ? 'active' : ''); ?>">
                <i class="fas fa-paw"></i>
                <span>Mascotas en adopción</span>
            </a>

            <?php if(auth()->guard()->check()): ?>
                
                <a href="<?php echo e(route('public.adopciones.mis-solicitudes')); ?>"
                    class="public-sidebar-item <?php echo e(request()->routeIs('public.adopciones.mis-solicitudes') ? 'active' : ''); ?>">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Mis solicitudes</span>
                </a>
            <?php endif; ?>

            
            <a href="<?php echo e(url('/apadrinamientos')); ?>"
                class="public-sidebar-item <?php echo e(request()->is('apadrinamientos*') ? 'active' : ''); ?>">
                <i class="fas fa-heart"></i>
                <span>Apadrinar</span>
            </a>
        </div>

        
        <div class="public-sidebar-section">
            <div class="public-section-title">
                <i class="fas fa-hand-holding-heart me-1"></i> COLABORAR
            </div>

            
            <a href="<?php echo e(url('/donaciones')); ?>"
                class="public-sidebar-item <?php echo e(request()->is('donaciones*') ? 'active' : ''); ?>">
                <i class="fas fa-donate"></i>
                <span>Donar</span>
            </a>

            
            <a href="<?php echo e(url('/eventos')); ?>"
                class="public-sidebar-item <?php echo e(request()->is('eventos*') ? 'active' : ''); ?>">
                <i class="fas fa-calendar-alt"></i>
                <span>Eventos</span>
            </a>
        </div>

        
        <div class="public-sidebar-section">
            <div class="public-section-title">
                <i class="fas fa-users me-1"></i> COMUNIDAD
            </div>

            
            <a href="<?php echo e(url('/fundaciones')); ?>"
                class="public-sidebar-item <?php echo e(request()->is('fundaciones*') ? 'active' : ''); ?>">
                <i class="fas fa-building"></i>
                <span>Fundaciones</span>
            </a>

            
            <a href="<?php echo e(url('/veterinarias')); ?>"
                class="public-sidebar-item <?php echo e(request()->is('veterinarias*') ? 'active' : ''); ?>">
                <i class="fas fa-clinic-medical"></i>
                <span>Veterinarias</span>
            </a>
        </div>
    </nav>

    
    <div class="public-sidebar-footer">
        
        <?php if(auth()->guard()->check()): ?>
            <a href="<?php echo e(url('/perfil')); ?>" class="public-sidebar-item">
                <i class="fas fa-user-circle"></i>
                <span>Mi Perfil</span>
            </a>
        <?php endif; ?>

        
        <a href="<?php echo e(url('/notificaciones')); ?>"
            class="public-sidebar-item <?php echo e(request()->is('notificaciones*') ? 'active' : ''); ?>">
            <i class="fas fa-bell"></i>
            <span>Notificaciones</span>
            <?php
                $notificacionesNoLeidas = 3; // Esto vendrá de tu controlador
            ?>
            <?php if($notificacionesNoLeidas > 0): ?>
                <span class="public-sidebar-badge"><?php echo e($notificacionesNoLeidas); ?></span>
            <?php endif; ?>
        </a>

        
        <a href="<?php echo e(url('/faq')); ?>" class="public-sidebar-item">
            <i class="fas fa-question-circle"></i>
            <span>Ayuda</span>
        </a>

        
        <?php if(auth()->guard()->check()): ?>
            <a href="<?php echo e(url('/logout')); ?>" class="public-sidebar-item text-danger"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar sesión</span>
            </a>
            <form id="logout-form" action="<?php echo e(url('/logout')); ?>" method="POST" class="d-none">
                <?php echo csrf_field(); ?>
            </form>
        <?php endif; ?>
    </div>
</aside>


<div class="public-sidebar-overlay" id="publicSidebarOverlay"></div>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\layouts\navigation.blade.php ENDPATH**/ ?>