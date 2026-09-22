@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Respaldo y restauración de base de datos</h2>
            <p class="text-muted small mt-2">Administra tus copias de seguridad y restauraciones</p>
        </div>
        <form method="POST" action="{{ route('backups.create') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success btn-lg">
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

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-title text-muted">Total de respaldos</h6>
                    <h3 class="card-text">{{ count($backups) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-title text-muted">Tamaño total</h6>
                    @php
                        $totalSize = 0;
                        foreach($backups as $backup) {
                            $filePath = storage_path('app/backups/' . $backup);
                            if(file_exists($filePath)) {
                                $totalSize += filesize($filePath);
                            }
                        }
                    @endphp
                    <h3 class="card-text">{{ number_format($totalSize / 1024 / 1024, 2) }} MB</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-title text-muted">Ubicación</h6>
                    <small class="card-text text-monospace text-truncate d-block">storage/app/backups</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-4">
                <i class="bi bi-file-earmark-arrow-down"></i> Archivos de respaldo
            </h5>
            @if(count($backups) > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
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
                                        <small>{{ $backup }}</small>
                                    </td>
                                    <td>
                                        <small class="badge bg-info">{{ number_format($fileSize / 1024 / 1024, 2) }} MB</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ date('d/m/Y H:i:s', $modTime) }}</small>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('backups.download', ['file' => $backup]) }}"
                                           class="btn btn-sm btn-outline-primary" title="Descargar">
                                            <i class="bi bi-cloud-download"></i>
                                        </a>
                                        <form method="POST" action="{{ route('backups.restore') }}" class="d-inline"
                                              onsubmit="return confirm('¿Desea restaurar esta copia de seguridad? Esta acción no se puede deshacer.')">
                                            @csrf
                                            <input type="hidden" name="file" value="backups/{{ $backup }}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Restaurar">
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

    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h5 class="card-title mb-3">
                <i class="bi bi-gear"></i> Ayuda y configuración
            </h5>
            <ul class="small">
                <li><strong>Crear respaldo:</strong> Guarda una copia completa de la base de datos en la carpeta de almacenamiento.</li>
                <li><strong>Descargar:</strong> Descarga el archivo SQL a tu computadora.</li>
                <li><strong>Restaurar:</strong> Recarga la base de datos con los datos de una copia anterior.</li>
                <li><strong>Automático:</strong> Los respaldos se ejecutan automáticamente a las 2:00 AM cada día.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
