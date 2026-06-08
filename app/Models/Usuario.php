<?php

// Model de usuário: estende Authenticatable pra integrar com o sistema de auth do Laravel.
// No PHP puro, o "model" Usuario era um DTO simples (só propriedades e fromRow/toArray).
// Aqui o model tem responsabilidades extras: diz ao Laravel como autenticar esse usuário.
// Pesquise "Laravel Authenticatable", "auth guards", "user providers".

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    /*
     * HasApiTokens: adiciona createToken(), tokens() e tokenCan() ao model.
     * Sanctum armazena o hash do token na tabela personal_access_tokens.
     * HasFactory: habilita Usuario::factory() nos testes.
     * SoftDeletes: substitui DELETE físico por deleted_at = NOW().
     * Todas as queries passam a filtrar WHERE deleted_at IS NULL automaticamente.
     * Pesquise "Eloquent soft deleting", "withTrashed", "onlyTrashed".
     */
    use HasApiTokens, HasFactory, SoftDeletes, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = ['nome', 'email', 'senha'];

    // Esconde "senha" do toArray() e toJson() por padrão.
    protected $hidden = ['senha'];

    /*
     * CARBON TIMESTAMPS AUTOMÁTICOS:
     * O Eloquent converte automaticamente created_at e updated_at pra Carbon instances.
     * Você não faz nada, é o comportamento padrão de Eloquent model.
     *
     * Na view admin/usuarios/index.blade.php, exibimos assim:
     *   {{ $u->created_at->format('d/m/Y H:i') }}
     *
     * O $u->created_at é uma instance de Carbon, então podemos:
     * - ->format('...'): formatar a data
     * - ->diffForHumans(): "3 days ago"/"3 dias atrás" (com locale)
     * - ->isToday(), ->isYesterday(): comparações úteis
     * - ->addDays(), ->subMonths(): manipulação
     * - ->timezone(): gerenciar timezones
     *
     * Se em algum momento você mudar o banco de dados e RENAME uma coluna de timestamp, atualize:
     *   public const CREATED_AT = 'new_column_name';
     * Pesquise: "Carbon", "Laravel timestamps", "date casting".
     */

    /*
     * Informa ao Laravel o nome da coluna de senha (padrão seria "password").
     * Pesquise "getAuthPasswordName Laravel", "Authenticatable contract".
     */
    public function getAuthPasswordName(): string
    {
        return 'senha';
    }

    // Desativa o "lembrar-me" (remember token) que o Eloquent esperaria por padrão.
    public function getRememberTokenName(): string
    {
        return '';
    }

    /*
     * Relacionamento polimórfico: registros de auditoria deste usuário.
     * O Laravel filtra automaticamente por entidade_type='usuario' + entidade_id=$this->id.
     * Pesquise "Eloquent morphMany", "polymorphic relationships".
     */
    public function historico(): MorphMany
    {
        return $this->morphMany(Historico::class, 'entidade');
    }
}
