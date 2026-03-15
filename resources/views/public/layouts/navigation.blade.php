{{-- resources/views/public/partials/sidebar/public-sidebar.blade.php --}}

<!-- Sidebar Público Simplificado -->
<aside class="public-sidebar" id="publicSidebar">

    {{-- Header del Sidebar --}}
    <div class="public-sidebar-header">
        <div class="public-sidebar-user">
            <div class="public-sidebar-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="public-sidebar-user-info">
                <h5>{{ auth()->check() ? auth()->user()->name : 'Invitado' }}</h5>
                <span class="public-sidebar-user-role">Bienvenido</span>
            </div>
        </div>
        <button class="public-sidebar-close" id="publicSidebarClose">
            <i class="fas fa-times"></i>
        </button>
    </div>

    {{-- Navegación Principal --}}
    <nav class="public-sidebar-nav">

        {{-- ACCIONES PRINCIPALES --}}
        <div class="public-sidebar-section">
            {{-- Reportar Emergencia (Destacado) --}}
            <a href="{{ url('/rescates/reportar') }}"
                class="public-sidebar-item public-rescate-item {{ request()->is('rescates/reportar') ? 'active' : '' }}">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Reportar Emergencia</span>
                <span class="public-sidebar-badge">URGENTE</span>
            </a>

            {{-- Rescates activos --}}
            <a href="{{ url('/rescates/activos') }}"
                class="public-sidebar-item {{ request()->is('rescates/activos') ? 'active' : '' }}">
                <i class="fas fa-map-marker-alt"></i>
                <span>Rescates activos</span>
            </a>
        </div>

        {{-- ADOPCIÓN --}}
        <div class="public-sidebar-section">
            <div class="public-section-title">
                <i class="fas fa-dog me-1"></i> ADOPCIÓN
            </div>

            {{-- Mascotas en adopción --}}
            <a href="{{ route('public.adopciones.index') }}"
                class="public-sidebar-item {{ request()->routeIs('public.adopciones.index') ? 'active' : '' }}">
                <i class="fas fa-paw"></i>
                <span>Mascotas en adopción</span>
            </a>

            @auth
                {{-- Mis solicitudes --}}
                <a href="{{ route('public.adopciones.mis-solicitudes') }}"
                    class="public-sidebar-item {{ request()->routeIs('public.adopciones.mis-solicitudes') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Mis solicitudes</span>
                </a>
            @endauth

            {{-- Apadrinar --}}
            <a href="{{ url('/apadrinamientos') }}"
                class="public-sidebar-item {{ request()->is('apadrinamientos*') ? 'active' : '' }}">
                <i class="fas fa-heart"></i>
                <span>Apadrinar</span>
            </a>
        </div>

        {{-- COLABORAR --}}
        <div class="public-sidebar-section">
            <div class="public-section-title">
                <i class="fas fa-hand-holding-heart me-1"></i> COLABORAR
            </div>

            {{-- Donar --}}
            <a href="{{ url('/donaciones') }}"
                class="public-sidebar-item {{ request()->is('donaciones*') ? 'active' : '' }}">
                <i class="fas fa-donate"></i>
                <span>Donar</span>
            </a>

            {{-- Eventos --}}
            <a href="{{ url('/eventos') }}"
                class="public-sidebar-item {{ request()->is('eventos*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i>
                <span>Eventos</span>
            </a>
        </div>

        {{-- COMUNIDAD --}}
        <div class="public-sidebar-section">
            <div class="public-section-title">
                <i class="fas fa-users me-1"></i> COMUNIDAD
            </div>

            {{-- Fundaciones --}}
            <a href="{{ url('/fundaciones') }}"
                class="public-sidebar-item {{ request()->is('fundaciones*') ? 'active' : '' }}">
                <i class="fas fa-building"></i>
                <span>Fundaciones</span>
            </a>

            {{-- Veterinarias --}}
            <a href="{{ url('/veterinarias') }}"
                class="public-sidebar-item {{ request()->is('veterinarias*') ? 'active' : '' }}">
                <i class="fas fa-clinic-medical"></i>
                <span>Veterinarias</span>
            </a>
        </div>
    </nav>

    {{-- Footer del Sidebar --}}
    <div class="public-sidebar-footer">
        {{-- Mi Perfil (solo si está logueado) --}}
        @auth
            <a href="{{ url('/perfil') }}" class="public-sidebar-item">
                <i class="fas fa-user-circle"></i>
                <span>Mi Perfil</span>
            </a>
        @endauth

        {{-- Notificaciones con badge --}}
        <a href="{{ url('/notificaciones') }}"
            class="public-sidebar-item {{ request()->is('notificaciones*') ? 'active' : '' }}">
            <i class="fas fa-bell"></i>
            <span>Notificaciones</span>
            @php
                $notificacionesNoLeidas = 3; // Esto vendrá de tu controlador
            @endphp
            @if ($notificacionesNoLeidas > 0)
                <span class="public-sidebar-badge">{{ $notificacionesNoLeidas }}</span>
            @endif
        </a>

        {{-- Ayuda --}}
        <a href="{{ url('/faq') }}" class="public-sidebar-item">
            <i class="fas fa-question-circle"></i>
            <span>Ayuda</span>
        </a>

        {{-- Cerrar sesión (si está logueado) --}}
        @auth
            <a href="{{ url('/logout') }}" class="public-sidebar-item text-danger"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar sesión</span>
            </a>
            <form id="logout-form" action="{{ url('/logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        @endauth
    </div>
</aside>

{{-- Overlay para cerrar sidebar al hacer clic fuera --}}
<div class="public-sidebar-overlay" id="publicSidebarOverlay"></div>
