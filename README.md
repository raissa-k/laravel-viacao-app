# Viações Demo (Laravel)

Versão Laravel do projeto `php-task-app`.
Mesma aplicação de gerenciamento de viações, endpoints e CRUD idênticos ao projeto em PHP puro, porém escrito do jeito Laravel com Eloquent, FormRequest, Blade, named routes e `.env`.

## Stack

- PHP 8.4 + Apache (Docker)
- Laravel 13
- MySQL 8
- Composer 2

## Arquitetura (comparação com o projeto PHP puro)

A estrutura é paralela ao `php-task-app`:

| Camada no PHP puro                        | Equivalente Laravel                                 |
|-------------------------------------------|-----------------------------------------------------|
| `src/Core/Router.php`                     | `routes/web.php` + `routes/api.php` (framework)    |
| `src/Controllers/ViacaoController.php`    | `app/Http/Controllers/ViacaoController.php`         |
| `src/Controllers/Api/ViacaoApiController` | `app/Http/Controllers/Api/ViacaoApiController.php`  |
| `src/Services/ViacaoService.php`          | `app/Services/ViacaoService.php`                    |
| `src/Models/Viacao.php` (DTO)             | `app/Models/Viacao.php` (Eloquent)                  |
| `src/Repositories/HistoricoRepository`    | `app/Services/HistoricoService.php`                 |
| `src/database/init.sql`                   | `database/migrations/*_create_*_table.php`          |
| `src/views/*.php` + `_layout.php`         | `resources/views/layouts/app.blade.php` + `admin/*` |
| `src/Validators/ViacaoValidator.php`      | `app/Http/Requests/ViacaoRequest.php`               |
| `View::flash()`                           | `session()->flash()` (via `->with('success', ...)`) |

## Estrutura de pastas

O Laravel por padrão organiza o código em pastas específicas, e aqui estendemos o padrão.

```
laravel-task-app/
├── app/
│   ├── Console/Commands/        # Comandos Artisan (CLI)
│   ├── DTOs/                    # Objetos de transferência de dados (imutáveis)
│   │   └── Contracts/           # Interfaces dos DTOs (ex: FilterDTO)
│   ├── Enums/                   # Enums tipados (backed: string ou int)
│   ├── Http/
│   │   ├── Controllers/         # Controladores (web + API)
│   │   │   └── Api/             # Controllers exclusivos da API REST
│   │   ├── Requests/            # Validação de formulários (FormRequest)
│   │   ├── Resources/           # Transformação de Models em JSON (API)
│   │   └── Middleware/          # Middleware (auth, rate limit, etc.)
│   ├── Models/                  # Models Eloquent (representam tabelas)
│   ├── Notifications/           # Notificações (e-mail, banco, etc.)
│   ├── Policies/                # Políticas de autorização (Gate/Policy)
│   ├── Providers/               # Service providers (boot e registro)
│   └── Services/                # Lógica de negócio (separada do controller)
│
├── config/
│   ├── api.php                  # Rate limits da API (lidos via config(), não env())
│   ├── app.php                  # Config principal (nome, locale, timezone)
│   ├── auth.php                 # Configuração de guards e providers (publicado)
│   ├── database.php             # Conexões de banco e timezones
│   ├── sanctum.php              # Tokens de API: expiração, prefixo, domínios (publicado)
│   └── ...outros arquivos de config
│
├── database/
│   ├── migrations/              # Versionamento de schema (CREATE TABLE, ALTER TABLE)
│   ├── factories/               # Geração de dados fake (testes e seeds)
│   └── seeders/                 # Scripts para popular banco de desenvolvimento
│
├── docker/
│   ├── docker-compose.yml       # Configuração base (todos os ambientes)
│   └── docker-compose.override.yml  # Override local: Mailhog, flags de debug
│
├── resources/
│   └── views/
│       ├── layouts/             # Layouts base (@extends)
│       ├── auth/                # Views de autenticação
│       ├── admin/               # Views do painel admin
│       └── vendor/              # Templates publicados do framework (customizáveis)
│           ├── mail/html/       # Layout e componentes de e-mail HTML
│           └── notifications/   # Template base de notificações por e-mail
│
├── routes/
│   ├── web.php                  # Rotas web (retornam HTML/redirect)
│   └── api.php                  # Rotas API (retornam JSON)
│
├── storage/app/private/         # Arquivos privados (uploads de logos)
│
├── tests/
│   ├── Feature/                 # Testes de fluxo completo (HTTP, banco)
│   └── Unit/                    # Testes de classe isolada
│
├── Dockerfile                   # Imagem PHP + Apache
├── .env.example                 # Template de variáveis de ambiente
└── phpunit.xml                  # Config dos testes
```

