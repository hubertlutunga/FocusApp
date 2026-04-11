<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

final class Router
{
    private array $routes = [];

    public function get(string $uri, array $action, array $middleware = []): void
    {
        $this->addRoute('GET', $uri, $action, $middleware);
    }

    public function post(string $uri, array $action, array $middleware = []): void
    {
        $this->addRoute('POST', $uri, $action, $middleware);
    }

    private function addRoute(string $method, string $uri, array $action, array $middleware = []): void
    {
        $this->routes[$method][$this->normalizePath($uri)] = [
            'action' => $action,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = $this->normalizeRequestUri($uri);
        $route = $this->routes[$method][$path] ?? null;

        if (!$route) {
            $this->renderError(404, 'Page introuvable', 'La ressource demandée est introuvable.');
            return;
        }

        try {
            foreach ($route['middleware'] as $middlewareClass) {
                (new $middlewareClass())->handle();
            }

            [$controllerClass, $controllerMethod] = $route['action'];
            $controller = new $controllerClass();
            $controller->{$controllerMethod}();
        } catch (Throwable $throwable) {
            http_response_code(500);
            $this->renderError(500, 'Erreur interne', $throwable->getMessage());
        }
    }

    private function normalizeRequestUri(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $baseUrl = rtrim((string) config('app.base_url', ''), '/');

        if ($baseUrl !== '' && str_starts_with($path, $baseUrl)) {
            $path = substr($path, strlen($baseUrl)) ?: '/';
        }

        if ($path === '/index.php') {
            $path = '/';
        } elseif (str_starts_with($path, '/index.php/')) {
            $path = substr($path, strlen('/index.php')) ?: '/';
        }

        return $this->normalizePath($path);
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '//' ? '/' : (rtrim($path, '/') ?: '/');
    }

    private function renderError(int $code, string $title, string $message): void
    {
        http_response_code($code);
        $controller = new Controller();
        $controller->render('errors.' . $code, compact('title', 'message'), 'auth');
    }
}
