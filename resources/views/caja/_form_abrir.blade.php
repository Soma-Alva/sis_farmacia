{{--
    Formulario de apertura de caja, usado tanto cuando todavía no existe
    ninguna caja registrada como cuando la última caja quedó cerrada.
    Se separa en su propio partial para no repetir los mismos campos en
    las dos ramas de caja/index.blade.php.
--}}
<style>
    .gf-form-label{
        font-size:12.5px;
        font-weight:600;
        color:var(--verde-oscuro);
        margin-bottom:6px;
        display:block;
    }
    .gf-form-control{
        width:100%;
        border-radius:10px;
        border:1px solid rgba(47,79,62,0.15);
        padding:10px 14px;
        font-size:13.5px;
        color:var(--carbon);
    }
    .gf-form-control:focus{
        outline:none;
        border-color:var(--verde-medio);
        box-shadow:0 0 0 .2rem rgba(75,122,97,0.15);
    }
    .gf-form-group{ margin-bottom:16px; }
    .gf-form-hint{
        font-size:11.5px;
        color:rgba(36,36,32,.55);
        margin-top:6px;
        line-height:1.4;
    }
    .gf-form-check{
        display:flex;
        align-items:center;
        gap:9px;
        background:var(--verde-pastel);
        border-radius:10px;
        padding:11px 14px;
    }
    .gf-form-check input{
        width:16px;
        height:16px;
        accent-color:var(--verde-oscuro);
    }
    .gf-form-check label{
        font-size:13px;
        font-weight:500;
        color:var(--verde-oscuro);
        margin:0;
        cursor:pointer;
    }
</style>

<div class="gf-card-header">
    <i class="bi bi-unlock"></i> Abrir caja
</div>

<div class="p-4">

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('caja.abrir') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-md-4 gf-form-group">
                <label class="gf-form-label">Saldo inicial (C$)</label>
                <input type="number" step="0.01" min="0" name="saldo_inicial"
                       class="gf-form-control" value="{{ old('saldo_inicial') }}" required>
            </div>

            <div class="col-md-4 gf-form-group">
                <label class="gf-form-label">Tipo de cambio</label>
                <input type="number" step="0.0001" min="0" name="tipo_cambio"
                       class="gf-form-control" value="{{ old('tipo_cambio', '36.62') }}" required>
            </div>

            <div class="col-md-4 gf-form-group">
                <label class="gf-form-label">Observaciones (opcional)</label>
                <input type="text" name="observaciones" class="gf-form-control"
                       value="{{ old('observaciones') }}" placeholder="Ej. Turno de la mañana">
            </div>
        </div>

        <div class="row align-items-start">
            <div class="col-md-5 gf-form-group">
                <div class="gf-form-check">
                    <input type="checkbox" id="aplica_iva" name="aplica_iva" value="1"
                           {{ old('aplica_iva') ? 'checked' : '' }}>
                    <label for="aplica_iva">Aplicar IVA en esta caja</label>
                </div>
            </div>

            <div class="col-md-4 gf-form-group">
                <label class="gf-form-label">Porcentaje de IVA</label>
                <input type="number" step="0.01" min="0" max="100" name="iva_porcentaje"
                       class="gf-form-control" value="{{ old('iva_porcentaje', 15) }}">
            </div>

            <div class="col-md-12">
                <p class="gf-form-hint">
                    En Nicaragua los medicamentos están exentos de IVA por el Art. 114 de la
                    Constitución. Deja la casilla sin marcar si en este turno solo se va a
                    vender medicamento; márcala solo si también se van a vender productos
                    gravados (cosméticos, cuidado personal, etc).
                </p>
            </div>
        </div>

        <button type="submit" class="gf-btn-primario mt-2">
            <i class="bi bi-unlock"></i> Abrir caja
        </button>
    </form>

</div>
