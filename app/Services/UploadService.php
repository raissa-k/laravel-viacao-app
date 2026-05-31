<?php

// Service de upload: valida e salva arquivos com segurança.
// A lógica de segurança é a mesma (validar MIME, tamanho, nome aleatório).
// A diferença é a API: aqui usamos UploadedFile (do Laravel/Symfony) e Storage facade.

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadService
{
    private array $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];

    /*
     * No Laravel: $file->storeAs() com disco 'local' salva em storage/app/private/uploads/.
     *
     * Por que 'local' e não 'public'?
     * O disco 'public' fica em storage/app/public/, que é linkado pra public/storage/.
     * Arquivos lá são acessíveis diretamente pela URL sem passar pelo PHP.
     * O disco 'local' fica em storage/app/private/, não acessível diretamente.
     * O UploadController serve esses arquivos com validação.
     * Pesquise "Laravel Storage disks", "directory traversal attack", "access control".
     *
     * Exception handling: lançamos RuntimeException se validação falhar
     * - Try/catch fica NO CONTROLLER, não aqui no service
     * - Separação de responsabilidades: service valida e lança exception, controller trata a exception e decide como responder (redirection vs JSON)
     */
    public function handleUpload(UploadedFile $file): string
    {
        /*
         * getMimeType() usa finfo internamente para detectar o MIME pelo conteúdo real, não pela extensão.
         * A validação de tipo e tamanho principal vem do ViacaoRequest (FormRequest).
         * Essa checagem aqui é uma defesa extra para uso direto do service (ex: API).
         */
        $mime = $file->getMimeType();

        if (! in_array($mime, $this->allowedMime, true)) {
            throw new \RuntimeException('Tipo de arquivo não permitido.');
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            throw new \RuntimeException('Arquivo maior que o permitido.');
        }

        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'bin',
        };

        // Nome aleatório: bin2hex de 8 bytes aleatórios
        $name = bin2hex(random_bytes(8)).'.'.$ext;

        // storeAs() salva em storage/app/private/uploads/<name>
        // Em produção real é praxe, na verdade, fazer o upload pra nuvem ao invés de lotar o armazenamento do servidor.
        $file->storeAs('uploads', $name, 'local');

        return $name;
    }

    /** Verifica se o arquivo existe no storage privado. */
    public function exists(string $filename): bool
    {
        return Storage::disk('local')->exists('uploads/'.$filename);
    }

    /** Retorna o caminho absoluto do arquivo no servidor. */
    public function path(string $filename): string
    {
        return Storage::disk('local')->path('uploads/'.$filename);
    }

    /**
     * Remove o arquivo do storage.
     *
     * Se o arquivo existir e a exclusão falhar, registramos warning no log.
     * Isso evita "falha silenciosa" e ajuda a investigar permissões de disco ou inconsistências de filesystem.
     */
    public function delete(string $filename): void
    {
        $safe = basename($filename); // proteção extra contra path traversal
        $path = 'uploads/'.$safe;

        if (Storage::disk('local')->exists($path)) {
            $deleted = Storage::disk('local')->delete($path);

            if (! $deleted) {
                Log::warning('Falha ao remover arquivo de upload.', [
                    'filename' => $safe,
                    'path' => $path,
                ]);
            }
        }
    }
}
