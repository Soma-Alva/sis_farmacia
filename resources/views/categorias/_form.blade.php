@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-3">
    <label for="nombre" class="form-label">
        Nombre <span class="text-danger">*</span>
    </label>
    <input type="text" id="nombre" name="nombre"
           class="form-control @error('nombre') is-invalid @enderror"
           value="{{ old('nombre', $categoria->nombre ?? '') }}"
           maxlength="100" required autofocus>
</div>

<div class="mb-3">
    <label for="descripcion" class="form-label">Descripción</label>
    <textarea id="descripcion" name="descripcion" rows="3"
              class="form-control @error('descripcion') is-invalid @enderror"
              maxlength="200">{{ old('descripcion', $categoria->descripcion ?? '') }}</textarea>
    <div class="form-text">Máximo 200 caracteres.</div>
</div>

<div class="mb-4">
    <div class="form-check">
        <input type="hidden" name="activo" value="0">
        <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1"
               @checked((bool) old('activo', $categoria->activo ?? true))>
        <label class="form-check-label" for="activo">Categoría activa</label>
    </div>
</div>

<button type="submit" class="btn btn-success">
    <i class="bi bi-check-circle"></i> {{ $textoBoton }}
</button>
<a href="{{ route('categorias.index') }}" class="btn btn-secondary">Cancelar</a>
