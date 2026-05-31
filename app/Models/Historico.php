<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Historico extends Model
{
    use HasFactory;

    protected $table = 'historico';

    protected $fillable = ['entidade_type', 'entidade_id', 'usuario_id', 'acao', 'alteracoes'];

    /*
     * CREATED_AT = 'criado_em': Eloquent escreve now() nessa coluna ao criar.
     * UPDATED_AT = null: log é imutável, não tem "última atualização".
     */
    public const CREATED_AT = 'criado_em';

    public const UPDATED_AT = null;

    protected $casts = [
        /*
         * decodifica o JSON ao ler e codifica com JSON_UNESCAPED_UNICODE ao salvar (mantém acentos legíveis no banco).
         * Pesquise "Laravel json:unicode cast".
         */
        'alteracoes' => 'json:unicode',
    ];

    /*
     * entidade(): relação polimórfica com a entidade auditada (Viacao ou Usuario).
     *
     * COMO O LARAVEL RESOLVE AS COLUNAS:
     * morphTo() sem argumentos usa o nome do method como prefixo.
     * method = 'entidade'  ->  coluna de tipo: entidade_type, coluna de id: entidade_id.
     * Se o method se chamasse 'sujeito', as colunas esperadas seriam sujeito_type e sujeito_id.
     *
     * COMO O LARAVEL RESOLVE O MODEL:
     * Lê entidade_type ('viacao') -> consulta o morph map -> obtém Viacao::class -> instancia.
     * Sem morph map: entidade_type conteria o FQCN 'App\Models\Viacao' diretamente (comum, mas aqui fomos além pra aprendizado).
     *
     * withTrashed() inclui registros soft-deleted porque o log deve sobreviver à exclusão.
     * withDefault() evita null pointer nos templates quando a entidade não existe.
     *
     * Pesquise "Eloquent polymorphic relationships", "MorphTo", "morph map".
     */
    public function entidade(): MorphTo
    {
        return $this->morphTo()->withTrashed()->withDefault();
    }

    /*
     * ator(): quem realizou a ação (via usuario_id).
     * withDefault() retorna um model vazio (nome = '') quando usuario_id é null (ex: criado por importação)
     * Assim, nos templates $h->ator->nome nunca lança erro.
     */
    public function ator(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id')->withTrashed()->withDefault();
    }
}
