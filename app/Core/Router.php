<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $pattern, array $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, array $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, array $handler): void
    {
        $this->routes[] = compact('method', 'pattern', 'handler');
    }

    public function dispatch(string $method, string $path): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = [];
            $regex = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', function ($matches) use (&$params) {
                $params[] = $matches[1];
                return '([^/]+)';
            }, $route['pattern']);

            $regex = '#^' . $regex . '$#';
            if (preg_match($regex, $path, $matches)) {
                array_shift($matches);
                $args = [];
                foreach ($matches as $index => $value) {
                    $args[$params[$index]] = $value;
                }

                [$class, $methodName] = $route['handler'];
                $controller = new $class();
                $controller->$methodName(...array_values($args));
                return;
            }
        }

        http_response_code(404);
        echo '404 Not Found';
    }
}

