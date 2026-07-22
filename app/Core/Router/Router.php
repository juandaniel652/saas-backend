<?php

declare(strict_types=1);

namespace App\Core\Router;

use App\Core\Container\Container;
use App\Core\Exceptions\NotFoundException;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Middleware\MiddlewareInterface;

final class Router
{
    /**
     * @var array<int, array{
     *     method: string,
     *     pattern: string,
     *     paramNames: string[],
     *     handler: array{0: class-string, 1: string},
     *     middleware: array<int, string|MiddlewareInterface>
     * }>
     */
    private array $routes = [];

    public function __construct(private readonly Container $container)
    {
    }

    /** @param array<int, string|MiddlewareInterface> $middleware */
    public function get(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    /** @param array<int, string|MiddlewareInterface> $middleware */
    public function post(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    /** @param array<int, string|MiddlewareInterface> $middleware */
    public function put(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    /** @param array<int, string|MiddlewareInterface> $middleware */
    public function delete(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    /**
     * @param array{0: class-string, 1: string} $handler
     * @param array<int, string|MiddlewareInterface> $middleware
     */
    private function addRoute(string $method, string $path, array $handler, array $middleware): void
    {
        $paramNames = [];
        $normalizedPath = rtrim($path, '/') ?: '/';

        $pattern = preg_replace_callback(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
            function (array $matches) use (&$paramNames): string {
                $paramNames[] = $matches[1];

                return '([^/]+)';
            },
            $normalizedPath,
        );

        $this->routes[] = [
            'method' => $method,
            'pattern' => '#^' . $pattern . '$#',
            'paramNames' => $paramNames,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(Request $request): Response
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method) {
                continue;
            }

            if (!preg_match($route['pattern'], $request->path, $matches)) {
                continue;
            }

            array_shift($matches);
            $params = array_combine($route['paramNames'], $matches) ?: [];

            return $this->runPipeline($request, $params, $route);
        }

        throw new NotFoundException('Ruta no encontrada: ' . $request->path);
    }

    /**
     * @param array<string, string> $params
     * @param array{
     *     method: string,
     *     pattern: string,
     *     paramNames: string[],
     *     handler: array{0: class-string, 1: string},
     *     middleware: array<int, string|MiddlewareInterface>
     * } $route
     */
    private function runPipeline(Request $request, array $params, array $route): Response
    {
        $handler = function (Request $request) use ($params, $route): Response {
            $controller = $this->container->make($route['handler'][0]);
            $method = $route['handler'][1];

            return $controller->{$method}($request, $params);
        };

        foreach (array_reverse($route['middleware']) as $middlewareEntry) {
            $next = $handler;

            $middleware = is_string($middlewareEntry)
                ? $this->container->make($middlewareEntry)
                : $middlewareEntry;

            $handler = fn (Request $request): Response => $middleware->handle($request, $next);
        }

        return $handler($request);
    }
}