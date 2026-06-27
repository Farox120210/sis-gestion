<div class="card card-outline card-secondary mt-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">Archivos / versiones</h3>
    <small class="text-muted">Sube uno o varios PDFs y marca uno como principal.</small>
  </div>

  @php
      $archivos = collect($archivos ?? []);
  @endphp

  <div class="card-body">
    <form action="{{ route('publicaciones.archivos.store', $pub->id_publicacion) }}"
          method="POST" enctype="multipart/form-data" class="mb-3">
      @csrf
      <div class="form-row">
        <div class="col-md-3 mb-2">
          <label class="form-label">Tipo</label>
          <select name="tipo" class="form-control">
            <option value="">—</option>
            <option value="preprint">Preprint</option>
            <option value="aceptado">Aceptado</option>
            <option value="publicado">Publicado</option>
          </select>
        </div>
        <div class="col-md-7 mb-2">
          <label class="form-label">Selecciona PDF(s)</label>
          <input type="file" name="archivos[]" class="form-control" accept="application/pdf" multiple>
        </div>
        <div class="col-md-2 mb-2 d-flex align-items-end">
          <button class="btn btn-primary btn-block">
            <i class="bi bi-upload"></i> Subir
          </button>
        </div>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-sm table-striped align-middle mb-0">
        <thead>
          <tr>
            <th>Principal</th>
            <th>Versión</th>
            <th>Nombre</th>
            <th>Tipo</th>
            <th>Tamaño</th>
            <th>Subido</th>
            <th>Enlace</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
        @forelse ($archivos as $a)
          <tr>
            <td>
              @if($pub->id_archivo_principal == $a->ID_ARCHIVO)
                <span class="badge badge-success">Sí</span>
              @else
                <form action="{{ route('publicaciones.archivos.principal', [$pub->id_publicacion, $a->ID_ARCHIVO]) }}" method="POST">
                  @csrf @method('PATCH')
                  <button class="btn btn-outline-secondary btn-sm">Marcar</button>
                </form>
              @endif
            </td>
            <td><span class="badge badge-dark">v{{ $a->VERSION ?? '—' }}</span></td>
            <td>{{ $a->NOMBRE_ORIGINAL ?? '—' }}</td>
            <td><span class="badge badge-info">{{ $a->TIPO ?? '—' }}</span></td>
            <td>{{ number_format(($a->SIZE_BYTES ?? 0)/1024, 1) }} KB</td>
            <td>
              @if($a->FECHA_SUBIDA)
                {{ \Carbon\Carbon::parse($a->FECHA_SUBIDA)->format('d/m/Y H:i') }}
              @else
                —
              @endif
            </td>
            <td>
              @php
                  $disk = $a->DISK ?? 'public';
                  $url = $a->URL;

                  if (!$url && $a->PATH) {
                      $candidate = ltrim($a->PATH, '/');

                      if (\Illuminate\Support\Str::startsWith($candidate, 'storage/')) {
                          $url = '/' . $candidate;
                      } else {
                          if (\Illuminate\Support\Str::startsWith($candidate, 'public/')) {
                              $candidate = \Illuminate\Support\Str::after($candidate, 'public/');
                          }

                          if ($candidate) {
                              $url = \Illuminate\Support\Facades\Storage::disk($disk)->url($candidate);
                          }
                      }
                  }
              @endphp
              @if($url)
                <a href="{{ $url }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                  <i class="bi bi-file-earmark-pdf"></i> Ver
                </a>
              @else
                —
              @endif
            </td>
            <td class="text-nowrap">
              <form action="{{ route('publicaciones.archivos.destroy', [$pub->id_publicacion, $a->ID_ARCHIVO]) }}"
                    method="POST" onsubmit="return confirm('¿Eliminar este archivo?')">
                @csrf @method('DELETE')
                <button class="btn btn-danger btn-sm">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="text-center text-muted">Sin archivos todavía.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
