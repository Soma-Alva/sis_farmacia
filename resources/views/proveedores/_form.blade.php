@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-md-8 mb-3">
        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
        <input type="text" id="nombre" name="nombre"
               class="form-control @error('nombre') is-invalid @enderror"
               value="{{ old('nombre', $proveedor->nombre ?? '') }}" maxlength="150" required autofocus>
    </div>

    <div class="col-md-4 mb-3">
        <label for="ruc" class="form-label">RUC</label>
        <input type="text" id="ruc" name="ruc"
               class="form-control @error('ruc') is-invalid @enderror"
               value="{{ old('ruc', $proveedor->ruc ?? '') }}" maxlength="20">
    </div>

    <div class="col-md-6 mb-3">
        <label for="telefono" class="form-label">Teléfono</label>
        <input type="text" id="telefono" name="telefono"
               class="form-control @error('telefono') is-invalid @enderror"
               value="{{ old('telefono', $proveedor->telefono ?? '') }}" maxlength="20">
    </div>

    <div class="col-md-6 mb-3">
        <label for="email" class="form-label">Correo electrónico</label>
        <input type="email" id="email" name="email"
               class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $proveedor->email ?? '') }}" maxlength="100">
    </div>

    <div class="col-12 mb-3">
        <label for="direccion" class="form-label">Dirección</label>
        <textarea id="direccion" name="direccion" rows="3"
                  class="form-control @error('direccion') is-invalid @enderror">{{ old('direccion', $proveedor->direccion ?? '') }}</textarea>
    </div>

    <div class="col-12 mb-4">
        <div class="form-check">
            <input type="hidden" name="activo" value="0">
            <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1"
                   @checked((bool) old('activo', $proveedor->activo ?? true))>
            <label class="form-check-label" for="activo">Proveedor activo</label>
        </div>
    </div>
</div>

<button type="submit" class="btn btn-success">
    <i class="bi bi-check-circle"></i> {{ $textoBoton }}
</button>
<a href="{{ route('proveedores.index') }}" class="btn btn-secondary">Cancelar</a>
