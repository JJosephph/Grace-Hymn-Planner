<?php

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = BASE_PATH . '/app/Views/' . $view . '.php';
        ob_start();
        require BASE_PATH . '/app/Views/layouts/app.php';
        echo rewrite_route_links(ob_get_clean());
    }

    protected function redirect(string $path): void
    {
        if (!preg_match('#^https?://#', $path)) {
            $path = url($path);
        }

        header('Location: ' . $path);
        exit;
    }

    protected function json(array $payload): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
