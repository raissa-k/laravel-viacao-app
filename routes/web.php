<?php

// Rotas web do app (páginas HTML).
// Compare com src/routes/web.php do PHP puro.
//
// Diferenças de implementação:
//   PHP puro: $router->get('/path', [Controller::class, 'method'])
//   Laravel:  Route::get('/path', [Controller::class, 'method'])->name('nome')
//
// URLs nomeadas (->name()): (não precisa nomear pra funcionar),
// MAS com nomeação em vez de hardcodar "/admin/viacoes" nas views, usamos route('viacoes.index'). Se a URL mudar, só atualiza aqui.
// Pesquise "named routes Laravel", "route() helper".
//
// No Laravel: Route::middleware('auth') aplica o middleware a um grupo de rotas declarativamente.
// Pesquise "Laravel middleware groups", "route middleware".

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HistoricoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\ViacaoController;
use Illuminate\Support\Facades\Route;

// Home pública: qualquer visitante pode acessar
Route::get('/', [HomeController::class, 'index'])->name('home');

// Serve arquivos de upload (logos) armazenados fora do docroot.
// where(): restringe {filename} ao padrão gerado pelo UploadService (bin2hex(8 bytes) + extensão).
// O UploadController ainda valida com basename(), mas a rota nem chega a despachar se o formato não bater.
Route::get('/uploads/{filename}', [UploadController::class, 'serve'])
    ->name('uploads.serve')
    ->where('filename', '[a-f0-9]{16}\.(jpg|png|webp)');

// Auth: 'guest' redireciona usuários já logados pro painel (evita logar de novo)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    // throttle:5,1 = no máximo 5 tentativas por minuto por IP.
    // Sem isso, um atacante pode testar senhas indefinidamente (brute force).
    // Pesquise "brute force attack", "rate limiting Laravel".
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin: protegido por 'auth' redireciona pra /login se não estiver autenticado.
// O prefixo /admin deixa explícito que é área restrita, igual ao PHP puro.
Route::prefix('admin')->middleware('auth')->group(function () {

    // CRUD de viações
    Route::get('/viacoes', [ViacaoController::class, 'index'])->name('viacoes.index');
    Route::get('/viacoes/create', [ViacaoController::class, 'create'])->name('viacoes.create');
    Route::post('/viacoes', [ViacaoController::class, 'store'])->name('viacoes.store');
    Route::get('/viacoes/{viacao}/edit', [ViacaoController::class, 'edit'])->name('viacoes.edit');
    Route::put('/viacoes/{viacao}', [ViacaoController::class, 'update'])->name('viacoes.update');
    Route::delete('/viacoes/{viacao}', [ViacaoController::class, 'destroy'])->name('viacoes.destroy');

    // Histórico de alterações
    Route::get('/historico', [HistoricoController::class, 'index'])->name('historico.index');

    // Usuários (somente leitura)
    Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios.index');
});
