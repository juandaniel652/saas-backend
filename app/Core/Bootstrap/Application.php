<?php

declare(strict_types=1);

namespace App\Core\Bootstrap;

use App\Core\Config\Config;
use App\Core\Container\Container;
use App\Core\Database\Connection;
use App\Core\Events\EventDispatcher;
use App\Core\Exceptions\AppException;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Http\ResponseHelper;
use App\Core\Logging\Logger;
use App\Core\Mail\Mailer;
use App\Core\Router\Router;
use Dotenv\Dotenv;
use Throwable;

final class Application
{
    private Container $container;
    private Router $router;

    public function __construct(private readonly string $basePath)
    {
        $this->loadEnvironment();
        $this->container = new Container();
        $this->registerCoreBindings();
        $this->registerEventListeners();
        $this->router = $this->buildRouter();
    }

    private function loadEnvironment(): void
    {
        Dotenv::createImmutable($this->basePath)->safeLoad();
    }

    private function registerCoreBindings(): void
    {
        $this->container->singleton(
            Config::class,
            fn (): Config => new Config($this->basePath . '/config'),
        );

        $this->container->singleton(
            Logger::class,
            fn (Container $c): Logger => new Logger(
                $c->make(Config::class)->get('app.log_path', $this->basePath . '/storage/logs/app.log'),
            ),
        );

        $this->container->singleton(
            Connection::class,
            fn (Container $c): Connection => new Connection($c->make(Config::class)),
        );

        $this->container->singleton(
            \App\Core\Auth\JwtManager::class,
            fn (Container $c): \App\Core\Auth\JwtManager => new \App\Core\Auth\JwtManager($c->make(Config::class)),
        );

        $this->container->singleton(
            \App\Core\Auth\PasswordHasher::class,
            fn (): \App\Core\Auth\PasswordHasher => new \App\Core\Auth\PasswordHasher(),
        );

        $this->container->singleton(
            EventDispatcher::class,
            fn (Container $c): EventDispatcher => new EventDispatcher($c),
        );

        $this->container->singleton(
            Mailer::class,
            fn (Container $c): Mailer => new Mailer(
                $c->make(Config::class),
                $c->make(Logger::class),
            ),
        );
    }

    private function registerEventListeners(): void
    {
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this->container->make(EventDispatcher::class);

        $registerFiles = glob($this->basePath . '/app/Modules/*/Listeners/register.php') ?: [];

        foreach ($registerFiles as $file) {
            $register = require $file;

            if (is_callable($register)) {
                $register($dispatcher);
            }
        }
    }

    private function buildRouter(): Router
    {
        $router = new Router($this->container);

        $routeFiles = glob($this->basePath . '/app/Modules/*/Routes/routes.php') ?: [];

        foreach ($routeFiles as $routeFile) {
            $register = require $routeFile;

            if (is_callable($register)) {
                $register($router);
            }
        }

        return $router;
    }

    public function run(): void
    {
        $request = Request::fromGlobals();

        try {
            $response = $this->router->dispatch($request);
        } catch (Throwable $e) {
            $response = $this->handleException($e);
        }

        $response->send();
    }

    private function handleException(Throwable $e): Response
    {
        /** @var Logger $logger */
        $logger = $this->container->make(Logger::class);
        /** @var Config $config */
        $config = $this->container->make(Config::class);

        if ($e instanceof AppException) {
            $logger->warning($e->getMessage(), ['exception' => $e::class]);

            return ResponseHelper::error($e->getMessage(), $e->errors(), $e->statusCode());
        }

        $logger->error($e->getMessage(), [
            'exception' => $e::class,
            'trace' => $e->getTraceAsString(),
        ]);

        $debug = (bool) $config->get('app.debug', false);

        return ResponseHelper::error(
            $debug ? $e->getMessage() : 'Error interno del servidor',
            $debug ? ['trace' => explode("\n", $e->getTraceAsString())] : [],
            500,
        );
    }
}