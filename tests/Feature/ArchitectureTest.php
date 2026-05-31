<?php

use App\Http\Controllers\Controller;
use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

// Testes de arquitetura: verificam automaticamente que o código novo segue os padrões do projeto.
// A ideia é que se criar uma classe no lugar errado, herdar a classe errada ou esquecer um contrato,
// testes falham antes de chegar no code review.
//
// Pesquise "Pest architecture testing", "arch() Pest".

// Controllers
// Padrão: herdam Controller, nome termina com "Controller".
// Controllers só orquestram: recebem request, delegam ao service, devolvem response.
// Business logic fica no service; queries diretas ficam no Eloquent model via service.

arch('controllers estendem o Controller base')
    ->expect('App\Http\Controllers')
    ->classes()
    ->toExtend(Controller::class)
    ->ignoring(Controller::class);

arch('nomes de controllers terminam com "Controller"')
    ->expect('App\Http\Controllers')
    ->classes()
    ->toHaveSuffix('Controller');

// Controllers não chamam DB:: diretamente, isso é responsabilidade do service.
// Se precisar de uma query, extraia para um service e injete via construtor.
arch('controllers não chamam a facade DB diretamente')
    ->expect('App\Http\Controllers')
    ->not->toUse(DB::class);

// Chamadas a APIs externas (Http::get(), Http::post()) pertencem ao service, não ao controller.
// O controller só orquestra, a comunicação com serviços externos é responsabilidade do service.
// Pesquise "Laravel HTTP Client", "service layer pattern".
arch('controllers não chamam APIs externas diretamente')
    ->expect('App\Http\Controllers')
    ->not->toUse(Http::class);

// API Controllers
// Padrão: devolvem apenas JSON, nunca Blade views ou redirecionamentos.
// Clients de API (mobile, JS, terceiros) não interpretam redirects nem Blade.
// Pesquise "REST API design", "JSON response", "HTTP status codes".

arch('controllers de API não retornam views Blade')
    ->expect('App\Http\Controllers\Api')
    ->not->toUse(View::class);

arch('controllers de API não redirecionam')
    ->expect('App\Http\Controllers\Api')
    ->not->toUse(RedirectResponse::class);

// Vamos forçar aqui que Endpoints REST expõem exatamente um recurso via os métodos CRUD padrão.
// Se precisar de uma ação fora do CRUD (ex: "publicar"), crie um novo recurso (PublicacaoController).
// Pesquise "RESTful resource controllers", "REST resource design".
arch('controllers de API expõem apenas métodos RESTful')
    ->expect('App\Http\Controllers\Api')
    ->not->toHavePublicMethodsBesides([
        '__construct', '__invoke',
        'index', 'show', 'create', 'store', 'edit', 'update', 'destroy',
    ]);

// Services
// Padrão: nome termina com "Service", sem classe base, sem herança.
// Services contêm as regras de negócio e são injetados nos controllers via construtor.
// Pesquise "Dependency Injection", "Service Layer pattern".

arch('nomes de services terminam com "Service"')
    ->expect('App\Services')
    ->toHaveSuffix('Service');

arch('services não estendem nenhuma classe base')
    ->expect('App\Services')
    ->toExtendNothing();

// Models
// Padrão: estendem Model (direto ou via Authenticatable), usam HasFactory para os testes.

arch('models estendem Eloquent Model')
    ->expect('App\Models')
    ->toExtend(Model::class);

arch('models usam a trait HasFactory')
    ->expect('App\Models')
    ->toUse(HasFactory::class);

// Lógica de apresentação (HTML, JSON de resposta) pertence às views e aos Resources, não ao model.
// Ver ViacaoResource como exemplo de onde formatar dados para o JSON da API.
arch('models não importam classes HTTP ou de view')
    ->expect('App\Models')
    ->not->toUse([
        View::class,
        Response::class,
        JsonResponse::class,
        RedirectResponse::class,
    ]);

// DTOs
// Padrão: final readonly class garante imutabilidade e uso seguro como value object.
// DTOs encapsulam e normalizam dados antes de chegarem no service.
// Nesse projeto não use $request->input() diretamente no controller ou service: crie um DTO.
//
// DTOs de filtro (GET): implementam FilterDTO e terminam com "FilterDTO" ex: ViacaoFilterDTO.
// Outros DTOs (criação, importação etc.) não precisam implementar FilterDTO, mas ainda devem ser final readonly.
//
// Pesquise "Data Transfer Object", "readonly class PHP 8.2", "value object".

arch('todos os DTOs são final e readonly')
    ->expect('App\DTOs')
    ->classes()
    ->toBeFinal()
    ->toBeReadonly()
    ->ignoring('App\DTOs\Contracts');

// Enums
// Padrão: backed enum (string ou int), obrigatório para usar ->value e tryFrom().
// Pure/unit enums (sem tipo) não têm ->value nem tryFrom(), então não podem diretamente ser salvos no banco ou reconstruídos a partir de input do usuário.
// Neste projeto não trabalharemos com pure enums.
// Neste projeto não use strings ou ints soltos no lugar de um enum.
// Pesquise "backed enum PHP 8.1", "UnitEnum vs BackedEnum", "tryFrom()".

arch('enums são backed (string ou int)')
    ->expect('App\Enums')
    ->toImplement(BackedEnum::class);