**Por que assim?**

- **Models**: representam tabelas; um arquivo por tabela. Só persistência, sem lógica HTTP ou apresentação
- **Controllers**: recebem request, delegam ao service, devolvem response. Não acessam banco diretamente
- **Services**: lógica de negócio (criar viação, regras, transações). Controllers chamam services
- **Requests**: validação de entrada (POST/PUT). O FormRequest intercepta antes do controller executar
- **Resources**: formatam o JSON da API. Não retorne `$model->toArray()` diretamente neste projeto.
- **DTOs**: normalizam parâmetros de entrada antes de chegarem no service (ex: filtros GET)
- **Policies**: autorização baseada em model. Recebem o model pronto, não buscam no banco
- **Notifications**: envios por múltiplos canais (e-mail, banco, SMS). Templates em `vendor/notifications/`
- **database/migrations**: versionam mudanças de schema. Execute `php artisan migrate` para aplicar
- **database/seeders**: populam banco com dados de exemplo. Execute `php artisan db:seed`

**Regra geral: cada arquivo tem um propósito específico. Não misture responsabilidades.**

## Como rodar

### 1. Entrar na pasta docker e subir os containers:

```bash
cd docker
docker compose up --build -d
```

Os containers ficam em `docker/`. O Docker Compose auto-discovers `docker-compose.yml` e `docker-compose.override.yml` nessa pasta e merge automaticamente.

Em todo start, o container do app:
- roda `composer install` (se vendor/ não existir)
- copia `.env.example` para `.env` (se não existir)
- gera `APP_KEY` (se ainda estiver vazia)
- roda `php artisan migrate`
- **não** roda `db:seed` automaticamente (para não resetar token/dados em todo restart)

**Após o primeiro start, habilite o modo debug no `.env`:**

```bash
# Antes (padrão seguro, não expõe stack traces)
APP_DEBUG=false

# Depois (desenvolvimento local, mostra erros completos)
APP_DEBUG=true
```

### 2. Popular com dados de exemplo (opcional):

```bash
docker compose exec viacoes_laravel_demo_app php artisan db:seed
```

O seed imprime o **API token** gerado na saída, copie pra `requests.http`.

### 3. Acessar:

| URL                               | O que é                                                 |
|-----------------------------------|---------------------------------------------------------|
| http://localhost:8082             | Home pública (viações ativas)                           |
| http://localhost:8082/login       | Painel admin (`admin@admin.com` / `admin123`)           |
| http://localhost:8082/api/viacoes | API JSON pública                                        |
| http://localhost:8082/telescope   | Debug de requests, queries, logs, e-mails               |
| http://localhost:8025             | Interceptor de e-mails (nenhum e-mail sai pra internet) |

### 4. Parar:

```bash
docker compose down
```

Reset total (apaga o volume do MySQL):

```bash
docker compose down -v
```

## Docker: base e override

Os arquivos docker ficam em `docker/`:

**`docker-compose.yml`** - configuração base, segura, commitada no Git. Válida para todos os ambientes.

**`docker-compose.override.yml`** - customizações locais, também commitado aqui para fins educacionais (em projetos reais, o override raramente é commitado). Adiciona:
- Serviço **Mailhog** (intercepta e-mails localmente)
- Variáveis `MAIL_*` apontando para o Mailhog no container do app

O Docker Compose carrega os dois automaticamente quando roda de dentro de `docker/`. 
Pra usar apenas o base (sem override), especifique o arquivo explicitamente:

```bash
docker compose -f docker/docker-compose.yml up -d
```

## Mailhog (e-mail local)

O Mailhog intercepta todos os e-mails enviados pela aplicação. Nenhum e-mail chega a uma caixa real.

