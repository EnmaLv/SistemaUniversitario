@extends('layouts.app')

@section('content_header')
    <div class="rd-card p-4 mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 rd-title-sm" style="font-size:1.4rem;">{{ $beca->nombre }}</h1>
            <p class="mt-1 mb-0" style="font-size:0.95rem; color:#475569;">
                Codigo <strong>{{ $beca->codigo }}</strong>
            </p>
        </div>
        <div class="d-flex" style="gap:12px;">
            <a href="{{ route('admin.becas.edit', $beca) }}?from=show" class="rd-btn rd-btn-primary">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('admin.becas.index') }}" class="rd-btn rd-btn-default">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    @include('components.alert')

    <div class="rd-card p-4 mb-4">
        <div class="row">
            <div class="col-md-4">
                <strong>Estado</strong>
                <p class="mb-0 mt-2">
                    <span class="rd-badge {{ $beca->activo ? 'rd-badge-success' : 'rd-badge-danger' }}">
                        {{ $beca->activo ? 'Activa' : 'Inactiva' }}
                    </span>
                </p>
            </div>
        </div>
        <strong>Descripcion</strong>
        <p class="mb-0">{{ $beca->descripcion ?: 'Sin descripcion.' }}</p>
    </div>

    <div class="rd-card p-4 mb-4">
        <div class="rd-card-header mb-3">
            <h3 class="rd-title-sm">Preguntas de la beca</h3>
        </div>
        <table class="rd-table">
            <thead>
                <tr>
                    <th>Pregunta</th>
                    <th>Tipo</th>
                    <th>Limitador</th>
                </tr>
            </thead>
            <tbody>
                @forelse($beca->preguntas as $pregunta)
                    <tr>
                        <td>{{ $pregunta->texto }}</td>
                        <td>{{ $pregunta->tipo === 'number' ? 'NÃºmero' : 'Texto' }}</td>
                        <td>
                            @if($pregunta->tipo === 'number')
                                {{ $pregunta->min !== null || $pregunta->max !== null ? 'Min: ' . ($pregunta->min ?? '-') . ' / Max: ' . ($pregunta->max ?? '-') : 'Sin lÃ­mite' }}
                            @else
                                â€”
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center py-4">No hay preguntas registradas para esta beca.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="rd-card p-4 mb-4">
        <div class="rd-card-header mb-3">
            <h3 class="rd-title-sm">Beneficios asignados</h3>
        </div>
        <table class="rd-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>DescripciÃ³n</th>
                    <th>ObservaciÃ³n</th>
                </tr>
            </thead>
            <tbody>
                @forelse($beca->beneficios as $beneficio)
                    <tr>
                        <td>{{ $beneficio->nombre_beneficio }}</td>
                        <td>{{ $beneficio->descripcion ?: 'Sin descripciÃ³n' }}</td>
                        <td>{{ $beneficio->pivot->observacion ?: 'Sin observaciÃ³n' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center py-4">No hay beneficios asignados a esta beca.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="rd-card p-4 mb-4">
        <div class="rd-card-header mb-3">
            <h3 class="rd-title-sm">Tutores de la beca</h3>
        </div>
        <table class="rd-table">
            <thead>
                <tr>
                    <th>Rol</th>
                    <th>Tutor</th>
                    <th>DescripciÃ³n</th>
                </tr>
            </thead>
            <tbody>
                @forelse($beca->tutores as $tutor)
                    <tr>
                        <td>{{ optional($tutor->rol)->nombre ?? 'Sin rol' }}</td>
                        <td>{{ $tutor->tutor ? trim($tutor->tutor->nombre_persona . ' ' . $tutor->tutor->apellido_persona) : 'Sin tutor asignado' }}</td>
                        <td>{{ $tutor->descripcion ?: 'Sin descripciÃ³n' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center py-4">No hay tutores asignados a esta beca.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@stop

