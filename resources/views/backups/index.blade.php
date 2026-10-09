@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="gf-page-title">Respaldo y restauración de base de datos</h4>
        <p class="gf-page-subtitle">Administra tus copias de seguridad y restauraciones</p>
    </div>
    <form method="POST" action="{{ route('backups.create') }}" class="d-inline">
        @csrf
        <button type="submit" class="gf-btn-primario">
            <i class="bi bi-download"></i> Crear respaldo ahora
        </button>
    </form>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="gf-stat">
            <div class="icono" style="background:var(--verde-oscuro);"><i class="bi bi-archive"></i></div>
            <div class="etiqueta">Total de respaldos</div>
            <div class="valor">{{ count($backups) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="gf-stat">
            <div class="icono" style="background:var(--verde-medio);"><i class="bi bi-hdd"></i></div>
            <div class="etiqueta">Tamaño total</div>
            @php
                $totalSize = 0;
                foreach($backups as $backup) {
                    $filePath = storage_path('app/backups/' . $backup);
                    if(file_exists($filePath)) {
                        $totalSize += filesize($filePath);
                    }
                }
            @endphp
            <div class="valor">{{ number_format($totalSize / 1024 / 1024, 2) }} MB</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="gf-stat">
            <div class="icono" style="background:#C98A3B;"><i class="bi bi-folder2"></i></div>
            <div class="etiqueta">Ubicación</div>
            <div class="valor" style="font-size:14px;font-family:monospace;">storage/app/backups</div>
        </div>
    </div>
</div>

<div class="gf-card mb-3">
    <div class="gf-card-header">
        <i class="bi bi-file-earmark-arrow-down"></i> Archivos de respaldo
    </div>
    <div class="p-3">
        @if(count($backups) > 0)
            <div class="table-responsive">
                <table class="gf-table table-hover">
                    <thead>
                        <tr>
                            <th>Archivo</th>
                            <th>Tamaño</th>
                            <th>Fecha creación</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($backups as $backup)
                            @php
                                $filePath = storage_path('app/backups/' . $backup);
                                $fileSize = filesize($filePath);
                                $modTime = filemtime($filePath);
                            @endphp
                            <tr>
                                <td>
                                    <i class="bi bi-file-earmark-text"></i>
                                    {{ $backup }}
                                </td>
                                <td>
                                    <span class="gf-badge gf-badge-neutro">{{ number_format($fileSize / 1024 / 1024, 2) }} MB</span>
                                </td>
                                <td class="gf-page-subtitle">{{ date('d/m/Y H:i:s', $modTime) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('backups.download', ['file' => $backup]) }}"
                                       class="gf-btn-soft gf-btn-neutro" title="Descargar">
                                        <i class="bi bi-cloud-download"></i>
                                    </a>
                                    <form method="POST" action="{{ route('backups.restore') }}" class="d-inline"
                                          onsubmit="return confirm('¿Desea restaurar esta copia de seguridad? Esta acción no se puede deshacer.')">
                                        @csrf
                                        <input type="hidden" name="file" value="backups/{{ $backup }}">
                                        <button type="submit" class="gf-btn-soft gf-btn-peligro" title="Restaurar">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-warning mb-0">
                <i class="bi bi-info-circle"></i>
                No hay respaldos creados aún. Haz clic en "Crear respaldo ahora" para comenzar.
            </div>
        @endif
    </div>
</div>

<div class="gf-card">
    <div class="gf-card-header">
        <i class="bi bi-gear"></i> Ayuda y configuración
    </div>
    <div class="p-3">
        <ul class="small mb-0">
            <li><strong>Crear respaldo:</strong> Guarda una copia completa de la base de datos en la carpeta de almacenamiento.</li>
            <li><strong>Descargar:</strong> Descarga el archivo SQL a tu computadora.</li>
            <li><strong>Restaurar:</strong> Recarga la base de datos con los datos de una copia anterior.</li>
            <li><strong>Automático:</strong> Los respaldos se ejecutan automáticamente a las 2:00 AM cada día.</li>
        </ul>
    </div>
</div>

@endsection
