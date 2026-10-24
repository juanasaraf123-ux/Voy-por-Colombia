<?php
declare(strict_types=1);

namespace App\Core;

/** Enrutador simple: patrones con parámetros {nombre} y manejadores [Clase, método]. */
final class Router
{
    private array $routes = [];

    public function get(string $pattern, array|callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, array|callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function add(string $method, string $pattern, array|callable $handler): void
    {
        $regex = preg_replace('#\{(id)\}#', '(?P<$1>\d+)', $pattern);
        $regex = preg_replace('#\{([a-z_]+)\}#', '(?P<$1>[a-z0-9\-]+)', (string) $regex);
        $this->routes[] = [$method, '#^' . $regex . '$#', $handler];
    }

    public function dispatch(string $method, string $path): void
    {
        $allowed = false;
        foreach ($this->routes as [$m, $regex, $handler]) {
            if (!preg_match($regex, $path, $matches)) {
                continue;
            }
            if ($m !== $method && !($m === 'GET' && $method === 'HEAD')) {
                $allowed = true;
                continue;
            }
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            if ($method === 'POST') {
                Csrf::verify();
            }
            if (is_array($handler)) {
                [$class, $action] = $handler;
                (new $class())->$action(...$params);
            } else {
                $handler(...$params);
            }
            return;
        }
        throw new HttpException($allowed ? 405 : 404);
    }
}
