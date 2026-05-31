<?php

// Controller de upload: serve arquivos salvos fora do docroot com segurança.
// Compare com src/Controllers/UploadController.php do PHP puro.
// A lógica de segurança é idêntica (path traversal, MIME detection).
// No Laravel: response()->file() substitui o header() + readfile() manual.

namespace App\Http\Controllers;

use App\Services\UploadService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UploadController extends Controller
{
    public function __construct(
        private readonly UploadService $uploadService,
    ) {}

    public function serve(string $filename): BinaryFileResponse
    {
        /*
         * Proteção contra Path Traversal: basename() elimina qualquer "../" ou "/" do nome, prevenindo navegação acima do diretório.
         * Se o nome mudou após basename(), é suspeito e rejeitamos.
         * Pesquise "directory traversal attack", "OWASP File Upload".
         */
        if (basename($filename) !== $filename) {
            abort(400, 'Nome de arquivo inválido.');
        }

        if (! $this->uploadService->exists($filename)) {
            abort(404, 'Arquivo não encontrado.');
        }

        $path = $this->uploadService->path($filename);

        /*
         * response()->file() detecta o MIME, define Content-Type e Content-Length automaticamente.
         * No PHP puro: finfo_open() + finfo_file() + header('Content-Type: ...') etc.
         * O Laravel abstrai esses headers, mas o comportamento de segurança é o mesmo.
         */
        return response()->file($path, [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
