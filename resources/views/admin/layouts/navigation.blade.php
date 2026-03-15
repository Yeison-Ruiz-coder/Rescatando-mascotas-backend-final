<!-- Menú Lateral Administrador - Versión Simplificada -->
<nav class="side-menu" id="sideMenu">
    <div class="menu-header">
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="user-details">
                <h5>{{ auth()->user()->name ?? 'Administrador' }}</h5>
                <span class="user-role">Administrador</span>
            </div>
        </div>
        <button class="close-menu" id="closeMenu">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="menu-search">
        <i class="fas fa-search"></i>
        <input type="text" id="menuSearch" placeholder="Buscar...">
    </div>

    <div class="menu-sections">
        <!-- Dashboard -->
        <div class="menu-section">
            <a href="{{ route('admin.dashboard.index') }}"
                class="menu-item {{ request()->routeIs('admin.dashboard.index') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <!-- MÓDULO PRINCIPAL: Mascotas -->
        <div class="menu-section">
            <div class="menu-item has-submenu {{ request()->routeIs('admin.mascotas.*') || request()->routeIs('admin.rescates.*') || request()->routeIs('admin.razas.*') ? 'active' : '' }}">
                <i class="fas fa-paw"></i>
                <span>Gestión de Mascotas</span>
                <i class="fas fa-chevron-right arrow"></i>
            </div>
            <div class="submenu">
                <a href="{{ route('admin.mascotas.index') }}" class="submenu-item {{ request()->routeIs('admin.mascotas.index') ? 'active' : '' }}">
                    <i class="fas fa-list"></i> Todas las Mascotas
                </a>
                <a href="{{ route('admin.mascotas.create') }}" class="submenu-item {{ request()->routeIs('admin.mascotas.create') ? 'active' : '' }}">
                    <i class="fas fa-plus-circle"></i> Registrar Nueva
                </a>
                <a href="{{ route('admin.rescates.index') }}" class="submenu-item {{ request()->routeIs('admin.rescates.index') ? 'active' : '' }}">
                    <i class="fas fa-ambulance"></i> Rescates Activos
                </a>
                <a href="{{ route('admin.razas.index') }}" class="submenu-item {{ request()->routeIs('admin.razas.index') ? 'active' : '' }}">
                    <i class="fas fa-dna"></i> Catálogo de Razas
                </a>
            </div>
        </div>

        <!-- Adopciones y Apadrinamientos (Unificados) -->
        <div class="menu-section">
            <div class="menu-item has-submenu {{ request()->routeIs('admin.adopciones.*') || request()->routeIs('admin.apadrinamientos.*') ? 'active' : '' }}">
                <i class="fas fa-heart"></i>
                <span>Adopciones y Apadrinamientos</span>
                <i class="fas fa-chevron-right arrow"></i>
                @php
                    $pendientesAdopcion = 5;
                    $pendientesApadrinamiento = 3;
                    $totalPendientes = $pendientesAdopcion + $pendientesApadrinamiento;
                @endphp
                @if ($totalPendientes > 0)
                    <span class="menu-badge">{{ $totalPendientes }}</span>
                @endif
            </div>
            <div class="submenu">
                <a href="{{ route('admin.adopciones.index') }}" class="submenu-item {{ request()->routeIs('admin.adopciones.index') ? 'active' : '' }}">
                    <i class="fas fa-home"></i> Adopciones
                    @if ($pendientesAdopcion > 0)
                        <span class="badge bg-danger">{{ $pendientesAdopcion }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.solicitudes.index') }}" class="submenu-item {{ request()->routeIs('admin.solicitudes.index') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i> Solicitudes Pendientes
                    @if ($pendientesAdopcion > 0)
                        <span class="badge bg-danger">{{ $pendientesAdopcion }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.donaciones.index') }}" class="submenu-item {{ request()->routeIs('admin.donaciones.index') ? 'active' : '' }}">
                    <i class="fas fa-star"></i> Apadrinamientos
                    @if ($pendientesApadrinamiento > 0)
                        <span class="badge bg-info">{{ $pendientesApadrinamiento }}</span>
                    @endif
                </a>
            </div>
        </div>

        <!-- Donaciones y Eventos (Unificados) -->
        <div class="menu-section">
            <div class="menu-item has-submenu {{ request()->routeIs('admin.donaciones.*') || request()->routeIs('admin.eventos.*') ? 'active' : '' }}">
                <i class="fas fa-hand-holding-heart"></i>
                <span>Donaciones y Eventos</span>
                <i class="fas fa-chevron-right arrow"></i>
            </div>
            <div class="submenu">
                <a href="{{ route('admin.donaciones.index') }}" class="submenu-item {{ request()->routeIs('admin.donaciones.index') ? 'active' : '' }}">
                    <i class="fas fa-donate"></i> Donaciones
                </a>
                <a href="{{ route('admin.eventos.index') }}" class="submenu-item {{ request()->routeIs('admin.eventos.index') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i> Eventos
                </a>
                <a href="{{ route('admin.donaciones.reporte') }}" class="submenu-item {{ request()->routeIs('admin.donaciones.reporte') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i> Reportes Financieros
                </a>
            </div>
        </div>

        <!-- Fundaciones y Veterinarias (Unificados) -->
        <div class="menu-section">
            <div class="menu-item has-submenu {{ request()->routeIs('admin.fundaciones.*') || request()->routeIs('admin.veterinarias.*') ? 'active' : '' }}">
                <i class="fas fa-building"></i>
                <span>Fundaciones y Veterinarias</span>
                <i class="fas fa-chevron-right arrow"></i>
            </div>
            <div class="submenu">
                <a href="{{ route('admin.fundaciones.index') }}" class="submenu-item {{ request()->routeIs('admin.fundaciones.index') ? 'active' : '' }}">
                    <i class="fas fa-building"></i> Fundaciones
                </a>
                <a href="{{ route('admin.veterinarias.index') }}" class="submenu-item {{ request()->routeIs('admin.veterinarias.index') ? 'active' : '' }}">
                    <i class="fas fa-clinic-medical"></i> Veterinarias
                </a>
            </div>
        </div>

        <!-- Tienda (Consolidado) -->
        <div class="menu-section">
            <div class="menu-item has-submenu {{ request()->routeIs('admin.tiendas.*') ? 'active' : '' }}">
                <i class="fas fa-store"></i>
                <span>Tienda</span>
                <i class="fas fa-chevron-right arrow"></i>
            </div>
            <div class="submenu">
                <a href="{{ route('admin.tiendas.index') }}" class="submenu-item {{ request()->routeIs('admin.tiendas.index') ? 'active' : '' }}">
                    <i class="fas fa-box"></i> Productos
                </a>
                @if(isset($tiendas) && $tiendas->count() > 0)
                    <a href="{{ route('admin.tiendas.ventas', ['tienda' => $tiendas->first()->id]) }}" class="submenu-item">
                        <i class="fas fa-shopping-cart"></i> Ventas
                    </a>
                    <a href="{{ route('admin.tiendas.inventario', ['tienda' => $tiendas->first()->id]) }}" class="submenu-item">
                        <i class="fas fa-warehouse"></i> Inventario
                    </a>
                @endif
            </div>
        </div>

        <!-- Reportes y Estadísticas -->
        <div class="menu-section">
            <div class="menu-item has-submenu {{ request()->routeIs('admin.reportes.*') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i>
                <span>Reportes y Estadísticas</span>
                <i class="fas fa-chevron-right arrow"></i>
            </div>
            <div class="submenu">
                <a href="{{ route('admin.reportes.index') }}" class="submenu-item {{ request()->routeIs('admin.reportes.index') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie"></i> Reportes Generales
                </a>
                <a href="{{ route('admin.reportes.exportar', ['tipo' => 'general']) }}" class="submenu-item">
                    <i class="fas fa-file-export"></i> Exportar Datos
                </a>
            </div>
        </div>

        <!-- Comunicación -->
        <div class="menu-section">
            <div class="menu-item has-submenu {{ request()->routeIs('admin.notificaciones.*') || request()->routeIs('admin.comentarios.*') ? 'active' : '' }}">
                <i class="fas fa-bell"></i>
                <span>Comunicación</span>
                <i class="fas fa-chevron-right arrow"></i>
                @php
                    $notificacionesNoLeidas = 3;
                    $comentariosPendientes = 2;
                    $totalComunicacion = $notificacionesNoLeidas + $comentariosPendientes;
                @endphp
                @if ($totalComunicacion > 0)
                    <span class="menu-badge">{{ $totalComunicacion }}</span>
                @endif
            </div>
            <div class="submenu">
                <a href="{{ route('admin.notificaciones.index') }}" class="submenu-item {{ request()->routeIs('admin.notificaciones.index') ? 'active' : '' }}">
                    <i class="fas fa-bell"></i> Notificaciones
                    @if ($notificacionesNoLeidas > 0)
                        <span class="badge bg-danger">{{ $notificacionesNoLeidas }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.comentarios.index') }}" class="submenu-item {{ request()->routeIs('admin.comentarios.index') ? 'active' : '' }}">
                    <i class="fas fa-comments"></i> Comentarios
                    @if ($comentariosPendientes > 0)
                        <span class="badge bg-warning">{{ $comentariosPendientes }}</span>
                    @endif
                </a>
            </div>
        </div>
    </div>

    <!-- Footer del menú -->
    <div class="menu-footer">
        <a href="{{ route('admin.configuracion') }}" class="menu-item {{ request()->routeIs('admin.configuracion') ? 'active' : '' }}">
            <i class="fas fa-cog"></i>
            <span>Configuración</span>
        </a>
        <a href="{{ route('logout') }}" class="menu-item text-danger"
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fas fa-sign-out-alt"></i>
            <span>Cerrar Sesión</span>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</nav>
