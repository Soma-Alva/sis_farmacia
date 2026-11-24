{{--
    Barra de filtro de periodo + botón de Excel, compartida por los reportes.
    Recibe: $ruta (nombre de la ruta de la pantalla), $excel (nombre de la ruta
    del Excel), $periodo, $desde, $hasta. Conserva los demás filtros de la URL.
--}}
@php
    $otros = request()->except(['periodo', 'desde', 'hasta', 'page']);
    $paramsExcel = array_merge($otros, [
        'periodo' => 'rango',
        'desde' => $desde->toDateString(),
        'hasta' => $hasta->toDateString(),
    ]);
    $rapidos = ['hoy' => 'Hoy', '7' => '7 días', '30' => '30 días', 'mes' => 'Este mes', 'mes_ant' => 'Mes anterior'];
@endphp

<style>
    .gf-rep-barra{ display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:16px; }
    .gf-rep-periodo{ display:flex; align-items:center; gap:6px; flex-wrap:wrap; background:#fff; border-radius:14px; padding:6px; box-shadow:0 2px 10px rgba(47,79,62,0.06); }
    .gf-rep-periodo a{ padding:7px 14px; border-radius:10px; font-size:13px; font-weight:600; color:var(--verde-oscuro); text-decoration:none; }
    .gf-rep-periodo a:hover{ background:var(--verde-pastel); }
    .gf-rep-periodo a.activo{ background:var(--verde-oscuro); color:#fff; }
    .gf-rep-periodo form{ display:flex; align-items:center; gap:6px; margin:0; }
    .gf-rep-periodo input[type=date]{ border:1px solid rgba(47,79,62,0.15); border-radius:9px; padding:5px 8px; font-size:12.5px; color:var(--carbon); }
    .gf-rep-periodo button{ border:none; background:var(--verde-pastel); color:var(--verde-oscuro); border-radius:9px; padding:6px 12px; font-size:12.5px; font-weight:600; }
    .gf-btn-excel{ display:inline-flex; align-items:center; gap:8px; background:#1E7B45; color:#fff; border-radius:12px; padding:9px 18px; font-size:13.5px; font-weight:600; text-decoration:none; }
    .gf-btn-excel:hover{ background:#176437; color:#fff; }
    .gf-rep-kpi{ background:#fff; border-radius:18px; padding:18px 20px; box-shadow:0 2px 10px rgba(47,79,62,0.06); height:100%; }
    .gf-rep-kpi .et{ font-size:12.5px; color:rgba(36,36,32,0.55); }
    .gf-rep-kpi .val{ font-size:22px; font-weight:700; color:var(--carbon); }
    .gf-rep-kpi .val.pos{ color:#2F7A4B; }
    .gf-rep-kpi .val.neg{ color:#B4452F; }
    .gf-rep-kpi .sub{ font-size:12px; color:rgba(36,36,32,0.55); margin-top:2px; }
    .gf-tot-fila td{ background:var(--verde-pastel); font-weight:700; color:var(--verde-oscuro); }
    .gf-delta{ font-weight:700; }
    .gf-delta.sube{ color:#2F7A4B; } .gf-delta.baja{ color:#B4452F; } .gf-delta.igual{ color:rgba(36,36,32,0.5); }
</style>

<div class="gf-rep-barra">
    <div class="gf-rep-periodo">
        @foreach($rapidos as $clave => $texto)
            <a href="{{ route($ruta, array_merge($otros, ['periodo' => $clave])) }}" class="{{ $periodo === $clave ? 'activo' : '' }}">{{ $texto }}</a>
        @endforeach
        <form method="GET" action="{{ route($ruta) }}">
            @foreach($otros as $k => $v)
                @if(is_scalar($v)) <input type="hidden" name="{{ $k }}" value="{{ $v }}"> @endif
            @endforeach
            <input type="hidden" name="periodo" value="rango">
            <input type="date" name="desde" value="{{ $desde->toDateString() }}" max="{{ now()->toDateString() }}" required>
            <span>–</span>
            <input type="date" name="hasta" value="{{ $hasta->toDateString() }}" max="{{ now()->toDateString() }}" required>
            <button type="submit">Aplicar</button>
        </form>
    </div>

    <a href="{{ route($excel, $paramsExcel) }}" class="gf-btn-excel">
        <i class="bi bi-file-earmark-excel"></i> Exportar Excel
    </a>
</div>