- **Interface web:** http://localhost:8025
- **SMTP interno:** `mailhog:1025` (usado automaticamente pelo Laravel via `.env`)

Para criar e testar uma notificação por e-mail:

```bash
php artisan make:notification MinhaNotificacao
```

O template base de e-mail está em `resources/views/vendor/notifications/email.blade.php`.
O layout visual (cores, logo, fontes) está em `resources/views/vendor/mail/html/`.

Para republicar esses templates do zero (já feito neste projeto):

```bash
php artisan vendor:publish --tag=laravel-notifications
php artisan vendor:publish --tag=laravel-mail
```

## Telescope (debug local)

O Telescope é um painel que aqui deixamos disponível apenas no ambiente local (`APP_ENV=local`).

**Acesso:** http://localhost:8082/telescope (faça login no admin primeiro)

O que você pode observar no Telescope:

| Aba        | O que mostra                                                         |
|------------|----------------------------------------------------------------------|
| Requests   | Toda requisição HTTP: rota, middleware, tempo de resposta            |
| Queries    | SQL executado por requisição, tempo, bindings. Bom para detectar N+1 |
| Logs       | Tudo que `Log::info()`, `Log::error()` etc. registram                |
| Mail       | E-mails enviados (complementa o Mailhog com detalhes do Laravel)     |
| Jobs       | Jobs dispatched, status, tempo de execução                           |
| Exceptions | Exceptions com stack trace completo                                  |
| Events     | Eventos disparados com `Event::dispatch()`                           |

O Telescope é registrado condicionalmente em `AppServiceProvider::register()`. A lógica está em `app/Providers/TelescopeServiceProvider.php`.

## API JSON

| Método | URI                 | Auth | Comportamento              |
|--------|---------------------|------|----------------------------|
| GET    | `/api`              | não  | alias de `/api/viacoes`    |
| GET    | `/api/viacoes`      | não  | lista viações **ativas**   |
| GET    | `/api/viacoes/{id}` | não  | retorna uma viação pelo ID |
| POST   | `/api/viacoes`      | sim  | cria uma viação            |
| PUT    | `/api/viacoes/{id}` | sim  | atualiza uma viação        |
| DELETE | `/api/viacoes/{id}` | sim  | remove uma viação          |

Rotas protegidas exigem `Authorization: Bearer <token>` gerado pelo `db:seed`.

**Nota:** `GET /api/viacoes` retorna apenas viações ativas (`ativa = true`), espelhando a home pública. Viações inativas só aparecem no painel admin autenticado.

**Rate limits:**
- Leitura: `API_RATE_LIMIT_READ` por minuto por IP (padrão: 60)
- Escrita: `API_RATE_LIMIT_WRITE` por minuto por usuário+IP (padrão: 20)
- Resposta ao exceder: `429 JSON`

Testes rápidos:

```bash
curl -s http://localhost:8082/api/viacoes | jq
curl -s http://localhost:8082/api/viacoes/1 | jq

curl -X POST http://localhost:8082/api/viacoes \
  -H "Authorization: Bearer TOKEN_AQUI" \
  -H "Content-Type: application/json" \
  -d '{"nome":"Nova Viação","cidade":"Porto Alegre","ativa":true}' | jq
```

O arquivo [requests.http](requests.http) tem requests prontas pro PhpStorm HTTP Client.

## Rotas Web (Admin)

Autenticadas com `middleware('auth')` em `/admin/...`. Acesse em `/login`.

| Método | URI                        | Controller                  |
|--------|----------------------------|-----------------------------|
| GET    | `/`                        | `HomeController@index`      |
| GET    | `/login`                   | `AuthController@loginForm`  |
| POST   | `/login`                   | `AuthController@login`      |
| POST   | `/logout`                  | `AuthController@logout`     |
| GET    | `/admin/viacoes`           | `ViacaoController@index`    |
| GET    | `/admin/viacoes/create`    | `ViacaoController@create`   |
| POST   | `/admin/viacoes`           | `ViacaoController@store`    |
| GET    | `/admin/viacoes/{id}/edit` | `ViacaoController@edit`     |
| PUT    | `/admin/viacoes/{id}`      | `ViacaoController@update`   |
| DELETE | `/admin/viacoes/{id}`      | `ViacaoController@destroy`  |
| GET    | `/admin/historico`         | `HistoricoController@index` |
| GET    | `/admin/usuarios`          | `UsuariosController@index`  |

