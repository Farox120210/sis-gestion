<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Http;

class ArchivoPublicacionController extends Controller
{
    public function store(Request $request, int $idPublicacion)
    {
        $data = $request->validate([
            'tipo'         => 'nullable|string|max:30',
            'archivos'     => 'required',
            'archivos.*'   => 'file|mimetypes:application/pdf|max:51200', // 50MB c/u
        ]);

        $pub = DB::table('proyecto_revista')->where('ID_PROYECTO_REVISTA', $idPublicacion)->first();
        abort_unless($pub, 404);

        $disk = 'public';
        $insertados = [];

        foreach ($request->file('archivos', []) as $file) {
            $maxVersion = DB::table('proyecto_revista_archivos')
                ->where('ID_PROYECTO_REVISTA', $idPublicacion)->max('VERSION');
            $nextVersion = (int) $maxVersion + 1;

            $stored = $file->store("public/publicaciones/$idPublicacion");
            $path   = Storage::url($stored);
            $hash   = hash_file('sha256', $file->getRealPath());

            $idArchivo = DB::table('proyecto_revista_archivos')->insertGetId([
                'ID_PROYECTO_REVISTA' => $idPublicacion,
                'NOMBRE_ORIGINAL'     => $file->getClientOriginalName(),
                'DISK'                => $disk,
                'PATH'                => $path,
                'URL'                 => null,
                'MIME_TYPE'           => $file->getMimeType(),
                'SIZE_BYTES'          => $file->getSize(),
                'HASH_SHA256'         => $hash,
                'TIPO'                => $request->input('tipo'),
                'VERSION'             => $nextVersion,
                'LICENCIA'            => null,
            ]);

            $insertados[] = $idArchivo;
        }

        if (empty($pub->ID_ARCHIVO_PRINCIPAL) && !empty($insertados)) {
            DB::table('proyecto_revista')
              ->where('ID_PROYECTO_REVISTA', $idPublicacion)
              ->update(['ID_ARCHIVO_PRINCIPAL' => $insertados[0]]);
        }

        // (Opcional) notificar a n8n con URL firmada del archivo principal
        if (!empty($insertados) && config('services.n8n.webhook')) {
            $principalId = DB::table('proyecto_revista')
                ->where('ID_PROYECTO_REVISTA', $idPublicacion)
                ->value('ID_ARCHIVO_PRINCIPAL');

            if ($principalId) {
                $signed = URL::temporarySignedRoute(
                    'integrations.n8n.archivo', now()->addMinutes(30), ['archivo' => $principalId]
                );

                Http::timeout(20)->post(config('services.n8n.webhook'), [
                    'secret'         => config('services.n8n.secret'),
                    'publication_id' => $idPublicacion,
                    'archivo_id'     => $principalId,
                    'file_url'       => $signed,
                    'note'           => 'new-files-uploaded',
                ]);
            }
        }

        return back()->with('ok', 'Archivo(s) subido(s) correctamente.');
    }

    public function destroy(Request $request, int $idPublicacion, int $idArchivo)
    {
        $archivo = DB::table('proyecto_revista_archivos')
            ->where('ID_ARCHIVO', $idArchivo)
            ->where('ID_PROYECTO_REVISTA', $idPublicacion)
            ->first();
        abort_unless($archivo, 404);

        if ($archivo->DISK === 'public' && $archivo->PATH) {
            $storagePath = str_replace('/storage/', 'public/', $archivo->PATH);
            Storage::delete($storagePath);
        }

        $pub = DB::table('proyecto_revista')->where('ID_PROYECTO_REVISTA', $idPublicacion)->first();
        if ($pub && (int) $pub->ID_ARCHIVO_PRINCIPAL === (int) $idArchivo) {
            DB::table('proyecto_revista')
              ->where('ID_PROYECTO_REVISTA', $idPublicacion)
              ->update(['ID_ARCHIVO_PRINCIPAL' => null]);
        }

        DB::table('proyecto_revista_archivos')
          ->where('ID_ARCHIVO', $idArchivo)
          ->delete();

        return back()->with('ok', 'Archivo eliminado.');
    }

    public function setPrincipal(Request $request, int $idPublicacion, int $idArchivo)
    {
        $exists = DB::table('proyecto_revista_archivos')
          ->where('ID_ARCHIVO', $idArchivo)
          ->where('ID_PROYECTO_REVISTA', $idPublicacion)
          ->exists();
        abort_unless($exists, 404);

        DB::table('proyecto_revista')
          ->where('ID_PROYECTO_REVISTA', $idPublicacion)
          ->update(['ID_ARCHIVO_PRINCIPAL' => $idArchivo]);

        return back()->with('ok', 'Archivo marcado como principal.');
    }
}
