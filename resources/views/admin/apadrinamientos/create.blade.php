@extends('admin.layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h1>{{ isset($apadrinamiento) ? 'Editar' : 'Crear' }} Apadrinamiento</h1>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ isset($apadrinamiento) ? route('admin.apadrinamientos.update', $apadrinamiento) : route('admin.apadrinamientos.store') }}" method="POST">
                @csrf
                @if(isset($apadrinamiento))
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label for="usuario_id" class="form-label">Usuario</label>
                    <select class="form-control @error('usuario_id') is-invalid @enderror" id="usuario_id" name="usuario_id" required>
                        <option value="">Seleccionar usuario</option>
                        @foreach($usuarios ?? [] as $usuario)
                            <option value="{{ optional($usuario)->id }}" {{ isset($apadrinamiento) && $apadrinamiento->usuario_id == optional($usuario)->id ? 'selected' : '' }}>
                                {{ optional($usuario)->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('usuario_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label for="mascota_id" class="form-label">Mascota</label>
                    <select class="form-control @error('mascota_id') is-invalid @enderror" id="mascota_id" name="mascota_id" required>
                        <option value="">Seleccionar mascota</option>
                        @foreach($mascotas ?? [] as $mascota)
                            <option value="{{ optional($mascota)->id }}" {{ isset($apadrinamiento) && $apadrinamiento->mascota_id == optional($mascota)->id ? 'selected' : '' }}>
                                {{ optional($mascota)->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('mascota_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label for="monto_mensual" class="form-label">Monto Mensual</label>
                    <input type="number" step="0.01" class="form-control @error('monto_mensual') is-invalid @enderror" id="monto_mensual" name="monto_mensual"
                        value="{{ isset($apadrinamiento) ? $apadrinamiento->monto_mensual : old('monto_mensual') }}" required>
                    @error('monto_mensual') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label for="frecuencia" class="form-label">Frecuencia</label>
                    <select class="form-control @error('frecuencia') is-invalid @enderror" id="frecuencia" name="frecuencia" required>
                        <option value="">Seleccionar frecuencia</option>
                        <option value="unica" {{ isset($apadrinamiento) && $apadrinamiento->frecuencia == 'unica' ? 'selected' : '' }}>Única</option>
                        <option value="mensual" {{ isset($apadrinamiento) && $apadrinamiento->frecuencia == 'mensual' ? 'selected' : '' }}>Mensual</option>
                        <option value="trimestral" {{ isset($apadrinamiento) && $apadrinamiento->frecuencia == 'trimestral' ? 'selected' : '' }}>Trimestral</option>
                        <option value="anual" {{ isset($apadrinamiento) && $apadrinamiento->frecuencia == 'anual' ? 'selected' : '' }}>Anual</option>
                    </select>
                    @error('frecuencia') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" id="fecha_inicio" name="fecha_inicio"
                        value="{{ isset($apadrinamiento) ? optional($apadrinamiento->fecha_inicio)->format('Y-m-d') : old('fecha_inicio') }}" required>
                    @error('fecha_inicio') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label for="fecha_fin" class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control @error('fecha_fin') is-invalid @enderror" id="fecha_fin" name="fecha_fin"
                        value="{{ isset($apadrinamiento) && $apadrinamiento->fecha_fin ? optional($apadrinamiento->fecha_fin)->format('Y-m-d') : old('fecha_fin') }}">
                    @error('fecha_fin') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-control @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                        <option value="">Seleccionar estado</option>
                        <option value="activo" {{ isset($apadrinamiento) && $apadrinamiento->estado == 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="pausado" {{ isset($apadrinamiento) && $apadrinamiento->estado == 'pausado' ? 'selected' : '' }}>Pausado</option>
                        <option value="cancelado" {{ isset($apadrinamiento) && $apadrinamiento->estado == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                        <option value="finalizado" {{ isset($apadrinamiento) && $apadrinamiento->estado == 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                    </select>
                    @error('estado') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label for="mensaje_apoyo" class="form-label">Mensaje de Apoyo</label>
                    <textarea class="form-control @error('mensaje_apoyo') is-invalid @enderror" id="mensaje_apoyo" name="mensaje_apoyo" rows="3">{{ isset($apadrinamiento) ? $apadrinamiento->mensaje_apoyo : old('mensaje_apoyo') }}</textarea>
                    @error('mensaje_apoyo') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div>
                    <button type="submit" class="btn btn-primary">{{ isset($apadrinamiento) ? 'Actualizar' : 'Crear' }}</button>
                    <a href="{{ route('admin.donaciones.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
