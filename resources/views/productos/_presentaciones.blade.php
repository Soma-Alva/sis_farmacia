{{-- Presentaciones de venta por paquete (ej. "Caja x100"). Opcional.
     Variable opcional: $presentaciones (colección, al editar). --}}
@php
    $filasIniciales = old('presentaciones');
    if ($filasIniciales === null) {
        $filasIniciales = isset($presentaciones)
            ? $presentaciones->map(fn ($p) => [
                'nombre' => $p->nombre,
                'unidades' => $p->unidades,
                'precio_venta' => $p->precio_venta,
            ])->values()->all()
            : [];
    }
@endphp

<div class="mb-3 p-3" style="border:1px solid rgba(47,79,62,0.15); border-radius:12px; background:#F7FAF8;">
    <label class="mb-1"><strong>Presentaciones por caja / paquete</strong> <small class="text-muted">(opcional)</small></label>
    <div class="text-muted small mb-2">
        El producto sigue manejándose por unidad. Aquí defines paquetes que se venden a un
        precio fijo (por ejemplo, "Caja x100" con 100 unidades a C$ 280). Al vender un paquete
        se descuentan sus unidades del stock.
    </div>

    @error('presentaciones')
        <div class="text-danger small mb-2">{{ $message }}</div>
    @enderror

    <div id="presentaciones-filas"></div>

    <button type="button" class="btn btn-sm btn-outline-success" onclick="agregarFilaPresentacion()">
        <i class="bi bi-plus-circle"></i> Agregar presentación
    </button>
</div>

<script>
    (function(){
        const contenedor = document.getElementById('presentaciones-filas');
        let indice = 0;

        window.agregarFilaPresentacion = function(datos){
            datos = datos || {};
            const i = indice++;
            const fila = document.createElement('div');
            fila.className = 'row g-2 mb-2 align-items-end';
            fila.innerHTML = `
                <div class="col-md-5">
                    <input type="text" class="form-control" name="presentaciones[${i}][nombre]"
                           placeholder="Caja x100" maxlength="100">
                </div>
                <div class="col-md-3">
                    <input type="number" class="form-control" name="presentaciones[${i}][unidades]"
                           placeholder="Unidades" min="2" step="1">
                </div>
                <div class="col-md-3">
                    <input type="number" class="form-control" name="presentaciones[${i}][precio_venta]"
                           placeholder="Precio caja" min="0" step="0.01">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger w-100" title="Quitar"
                            onclick="this.closest('.row').remove()"><i class="bi bi-trash"></i></button>
                </div>`;
            contenedor.appendChild(fila);
            fila.querySelector('[name$="[nombre]"]').value = datos.nombre ?? '';
            fila.querySelector('[name$="[unidades]"]').value = datos.unidades ?? '';
            fila.querySelector('[name$="[precio_venta]"]').value = datos.precio_venta ?? '';
        };

        @json($filasIniciales).forEach(function(f){ agregarFilaPresentacion(f); });
    })();
</script>
