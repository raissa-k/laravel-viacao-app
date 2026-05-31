<?php

// Model do histórico de alterações.
// O Eloquent faz o decode do JSON automaticamente via cast json:unicode.

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViacaoHistorico extends Model
{
    use HasFactory;

    protected $table = 'viacoes_historico';

    protected $fillable = ['viacao_id', 'usuario_id', 'acao', 'alteracoes'];

    /*
     * Renomeia a coluna de criação e desabilita updated_at.
     *
     * CREATED_AT = 'criado_em': Eloquent escreve now() em 'criado_em' ao fazer create().
     * UPDATED_AT = null: instrui o Eloquent a não tocar nenhuma coluna de atualização.
     *
     * Por que remover $timestamps = false?
     * $timestamps = false desabilita o gerenciamento de timestamps completamente.
     * As constantes CREATED_AT e UPDATED_AT não têm nenhum efeito quando está false.
     * Com as constantes e sem $timestamps = false, o Eloquent lida com 'criado_em' automaticamente e $h->criado_em vira uma Carbon instance em vez de string bruta.
     *
     *
     * CARBON INTEGRATION:
     * O Eloquent retorna $h->criado_em como Carbon instance, não string.
     * Isso significa você pode usar todos os métodos Carbon:
     *
     *   $h->criado_em->format('d/m/Y H:i') => "23/01/2026 14:30"
     *   $h->criado_em->diffForHumans()     => "2 hours ago"
     *   $h->criado_em->isPast()            => true
     *   $h->criado_em->toIso8601String()   => "2026-01-23T14:30:45+00:00"
     *
     * Pesquise: "Carbon documentation", "Laravel timestamps", "CREATED_AT constant".
     */
    public const CREATED_AT = 'criado_em';

    public const UPDATED_AT = null;

    /*
     * 'json:unicode', introduzido no Laravel 12.3, disponível no Laravel 13.
     * Funciona como 'json'/'array' (json_encode ao salvar, json_decode ao ler), mas adiciona JSON_UNESCAPED_UNICODE no encode.
     * 'array' e 'json' produzem o mesmo resultado funcional, mas gravam unicode escapado.
     * Para um sistema em português, 'json:unicode' é a escolha mais legível.
     *
     * Pesquise "Laravel json:unicode cast", "JSON_UNESCAPED_UNICODE PHP".
     */
    protected $casts = [
        'alteracoes' => 'json:unicode',
    ];

    /*
     * Relacionamentos: substituem os LEFT JOINs do HistoricoRepository::findAll().
     * Aqui: $historico->usuario (lazy loading) ou with('usuario') (eager loading)
     *
     * withDefault() retorna um model vazio quando a relação não existe (viacao excluída ou usuario_id null) em vez de retornar null.
     */
    public function viacao(): BelongsTo
    {
        return $this->belongsTo(Viacao::class, 'viacao_id')->withDefault();
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id')->withDefault();
    }
}
