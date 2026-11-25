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

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="gf-stat">
            <div class="icono" style="background:var(--verde-oscuro);"><i class="bi bi-archive"></i></div>
            <div class="etiqueta">Total de respaldos</div>
            <div class="valor">{{ $backups->total() }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="gf-stat">
            <div class="icono" style="background:var(--verde-medio);"><i class="bi bi-hdd"></i></div>
            <div class="etiqueta">Tamaño total</div>
            <div class="valor">{{ \App\Http\Controllers\BackupController::formatearTamano((int) $tamanoTotal) }}</div>
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
        @if($backups->total() > 0)
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
                                    <span class="gf-badge {{ $fileSize < 200 ? 'gf-badge-peligro' : 'gf-badge-neutro' }}">{{ \App\Http\Controllers\BackupController::formatearTamano((int) $fileSize) }}</span>
                                </td>
                                <td class="gf-page-subtitle">{{ date('d/m/Y H:i:s', $modTime) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('backups.download', ['file' => $backup]) }}"
                                       class="gf-btn-soft gf-btn-neutro" title="Descargar">
                                        <i class="bi bi-cloud-download"></i>
                                    </a>
                                    <form method="POST" action="{{ route('backups.restore') }}" class="d-inline"
                                          onsubmit="return confirm('¿Restaurar esta copia? Se reemplazarán los datos actuales por los del respaldo. Antes se guardará automáticamente una copia de seguridad del estado actual.')">
                                        @csrf
                                        <input type="hidden" name="file" value="{{ $backup }}">
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

            <div class="px-2 pt-2">
                {{ $backups->links('pagination::bootstrap-5') }}
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
            <li><strong>Crear respaldo:</strong> Guarda una copia completa y verificada de la base de datos (estructura y datos).</li>
            <li><strong>Descargar:</strong> Descarga el archivo SQL a tu computadora.</li>
            <li><strong>Restaurar:</strong> Recarga la base de datos con una copia anterior. Se verifica el archivo y se hace antes un respaldo <code>prerestore_...</code> del estado actual.</li>
            <li><strong>Automático:</strong> Se crea un respaldo a las 2:00 AM cada día (se conservan los últimos 30). Requiere que el programador de tareas de Laravel esté activo y la PC encendida a esa hora.</li>
        </ul>
    </div>
</div>

@endsection