// Form Requests
// Padrão: estendem FormRequest, nome termina com "Request".
// FormRequests centralizam a validação e autorização de uma rota, neste projeto não valide inline no controller.
// Pesquise "Laravel Form Request", "authorize() vs middleware".

arch('form requests estendem FormRequest')
    ->expect('App\Http\Requests')
    ->toExtend(FormRequest::class);

arch('nomes de form requests terminam com "Request"')
    ->expect('App\Http\Requests')
    ->toHaveSuffix('Request');

// Resources
// Padrão: estendem JsonResource, nome termina com "Resource".
// Resources controlam exatamente quais campos o JSON expõe, neste projeto não retorne $model->toArray() diretamente.
// Pesquise "Laravel API Resource", "toArray() Resource".

arch('resources estendem JsonResource')
    ->expect('App\Http\Resources')
    ->toExtend(JsonResource::class);

arch('nomes de resources terminam com "Resource"')
    ->expect('App\Http\Resources')
    ->toHaveSuffix('Resource');

// Policies
// Padrão: nome termina com "Policy".
// Uma Policy recebe o model pronto (injetado pelo Gate): não precisa buscar no banco.
// Registre policies em AppServiceProvider ou deixe o Laravel descobrir automaticamente.
// Pesquise "Laravel Gates and Policies", "authorize() in controllers", "Gate::define()".

arch('policies têm sufixo "Policy"')
    ->expect('App\Policies')
    ->toHaveSuffix('Policy');

// O Gate injeta o model na policy, ela recebe o objeto pronto, não o ID.
// Queries diretamente na policy contornam esse fluxo e dificultam mocking nos testes.
arch('policies não fazem queries diretamente no banco')
    ->expect('App\Policies')
    ->not->toUse(DB::class);

// Middleware
// Padrão: middleware expõe handle(Request $request, Closure $next).
// Middleware intercepta a requisição antes de chegar no controller.
// Use para cross-cutting concerns: autenticação, rate limiting, logging de acesso.
// Pesquise "Laravel middleware", "HTTP middleware pipeline".

arch('middleware tem o método handle()')
    ->expect('App\Http\Middleware')
    ->toHaveMethod('handle');

// Commands
// Padrão: estendem Command.
// Commands são a versão CLI de um controller: recebem argumentos, delegam ao service, retornam SUCCESS/FAILURE.

arch('commands estendem Command')
    ->expect('App\Console\Commands')
    ->toExtend(Command::class);

// Jobs
// Padrão: implementam ShouldQueue (para execução assíncrona em fila), têm handle().
// Jobs executam fora do ciclo HTTP: não têm acesso à sessão, cookies ou ao objeto Request.
// Se precisar de dados do request, extraia os valores ANTES de despachar e passe como parâmetros.
// Pesquise "Laravel queued jobs", "ShouldQueue", "queue worker", "dispatch()".

arch('jobs implementam ShouldQueue')
    ->expect('App\Jobs')
    ->toImplement(ShouldQueue::class);

arch('jobs têm o método handle()')
    ->expect('App\Jobs')
    ->toHaveMethod('handle');

arch('jobs não dependem do ciclo HTTP')
    ->expect('App\Jobs')
    ->not->toUse(Request::class);

// Event Listeners
// Padrão: têm handle() que recebe o evento como parâmetro tipado.
// Listeners reagem a eventos disparados no sistema; não dependem do ciclo HTTP.
// Pesquise "Laravel Events and Listeners", "Event::dispatch()", "ShouldHandleEventsAfterCommit".

arch('listeners têm o método handle()')
    ->expect('App\Listeners')
    ->toHaveMethod('handle');

arch('listeners não dependem do ciclo HTTP')
    ->expect('App\Listeners')
    ->not->toUse(Request::class);

// Notifications
// Padrão: estendem Notification, nome termina com "Notification".
// Notifications podem ser enviadas via múltiplos canais: mail, database, SMS, broadcast.
// via() define quais canais usar; toMail(), toDatabase() etc. formatam o conteúdo.
// Pesquise "Laravel Notifications", "via() method", "notification channels".

arch('notifications estendem Notification')
    ->expect('App\Notifications')
    ->toExtend(Notification::class);

arch('nomes de notifications terminam com "Notification"')
    ->expect('App\Notifications')
    ->toHaveSuffix('Notification');

// Global

// Debug helpers não devem aparecer no código de produção.
// Eles imprimem saída diretamente, quebram respostas JSON e são difíceis de rastrear.
arch('código de produção não usa debug helpers')
    ->expect('App')
    ->not->toUse(['dd', 'dump', 'var_dump', 'print_r', 'ray']);

// Namespace hygiene: previne que classes sejam criadas no lugar errado por engano.
// Neste projeto, Eloquent Models fora de App\Models quebram auto-discovery, factories e convenções.
arch('Eloquent Models ficam apenas em App\Models')
    ->expect('App')
    ->not->toExtend(Model::class)
    ->ignoring('App\Models');

// Sufixos são usados pelo IDE e pelo time para navegar no código.
// Uma classe "FooController" fora de App\Http\Controllers confunde quem lê o código.
arch('sufixo "Controller" é exclusivo do namespace de controllers')
    ->expect('App')
    ->not->toHaveSuffix('Controller')
    ->ignoring('App\Http\Controllers');
