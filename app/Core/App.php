<?php

namespace App\Core;

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\FileController;
use App\Controllers\HymnController;
use App\Controllers\ServicePlanController;
use App\Controllers\TagController;
use App\Controllers\TuneController;

class App
{
    public static function run(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        if (!empty($_GET['r']) && is_string($_GET['r'])) {
            $path = '/' . ltrim($_GET['r'], '/');
        }
        if ($path === '/install' || strpos($path, '/install/') === 0) {
            require BASE_PATH . '/install/index.php';
            return;
        }

        if (!file_exists(BASE_PATH . '/config/database.php') && !file_exists(BASE_PATH . '/install.lock')) {
            header('Location: /install');
            exit;
        }

        $router = new Router();
        self::routes($router);
        $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', rtrim($path, '/') ?: '/');
    }

    private static function routes(Router $router): void
    {
        $router->get('/login', [AuthController::class, 'showLogin']);
        $router->post('/login', [AuthController::class, 'login']);
        $router->post('/logout', [AuthController::class, 'logout']);

        $router->get('/', [DashboardController::class, 'index']);
        $router->get('/search', [DashboardController::class, 'search']);

        $router->get('/hymns', [HymnController::class, 'index']);
        $router->get('/hymns/create', [HymnController::class, 'create']);
        $router->post('/hymns', [HymnController::class, 'store']);
        $router->get('/hymns/{id}', [HymnController::class, 'show']);
        $router->get('/hymns/{id}/edit', [HymnController::class, 'edit']);
        $router->post('/hymns/{id}', [HymnController::class, 'update']);
        $router->post('/hymns/{id}/hide', [HymnController::class, 'hide']);
        $router->post('/hymns/{id}/delete', [HymnController::class, 'delete']);
        $router->post('/hymns/{id}/files', [FileController::class, 'uploadHymnFile']);

        $router->get('/files/{id}/download', [FileController::class, 'downloadHymnFile']);
        $router->post('/files/{id}/delete', [FileController::class, 'deleteHymnFile']);

        $router->get('/tunes', [TuneController::class, 'index']);
        $router->get('/tunes/create', [TuneController::class, 'create']);
        $router->post('/tunes', [TuneController::class, 'store']);
        $router->get('/tunes/{id}', [TuneController::class, 'show']);
        $router->get('/tunes/{id}/edit', [TuneController::class, 'edit']);
        $router->post('/tunes/{id}', [TuneController::class, 'update']);

        $router->get('/tags', [TagController::class, 'index']);
        $router->post('/tags/groups', [TagController::class, 'storeGroup']);
        $router->post('/tags', [TagController::class, 'storeTag']);

        $router->get('/plans', [ServicePlanController::class, 'index']);
        $router->get('/plans/create', [ServicePlanController::class, 'create']);
        $router->post('/plans', [ServicePlanController::class, 'store']);
        $router->get('/plans/{id}', [ServicePlanController::class, 'show']);
        $router->post('/plans/{id}', [ServicePlanController::class, 'update']);
        $router->post('/plans/{id}/items', [ServicePlanController::class, 'addItem']);
        $router->post('/plans/items/{id}', [ServicePlanController::class, 'updateItem']);
        $router->post('/plans/items/{id}/delete', [ServicePlanController::class, 'deleteItem']);
        $router->get('/plans/{id}/export', [ServicePlanController::class, 'export']);
    }
}