## Comandos úteis (artisan)

Todos dentro do container, prefixe com `docker compose exec viacoes_laravel_demo_app`:

```bash
# Rodar de dentro de docker/:
docker compose exec viacoes_laravel_demo_app php artisan migrate
docker compose exec viacoes_laravel_demo_app php artisan migrate:fresh --seed
docker compose exec viacoes_laravel_demo_app php artisan db:seed
docker compose exec viacoes_laravel_demo_app php artisan tinker
docker compose exec viacoes_laravel_demo_app php artisan route:list
docker compose exec viacoes_laravel_demo_app php artisan config:clear
docker compose exec viacoes_laravel_demo_app php artisan telescope:clear   # limpa entradas antigas
```

## Testes

```bash
# Rodar tudo (usa SQLite in-memory, não precisa do MySQL)
php artisan test --compact

# Rodar só os testes de arquitetura
php artisan test --compact tests/Feature/ArchitectureTest.php
```

Os testes de arquitetura (`tests/Feature/ArchitectureTest.php`) verificam automaticamente que o código segue os padrões do projeto. Sufixos, herança correta, sem DB nos controllers, etc. Se criar uma classe no lugar errado, falha antes do code review.

## Qualidade de código (Pint)

```bash
# Verifica formatação sem alterar arquivos
docker compose exec viacoes_laravel_demo_app vendor/bin/pint --test

# Aplica formatação automaticamente
docker compose exec viacoes_laravel_demo_app vendor/bin/pint
```

## Variáveis de ambiente (.env)

**O que é `.env`?**

Arquivo de variáveis de ambiente: credenciais, URLs, configurações sensíveis. Nunca commite `.env` no Git (está em `.gitignore`). Cada desenvolvedor tem o seu.

**Passo 1: Copiar o template**

```bash
cp .env.example .env
```

`.env.example` é o template com valores seguros para desenvolvimento local e comentários explicativos. Quando criar variáveis novas, documente-as aqui.

**Passo 2: Ajustar para seu ambiente**

```bash
APP_DEBUG=true                  # false em produção (não expõe stack traces)
APP_URL=http://localhost:8082

DB_PASSWORD=viacoes_pass        # alterar em produção
MYSQL_PASSWORD=viacoes_pass     # deve combinar com DB_PASSWORD
MYSQL_ROOT_PASSWORD=root123

MAIL_HOST=mailhog               # já configurado para Mailhog local
MAIL_PORT=1025
```

**Após mudar `.env`**, limpe o cache de config:

```bash
docker compose exec viacoes_laravel_demo_app php artisan config:clear
```

**Por que `config()` e não `env()` no código da aplicação?**

`env()` lê o `.env` diretamente, mas quando o cache de config está ativo (`php artisan config:cache`, padrão em produção), o `.env` não é carregado e `env()` retorna `null`. Use `config()` em código da aplicação; use `env()` apenas dentro de arquivos `config/*.php`.

Pesquise: "Laravel config cache", "env() vs config()".

## Banco de dados

```
Host local: localhost:3309
DB_DATABASE: viacoes_demo
DB_USERNAME: viacoes_user
DB_PASSWORD: viacoes_pass
```

**Credenciais de demo** (criadas pelo `db:seed` apenas em local/testing):
- Login: `admin@admin.com` / `admin123`
- API Token: impresso na saída do `db:seed`

## Paridade e diferenças intencionais (PHP puro vs Laravel)

| Tema        | PHP puro                           | Laravel (este projeto)                     | Motivo                                             |
|-------------|------------------------------------|--------------------------------------------|----------------------------------------------------|
| Auth da API | `X-API-TOKEN` manual no controller | `Authorization: Bearer` via `auth:sanctum` | padrão middleware e hash de token                  |
| DELETE API  | retornava `{"ok":true}`            | retorna `204 No Content`                   | convenção REST                                     |
| Boot Docker | seed no fluxo antigo               | seed manual (`php artisan db:seed`)        | previsibilidade (não trocar token em todo restart) |
| Debug local | ---                                | Telescope + Mailhog                        | observabilidade sem expor dados em produção        |

