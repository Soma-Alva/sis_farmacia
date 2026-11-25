@extends('layouts.app')

@section('content')

<style>
    .gf-tabla-kv th{ background:var(--verde-pastel); color:var(--verde-oscuro); font-size:13px; font-weight:600; border:none; padding:12px 16px; }
    .gf-tabla-kv td{ font-size:14px; border:none; padding:12px 16px; border-bottom:1px solid rgba(47,79,62,0.08); vertical-align:middle; }
    .gf-form-label{ font-size:12.5px; font-weight:600; color:var(--verde-oscuro); margin-bottom:6px; display:block; }
    .gf-form-control{ width:100%; border-radius:10px; border:1px solid rgba(47,79,62,0.15); padding:10px 14px; font-size:13.5px; color:var(--carbon); }
    .gf-form-control:focus{ outline:none; border-color:var(--verde-medio); box-shadow:0 0 0 .2rem rgba(75,122,97,0.15); }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="gf-page-title mb-0">Conciliación bancaria</h4>
        <p class="gf-page-subtitle mb-0">Compara lo que registró el sistema (tarjeta y transferencia) contra lo que confirma el banco. El efectivo físico se cuenta aparte en el arqueo de caja.</p>
    </div>
    <a href="{{ route('caja.index') }}" class="gf-btn-soft gf-btn-apagado">
        <i class="bi bi-arrow-left"></i> Volver a caja
    </a>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="gf-card">
            <div class="gf-card-header">Arqueo bancario del turno</div>
            <form action="{{ route('caja.conciliacion.guardar') }}" method="POST" class="p-3" id="gf-conc-form">
                @csrf
                <div class="table-responsive">
                    <table class="table gf-tabla-kv mb-0">
                        <thead><tr><th>Medio</th><th>Sistema</th><th style="width:38%;">Confirmado por banco</th><th>Dif.</th></tr></thead>
                        @foreach(['tarjeta' => ['TARJETA','Tarjeta (voucher POS)'], 'transferencia' => ['TRANSFERENCIA','Transferencia (estado de cuenta)']] as $k => [$m,$label])
                        <tr>
                            <td>{{ $label }}</td>
                            <td data-esp="{{ $totales[$m] }}">C$ {{ number_format($totales[$m], 2) }}</td>
                            <td><input type="number" step="0.01" min="0" name="confirmado_{{ $k }}" class="gf-form-control gf-conf" data-esp="{{ $totales[$m] }}" value="{{ old('confirmado_'.$k) }}" required></td>
                            <td class="gf-dif" style="font-weight:600;">—</td>
                        </tr>
                        @endforeach
                        <tr>
                            <td><strong>Total</strong></td>
                            <td><strong>C$ {{ number_format($totales['TARJETA'] + $totales['TRANSFERENCIA'], 2) }}</strong></td>
                            <td></td>
                            <td id="gf-dif-total" style="font-weight:700;">—</td>
                        </tr>
                    </table>
                </div>
                <div class="mt-3">
                    <label class="gf-form-label">Observaciones</label>
                    <textarea name="observaciones" rows="2" maxlength="500" class="gf-form-control">{{ old('observaciones') }}</textarea>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="gf-btn-primario" style="background:#2F5D8A;">
                        <i class="bi bi-bank"></i> Guardar conciliación
                    </button>
                </div>
            </form>
        </div>

        @if($historial->count())
        <div class="gf-card mt-3">
            <div class="gf-card-header">Conciliaciones guardadas en este turno</div>
            <div class="table-responsive p-2">
                <table class="table gf-tabla-kv mb-0">
                    <thead><tr><th>Hora</th><th>Usuario</th><th>Diferencia</th><th>Obs.</th></tr></thead>
                    @foreach($historial as $h)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($h->creado_en)->format('d/m h:i A') }}</td>
                        <td>{{ $h->nombre_completo ?? '—' }}</td>
                        <td>
                            @if(abs($h->diferencia_total) < 0.005)
                                <span class="gf-badge gf-badge-exito">Cuadrada</span>
                            @else
                                <span class="gf-badge gf-badge-peligro">C$ {{ number_format($h->diferencia_total, 2) }}</span>
                            @endif
                        </td>
                        <td>{{ $h->observaciones ?: '—' }}</td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-6">
        <div class="gf-card">
            <div class="gf-card-header">Pagos al banco del turno ({{ $pagos->count() }})</div>
            <div class="table-responsive p-2">
                <table class="table gf-tabla-kv mb-0">
                    <thead><tr><th>Hora</th><th>Ticket</th><th>Medio</th><th class="text-end">Monto</th></tr></thead>
                    @forelse($pagos as $p)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($p->creado_en)->format('d/m h:i A') }}</td>
                        <td>{{ $p->numero_ticket ?? ($p->descripcion ?: '—') }}</td>
                        <td>{{ $p->forma_pago === 'TARJETA' ? 'Tarjeta' : 'Transferencia' }}</td>
                        <td class="text-end" style="color:{{ $p->tipo_movimiento === 'EGRESO' ? '#8C3A2A' : 'inherit' }};">
                            {{ $p->tipo_movimiento === 'EGRESO' ? '−' : '' }}C$ {{ number_format($p->monto, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted">Sin pagos al banco en este turno.</td></tr>
                    @endforelse
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    (function(){
        var inputs = document.querySelectorAll('.gf-conf');
        function fmt(n){ return (n<0?'−':(n>0?'+':'')) + 'C$ ' + Math.abs(n).toFixed(2); }
        function paint(el, d, filled){
            el.textContent = filled ? fmt(d) : '—';
            el.style.color = !filled ? '' : (Math.abs(d) < 0.005 ? '#2F6B45' : '#8C3A2A');
        }
        function calc(){
            var total = 0, all = true;
            inputs.forEach(function(i){
                var filled = i.value !== '';
                var d = filled ? parseFloat(i.value) - parseFloat(i.dataset.esp) : 0;
                if(!filled) all = false; else total += d;
                paint(i.closest('tr').querySelector('.gf-dif'), d, filled);
            });
            paint(document.getElementById('gf-dif-total'), total, all);
        }
        inputs.forEach(function(i){ i.addEventListener('input', calc); });
    })();
</script>

@endsection
