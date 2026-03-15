<div class="row">
    <div class="col-sm-6 mb-4 mb-sm-0">
        <h5 class="footer-title">Formularios</h5>
        <ul class="list-unstyled footer-list">
            <li><a href="{{ route('admin.adopciones.create') }}">Adopción</a></li>
            <li><a href="{{ route('admin.rescates.create') }}">Rescates</a></li>
            <li><a href="{{ route('admin.usuarios.create') }}">Rescatistas</a></li>
        </ul>
    </div>
    <div class="col-sm-6">
        <h5 class="footer-title">Servicios</h5>
        <ul class="list-unstyled footer-list">
            <li><a href="{{ route('admin.adopciones.index') }}">Proceso de Adopción</a></li>
            <li><a href="#">Donación de suministros</a></li>
            <li><a href="{{ route('admin.donaciones.index') }}">Donaciones</a></li>
            <li><a href="{{ route('admin.donaciones.index') }}">Apadrinamientos</a></li>
            <li><a href="#">Rescatistas</a></li>
            <li><a href="{{ route('admin.eventos.index') }}">Eventos</a></li>
            <li><a href="#">Calificación</a></li>
        </ul>
    </div>
</div>
