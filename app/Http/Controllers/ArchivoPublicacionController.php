<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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
        $directory = "publicaciones/{$idPublicacion}";
        $insertados = [];

        Storage::disk($disk)->makeDirectory($directory);

        foreach ($request->file('archivos', []) as $file) {
            $maxVersion = DB::table('proyecto_revista_archivos')
                ->where('ID_PROYECTO_REVISTA', $idPublicacion)->max('VERSION');
            $nextVersion = (int) $maxVersion + 1;

            $fileName = $file->hashName();
            $storedPath = Storage::disk($disk)->putFileAs($directory, $file, $fileName);

            if (empty($storedPath)) {
                throw new \RuntimeException('No se pudo almacenar el archivo en el disco configurado.');
            }

            $publicUrl = Storage::disk($disk)->url($storedPath);
            $absolutePath = Storage::disk($disk)->path($storedPath);
            $hash = hash_file('sha256', $absolutePath);

            $idArchivo = DB::table('proyecto_revista_archivos')->insertGetId([
                'ID_PROYECTO_REVISTA' => $idPublicacion,
                'NOMBRE_ORIGINAL'     => $file->getClientOriginalName(),
                'DISK'                => $disk,
                'PATH'                => $storedPath,
                'URL'                 => $publicUrl,
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

        if ($archivo->PATH) {
            $disk = $archivo->DISK ?: config('filesystems.default');
            $rawPath = $archivo->PATH;

            if (!Str::startsWith($rawPath, ['http://', 'https://'])) {
                $normalizedBase = ltrim($rawPath, '/');
                $candidates = [$normalizedBase];

                if (Str::contains($normalizedBase, '/storage/')) {
                    $candidates[] = ltrim(Str::after($normalizedBase, '/storage/'), '/');
                }

                if (Str::startsWith($normalizedBase, 'storage/')) {
                    $candidates[] = Str::after($normalizedBase, 'storage/');
                }

                if (Str::contains($normalizedBase, '/public/')) {
                    $candidates[] = ltrim(Str::after($normalizedBase, '/public/'), '/');
                }

                if (Str::startsWith($normalizedBase, 'public/')) {
                    $candidates[] = Str::after($normalizedBase, 'public/');
                }

                foreach ($candidates as $candidate) {
                    $cleanCandidate = preg_replace('#^(storage/|public/)+#', '', $candidate);
                    $pathToDelete = $cleanCandidate ?: $candidate;

                    if ($pathToDelete && Storage::disk($disk)->exists($pathToDelete)) {
                        Storage::disk($disk)->delete($pathToDelete);
                    }
                }
            }
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
