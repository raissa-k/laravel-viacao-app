<?php

// Seeder de demonstração.
// Cria usuário admin, usuários extras, viações nomeadas e histórico variado.
// Pesquise "factory vs seeder responsibility".

namespace Database\Seeders;

use App\Enums\AcaoHistorico;
use App\Enums\EntidadeHistorico;
use App\Models\Historico;
use App\Models\Usuario;
use App\Models\Viacao;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use App\Models\Cidade;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * GUARD DE AMBIENTE: este seeder NÃO deve rodar em produção.
         *
         * Por que isso importa?
         * Este seeder cria o usuário admin@admin.com com a senha "admin123".
         * Essas credenciais são públicas (estão no README).
         * Se alguém rodar "php artisan db:seed" ou "php artisan migrate --seed" acidentalmente em produção,
         * qualquer pessoa com esse conhecimento consegue logar como admin e ter acesso total ao sistema.
         *
         * O abort() aqui garante que isso nunca aconteça.
         * Em produção, use dados reais configurados manualmente ou via CI secrets.
         *
         * Pesquise: "seeder environment guard", "production seed risk", "php artisan --env".
         */
        if (! App::environment('local', 'testing')) {
            $this->command?->error('Seeder abortado: este seeder só pode rodar em local ou testing.');
            $this->command?->warn('Em produção, crie usuários manualmente ou via CI secrets.');

            return;
        }

        // Usuário admin
        //
        // firstOrCreate em vez de factory()->create() porque as credenciais precisam ser conhecidas (o README e o requests.http documentam este email/senha).
        // Factory geraria um email/senha aleatórios e inúteis para login de demo.
        $admin = Usuario::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'nome' => 'Admin',
                /* Hash::make() usa bcrypt por padrão (config/hashing.php). Equivalente ao password_hash('admin123', PASSWORD_BCRYPT) do PHP puro. */
                'senha' => Hash::make('admin123'),
            ]
        );

        /*
         * Token Sanctum de demo pra API. O token anterior é revogado e um novo é gerado a cada seed.
         * Sanctum armazena só o hash SHA-256, o plain text só existe aqui.
         */
        $admin->tokens()->where('name', 'demo-token')->delete();
        $token = $admin->createToken('demo-token')->plainTextToken;
        $this->command?->newLine();
        $this->command?->info('  API Token (cole em requests.http > @apiToken): '.$token);
        $this->command?->newLine();

        // Usuários extras (via factory)
        //
        // UsuarioFactory gera nome, email e senha falsos automaticamente.
        // Esses usuários aparecem no histórico (gerado abaixo) para popular a tela de listagem.
        $extraUsers = Usuario::factory()->count(2)->create();

        // concat() cria uma nova collection sem mutar $extraUsers.
        $allUsers = $extraUsers->concat([$admin]);

        // Viações nomeadas
        //
        // firstOrCreate pra rodar db:seed duas vezes sem duplicar.
        // Os nomes são reais porque o demo precisa de dados reconhecíveis.
        $namedViacoes = collect([
            ['nome' => 'Expresso Guanabara', 'cidade' => 'Rio de Janeiro', 'ativa' => true],
            ['nome' => 'Eucatur',            'cidade' => 'Curitiba',       'ativa' => true],
            ['nome' => 'Reunidas Paulista',  'cidade' => 'São Paulo',      'ativa' => true],
            ['nome' => 'Cometa',             'cidade' => 'Campinas',       'ativa' => true],
            ['nome' => 'Itapemirim',         'cidade' => 'Vitória',        'ativa' => true],
            ['nome' => 'Real Expresso',      'cidade' => 'Brasília',       'ativa' => true],
            ['nome' => 'Penha',              'cidade' => 'Belo Horizonte', 'ativa' => false],
        ])->map(function ($data) {
            $cidade = Cidade::firstOrCreate(['nome' => $data['cidade']]);

            return Viacao::firstOrCreate(
                ['nome' => $data['nome']],
                ['cidade_id' => $cidade->id, 'ativa' => $data['ativa'], 'logo' => null]
            );
        });

        // Viações extras (via factory)
        //
        // ViacaoFactory gera nome, cidade e status aleatórios via Faker.
        // Demonstra como a factory funciona sem dados específicos.
        $extraViacoes = Viacao::factory()->count(3)->create();

        $allViacoes = $namedViacoes->merge($extraViacoes);

        foreach ($namedViacoes as $viacao) {
            // $viacao->historico() é o morphMany — já filtra por entidade_type e entidade_id.
            // Mais idiomático e menos frágil do que replicar o filtro manualmente via where().
            if (! $viacao->historico()->where('acao', AcaoHistorico::Criado->value)->exists()) {
                Historico::factory()
                    ->criado()
                    ->for($viacao, 'entidade')
                    ->state(['usuario_id' => $admin->id])
                    ->create();
            }
        }

        // Histórico variado: state() com closure para aleatorizar por registro.
        // ->value garante que o alias gravado no banco vem do enum, não de string avulsa.
        Historico::factory()
            ->count(5)
            ->editado()
            ->state(fn () => [
                'entidade_type' => EntidadeHistorico::Viacao->value,
                'entidade_id' => $allViacoes->random()->id,
                'usuario_id' => $allUsers->random()->id,
            ])
            ->create();

        Historico::factory()
            ->count(2)
            ->excluido()
            ->state(fn () => [
                'entidade_type' => EntidadeHistorico::Viacao->value,
                'entidade_id' => $allViacoes->random()->id,
                'usuario_id' => $allUsers->random()->id,
            ])
            ->create();
    }
}
