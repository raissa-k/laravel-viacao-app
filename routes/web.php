<?php

// Rotas web do app.
// Diferenças vs PHP puro:
//   Route::get/post/put/delete + ->name() = URL nomeada usada com route() nas views.
//   Route::middleware() = middleware declarativo em grupos, sem repetição.
//
// SOFT DELETE E ROUTE MODEL BINDING:
//   Por padrão, o binding NÃO encontra registros com deleted_at preenchido.
//   ->withTrashedParameters() instrui o binding a incluir soft-deleted.
//   Usado apenas nas rotas de restore, onde precisamos do registro excluído.
//   Pesquise "withTrashedParameters Laravel", "route model binding soft delete".

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
    Route::get('/viacoes/{viacao}', [ViacaoController::class, 'show'])->name('viacoes.show');
    Route::get('/viacoes/{viacao}/edit', [ViacaoController::class, 'edit'])->name('viacoes.edit');
    Route::put('/viacoes/{viacao}', [ViacaoController::class, 'update'])->name('viacoes.update');
    Route::delete('/viacoes/{viacao}', [ViacaoController::class, 'destroy'])->name('viacoes.destroy');
    // restore recebe o ID como int simples: route model binding padrão não encontra soft-deleted.
    // O controller busca com withTrashed() manualmente.
    Route::post('/viacoes/{id}/restore', [ViacaoController::class, 'restore'])->name('viacoes.restore')
        ->where('id', '[0-9]+');

    // Histórico de alterações
    Route::get('/historico', [HistoricoController::class, 'index'])->name('historico.index');

    // CRUD de usuários
    Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/create', [UsuariosController::class, 'create'])->name('usuarios.create');
    Route::post('/usuarios', [UsuariosController::class, 'store'])->name('usuarios.store');
    Route::get('/usuarios/{usuario}', [UsuariosController::class, 'show'])->name('usuarios.show');
    Route::get('/usuarios/{usuario}/edit', [UsuariosController::class, 'edit'])->name('usuarios.edit');
    Route::put('/usuarios/{usuario}', [UsuariosController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{usuario}', [UsuariosController::class, 'destroy'])->name('usuarios.destroy');
    Route::post('/usuarios/{id}/restore', [UsuariosController::class, 'restore'])->name('usuarios.restore')
        ->where('id', '[0-9]+');
});
