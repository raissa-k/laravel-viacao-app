<?php

// Diferença principal desta versão: usa factories para criar registros, em vez de arrays hardcoded passados direto para ::create().
// Isso mantém a lógica de geração de dados em um lugar só (a factory), e o seeder descreve QUAIS dados quer, não COMO construir.
// Pesquise "factory vs seeder responsibility", "idempotent seed".

namespace Database\Seeders;

use App\Enums\AcaoHistorico;
use App\Models\Usuario;
use App\Models\Viacao;
use App\Models\ViacaoHistorico;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * GUARD DE AMBIENTE: este seeder NÃO deve rodar em produção.
         *
         * Por quê isso importa?
         * Este seeder cria o usuário admin@admin.com com a senha "admin123".
         * Essas credenciais são públicas (estão no README e no requests.http).
         * Se alguém rodar "php artisan db:seed" ou "php artisan migrate --seed"
         * acidentalmente em produção, qualquer pessoa com esse conhecimento consegue logar como admin e ter acesso total ao sistema.
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
        ])->map(fn ($data) => Viacao::firstOrCreate(
            ['nome' => $data['nome']],
            ['cidade' => $data['cidade'], 'ativa' => $data['ativa'], 'logo' => null]
        ));

        // Viações extras (via factory)
        //
        // ViacaoFactory gera nome, cidade e status aleatórios via Faker.
        // Demonstra como a factory funciona sem dados específicos.
        $extraViacoes = Viacao::factory()->count(3)->create();

        $allViacoes = $namedViacoes->merge($extraViacoes);

        // Histórico "Criado" para viações nomeadas
        //
        // for($viacao) e for($admin): associa o historico a models já existentes sem criar novas viações ou usuários (a factory criaria por padrão).
        //
        // Pesquise "for() factory Laravel" e compare com o que acontece sem ele:
        // ViacaoHistorico::factory()->criado()->create() criaria uma Viacao e um Usuario novos, além do historico
        //
        // ViacaoHistorico::factory()->criado()->for($viacao)->for($admin)->create() reutiliza os models fornecidos
        foreach ($namedViacoes as $viacao) {
            if (! ViacaoHistorico::where('viacao_id', $viacao->id)->where('acao', AcaoHistorico::Criado->value)->exists()) {
                ViacaoHistorico::factory()
                    ->criado()
                    ->for($viacao)
                    ->for($admin)
                    ->create();
            }
        }

        // Histórico variado usando recycle()
        //
        // recycle($collection): quando a factory precisaria criar um model relacionado (ex: viacao_id => Viacao::factory()),
        // ela pega um aleatório da collection em vez de criar um novo.
        //
        // Isso é diferente de for():
        // for($viacao)         -> sempre usa ESSA viação específica
        // recycle($collection) -> sorteia uma a cada registro criado
        //
        // Resultado: 10 registros de histórico distribuídos pelas viações e usuários existentes.
        ViacaoHistorico::factory()
            ->count(5)
            ->editado()
            ->recycle($allViacoes)
            ->recycle($allUsers)
            ->create();

        ViacaoHistorico::factory()
            ->count(2)
            ->excluido()
            ->recycle($allViacoes)
            ->recycle($allUsers)
            ->create();
    }
}
