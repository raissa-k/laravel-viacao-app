<?php

// Model de viação: representa a entidade principal do demo.
// No PHP puro, o model Viacao era um DTO imutável (final class, readonly-like).
// Aqui o Eloquent model é mutável e faz ORM completo: queries, insert, update, delete.
// Pesquise "Active Record pattern", "ORM vs raw SQL", "Eloquent model".

namespace App\Models;

use App\Services\UploadService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Viacao extends Model
{
    use HasFactory, SoftDeletes;

    // O Eloquent é bem espertinho com pluralização em inglês.
    // Se o seu model fosse "User", ele automaticamente buscaria na tabela 'users' exceto se você declarasse outra coisa aqui.
    // Seguindo o padrão do inglês ele poderia esperar a tabela 'viacaos', então colocamos o nome exato pra evitar problemas.
    protected $table = 'viacoes';

    protected $fillable = ['nome', 'cidade', 'ativa', 'logo'];

    /*
     * Cast automático: o Eloquent converte ativa de TINYINT(1) (0/1) pra bool PHP automaticamente.
     * Pesquise "Eloquent attribute casting".
     *
     * CARBON AUTOMÁTICO:
     * O Eloquent converte TODAS as colunas de timestamp (created_at, updated_at) pra Carbon automaticamente.
     * Você não precisa fazer nada, é comportamento padrão.
     *
     * No banco: created_at = 2026-01-23 14:30:45 (string/datetime)
     * No PHP (Eloquent): $viacao->created_at é Carbon instance
     *
     * Você pode então usar todos os methods Carbon:
     * - $viacao->created_at->format('Y-m-d'): "2026-01-23"
     * - $viacao->created_at->diffForHumans(): "2 hours ago/2 horas atrás"
     * - $viacao->created_at->addDays(7): nova data
     * - $viacao->created_at->isPast(): true/false
     *
     * Se quiser CUSTOMIZAR o cast de uma coluna de data, adicione ao $casts:
     *   protected $casts = [
     *       'ativa' => 'boolean',
     *   ];
     * ou, em Laravel mais moderno,
     *   protected function casts(): array
     *  {
     *      return [ 'ativa' => 'boolean' ];
     * }
     *
     * Pesquise: "Eloquent accessors & mutators", "Carbon date casting", "mutating attributes".
     */
    protected $casts = [
        'ativa' => 'boolean',
    ];

    /*
     * Relacionamento polimórfico: registros de auditoria desta viação.
     * O Laravel filtra automaticamente por entidade_type='viacao' + entidade_id=$this->id.
     * Pesquise "Eloquent morphMany", "polymorphic relationships".
     */
    public function historico(): MorphMany
    {
        return $this->morphMany(Historico::class, 'entidade');
    }

    protected static function booted(): void
    {
        static::forceDeleted(function (self $viacao): void {
            if ($viacao->logo !== null) {
                app(UploadService::class)->delete($viacao->logo);
            }
        });
    }
}