## Service Layer

Controllers não acessam o banco diretamente. A lógica fica em services.

### ViacaoService

| Método                         | O que faz                                                 |
|--------------------------------|-----------------------------------------------------------|
| `all(ViacaoFilterDTO $filter)` | Todas as viações com filtros de busca e status (admin)    |
| `active()`                     | Só viações ativas, usada na home pública e na API pública |
| `find(int $id)`                | Busca por ID, retorna `null` se não encontrar             |
| `create(...)`                  | Cria viação e registra histórico em transação             |
| `update(Viacao, ...)`          | Atualiza e registra diff antes/depois em transação        |
| `delete(Viacao, ...)`          | Remove e registra histórico em transação                  |

### HistoricoService

| Método                                   | O que faz                                              |
|------------------------------------------|--------------------------------------------------------|
| `getHistory(HistoricoFilterDTO $filter)` | Lista histórico com eager-load de `viacao` e `usuario` |

## Blade: Helpers úteis

### `{{ }}` - Escapa HTML automaticamente

```blade
{{ $viacao->nome }}  {{-- Equivalente a htmlspecialchars() no PHP puro --}}
```

### `{!! !!}` - HTML sem escape (use com cuidado)

```blade
{!! $htmlConfiavel !!}  {{-- Só use com dados que você controla, nunca com input do usuário --}}
```

### `route()` - Gera URL da rota nomeada

```blade
<a href="{{ route('viacoes.index') }}">Voltar</a>
<a href="{{ route('viacoes.edit', $viacao) }}">Editar</a>
```

Se a URL mudar em `routes/web.php`, o `route()` atualiza automaticamente em toda a aplicação.

### `@csrf` e `@method()`

```blade
<form method="POST" action="{{ route('viacoes.update', $viacao) }}">
    @csrf           {{-- Proteção CSRF: valida que o form veio do seu site --}}
    @method('PUT')  {{-- HTML só suporta GET/POST; isso simula PUT/DELETE --}}
    ...
</form>
```

### `old()` - Repopula campo após erro de validação

```blade
<input type="text" name="nome" value="{{ old('nome') }}">
```

### `@error()` - Mostra mensagem de erro de campo

```blade
@error('nome')
    <span class="error">{{ $message }}</span>
@enderror
```

### `@checked()`, `@selected()`, `@disabled()`

```blade
<input type="checkbox" name="ativa" @checked(old('ativa', true))>
<option value="1" @selected($filter->ativa === true)>Ativas</option>
```

### `{{ Js::from() }}` - Serializa para JavaScript seguro

```blade
<script>
const nome = {{ Js::from($viacao->nome) }};
</script>
```

Sem `Js::from()`, um nome como `"A"; alert('xss');"` executaria código JS arbitrário.

## Padrões para estudo

### Injeção de dependência

```php
public function __construct(
    private readonly ViacaoService $viacaoService,
) {}
```

O container Laravel resolve e injeta automaticamente. Compare com PHP puro: `nullable` + `new ViacaoService()` manual.

### DTOs para normalização de entrada

```php
$filter = ViacaoFilterDTO::fromRequest($request);
$viacoes = $this->viacaoService->all($filter);
```

Parâmetros GET chegam ao service já normalizados e tipados, nunca como strings brutas.

### Transações com rollback automático

```php
DB::transaction(function () {
    $viacao = Viacao::create([...]);
    ViacaoHistorico::create([...]);
    // Se qualquer linha lançar exception, rollback automático
});
```

Compare com PHP puro: `beginTransaction()` + `try/catch` + `commit/rollBack` manual.

### Eager loading para evitar N+1

```php
ViacaoHistorico::with(['viacao', 'usuario'])->get();
// 3 queries: histórico + IN(viacoes) + IN(usuarios)
// Sem with(): 1 + N + N queries
```

### Enums backed para valores de banco

```php
// Salvar
ViacaoHistorico::create(['acao' => AcaoHistorico::Criado->value]);

// Reconstruir de input
$acao = AcaoHistorico::tryFrom($request->input('acao')); // null se inválido
```
